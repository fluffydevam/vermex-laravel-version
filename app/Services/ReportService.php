<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\JobOrderStatus;
use App\Models\Contract;
use App\Models\JobOrder;
use App\Models\Payment;
use App\Models\SiteInspection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * @return array{filter: string, start_date: string, end_date: string}
     */
    public function filtersFrom(Request $request): array
    {
        $filter = (string) $request->input('filter', 'this-month');

        if (! in_array($filter, ['this-month', 'last-month', 'year-to-date', 'custom'], true)) {
            $filter = 'this-month';
        }

        return [
            'filter' => $filter,
            'start_date' => (string) $request->input('start_date', ''),
            'end_date' => (string) $request->input('end_date', ''),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    public function range(array $filters): array
    {
        return match ($filters['filter'] ?? 'this-month') {
            'last-month' => [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
                now()->subMonthNoOverflow()->format('F Y'),
            ],
            'year-to-date' => [
                now()->startOfYear(),
                now()->endOfYear(),
                'Year to date ' . now()->year,
            ],
            'custom' => $this->customRange($filters['start_date'] ?? '', $filters['end_date'] ?? ''),
            default => [
                now()->startOfMonth(),
                now()->endOfMonth(),
                now()->format('F Y'),
            ],
        };
    }

    /**
     * Financial metrics shared by the admin and billing reports.
     */
    public function financial(array $filters): array
    {
        [$from, $to, $label] = $this->range($filters);

        $payments = Payment::query()->whereBetween('payment_date', [$from, $to]);

        $contracts = Contract::query()
            ->notArchived()
            ->withSum('payments', 'amount_paid')
            ->get();

        return [
            'period_label' => $label,
            'from' => $from,
            'to' => $to,
            'revenue' => (float) (clone $payments)->sum('amount_paid'),
            'transactions' => (clone $payments)->count(),
            'portfolio' => (float) $contracts->sum(fn (Contract $contract) => (float) $contract->contract_value),
            'outstanding' => (float) $contracts->sum(fn (Contract $contract) => $contract->final_balance),
            'active_contracts' => Contract::where('contract_status', ContractStatus::Active->value)->count(),
            'methods' => $this->paymentMethods($from, $to),
            'statuses' => $this->contractStatuses(),
            'monthly' => $this->monthlyCollections(),
        ];
    }

    /**
     * Operational metrics for the admin report.
     */
    public function operations(array $filters): array
    {
        [$from, $to] = $this->range($filters);

        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        $jobs = JobOrder::query()->whereBetween('scheduled_date', [$fromDate, $toDate]);
        $completedJobs = (clone $jobs)->where('route_status', JobOrderStatus::Completed->value)->count();
        $totalJobs = (clone $jobs)->count();

        $materials = DB::table('job_order_materials as jom')
            ->join('inventory_items as i', 'i.id', '=', 'jom.inventory_item_id')
            ->whereBetween('jom.logged_at', [$from, $to]);

        $chemicalQuantity = (float) (clone $materials)->sum('jom.quantity_used');
        $chemicalCost = (float) (clone $materials)->sum(DB::raw('jom.quantity_used * i.unit_cost'));

        $topChemicals = (clone $materials)
            ->selectRaw('i.item_name, i.unit, SUM(jom.quantity_used) as total_qty, SUM(jom.quantity_used * i.unit_cost) as estimated_cost')
            ->groupBy('i.id', 'i.item_name', 'i.unit')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $serviceTypes = JobOrder::query()
            ->whereBetween('scheduled_date', [$fromDate, $toDate])
            ->selectRaw('service_type, COUNT(*) as count')
            ->groupBy('service_type')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        $materialByTechnician = DB::table('job_order_materials as jom')
            ->join('job_orders as j', 'j.id', '=', 'jom.job_order_id')
            ->whereBetween('jom.logged_at', [$from, $to])
            ->selectRaw('j.technician_id, SUM(jom.quantity_used) as quantity')
            ->groupBy('j.technician_id')
            ->pluck('quantity', 'technician_id');

        $technicians = User::query()
            ->where('role', 'Field Technician')
            ->withCount([
                'jobOrdersAsTechnician as completed_jobs' => fn ($query) => $query
                    ->where('route_status', JobOrderStatus::Completed->value)
                    ->whereBetween('scheduled_date', [$fromDate, $toDate]),
                'siteInspectionsAsTechnician as inspection_count' => fn ($query) => $query
                    ->whereBetween('inspection_date', [$fromDate, $toDate]),
            ])
            ->orderBy('first_name')
            ->get()
            ->each(function (User $technician) use ($materialByTechnician) {
                $technician->setAttribute(
                    'material_quantity',
                    (float) ($materialByTechnician[$technician->id] ?? 0),
                );
            });

        return [
            'jobs_total' => $totalJobs,
            'jobs_completed' => $completedJobs,
            'fulfillment_rate' => $totalJobs > 0 ? round(($completedJobs / $totalJobs) * 100, 1) : 0.0,
            'inspections' => SiteInspection::whereBetween('inspection_date', [$fromDate, $toDate])->count(),
            'chemical_quantity' => $chemicalQuantity,
            'chemical_cost' => $chemicalCost,
            'top_chemicals' => $topChemicals,
            'service_types' => $serviceTypes,
            'technicians' => $technicians,
        ];
    }

    private function paymentMethods(Carbon $from, Carbon $to): Collection
    {
        return Payment::query()
            ->whereBetween('payment_date', [$from, $to])
            ->selectRaw('payment_method, COUNT(*) as transactions, SUM(amount_paid) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();
    }

    private function contractStatuses(): Collection
    {
        return Contract::query()
            ->selectRaw('contract_status, COUNT(*) as count, SUM(contract_value) as value')
            ->groupBy('contract_status')
            ->orderBy('contract_status')
            ->get();
    }

    /**
     * Collections for the last six calendar months, with bar widths pre-computed for the view.
     */
    private function monthlyCollections(): Collection
    {
        $start = now()->subMonthsNoOverflow(5)->startOfMonth();

        $totals = Payment::query()
            ->where('payment_date', '>=', $start)
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as bucket, SUM(amount_paid) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $months = collect(range(0, 5))->map(function (int $offset) use ($totals) {
            $month = now()->subMonthsNoOverflow(5 - $offset)->startOfMonth();
            $key = $month->format('Y-m');

            return [
                'label' => $month->format('M Y'),
                'total' => (float) ($totals[$key] ?? 0),
            ];
        });

        $peak = max(1.0, (float) $months->max('total'));

        return $months->map(fn (array $month) => $month + [
            'percent' => round(($month['total'] / $peak) * 100, 1),
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function customRange(string $start, string $end): array
    {
        try {
            $from = $start !== '' ? Carbon::parse($start)->startOfDay() : now()->startOfMonth();
            $to = $end !== '' ? Carbon::parse($end)->endOfDay() : now()->endOfMonth();
        } catch (\Throwable) {
            $from = now()->startOfMonth();
            $to = now()->endOfMonth();
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to, $from->format('M j, Y') . ' – ' . $to->format('M j, Y')];
    }
}
