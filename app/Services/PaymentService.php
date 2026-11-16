<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Normalise the payment ledger filters from the request.
     *
     * @return array{search: string, method: string, type: string, date_filter: string, start_date: string, end_date: string}
     */
    public function filtersFrom(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search', '')),
            'method' => (string) $request->input('method', ''),
            'type' => (string) $request->input('type', ''),
            'date_filter' => (string) $request->input('date_filter', 'all'),
            'start_date' => (string) $request->input('start_date', ''),
            'end_date' => (string) $request->input('end_date', ''),
        ];
    }

    public function paginate(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return $this->applyFilters($this->baseQuery(), $filters)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array{collected: float, transactions: int, portfolio: float, outstanding: float}
     */
    public function metrics(array $filters): array
    {
        $filtered = $this->applyFilters(Payment::query(), $filters);

        $contracts = Contract::query()
            ->notArchived()
            ->withSum('payments', 'amount_paid')
            ->get();

        return [
            'collected' => (float) (clone $filtered)->sum('amount_paid'),
            'transactions' => (clone $filtered)->count(),
            'portfolio' => (float) $contracts->sum(fn (Contract $contract) => (float) $contract->contract_value),
            'outstanding' => (float) $contracts->sum(fn (Contract $contract) => $contract->final_balance),
        ];
    }

    /**
     * Non-archived contracts available for new payments, with their live balance.
     */
    public function eligibleContracts(): Collection
    {
        return Contract::query()
            ->notArchived()
            ->with('client')
            ->withSum('payments', 'amount_paid')
            ->join('clients', 'clients.id', '=', 'contracts.client_id')
            ->orderBy('clients.client_name')
            ->orderBy('contracts.contract_name')
            ->select('contracts.*')
            ->get();
    }

    /**
     * Record a payment after locking the contract and re-checking the live balance.
     *
     * @param  array{contract_id: int|string, amount_paid: float|string, payment_type: string, payment_method: string, reference_number?: string|null, remarks?: string|null, payment_date?: string|null}  $data
     */
    public function record(array $data, User $user): Payment
    {
        return DB::transaction(function () use ($data, $user) {
            /** @var Contract $contract */
            $contract = Contract::query()
                ->lockForUpdate()
                ->withSum('payments', 'amount_paid')
                ->findOrFail($data['contract_id']);

            if ($contract->contract_status === ContractStatus::Archived) {
                throw ValidationException::withMessages([
                    'contract_id' => 'Archived contracts cannot receive new payments.',
                ]);
            }

            $amount = round((float) $data['amount_paid'], 2);
            $outstanding = (float) $contract->final_balance;

            if ($amount > $outstanding + 0.005) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'The amount exceeds the outstanding balance of ₱' . number_format($outstanding, 2) . '.',
                ]);
            }

            return Payment::create([
                'contract_id' => $contract->id,
                'user_id' => $user->id,
                'amount_paid' => $amount,
                'payment_type' => $data['payment_type'],
                'payment_method' => $data['payment_method'],
                'reference_number' => filled($data['reference_number'] ?? null) ? trim((string) $data['reference_number']) : null,
                'remarks' => filled($data['remarks'] ?? null) ? trim((string) $data['remarks']) : null,
                'payment_date' => $this->paymentDate($data['payment_date'] ?? null),
            ]);
        });
    }

    public function methodOptions(): array
    {
        return collect(PaymentMethod::cases())->mapWithKeys(fn (PaymentMethod $case) => [$case->value => $case->value])->all();
    }

    public function typeOptions(): array
    {
        return collect(PaymentType::cases())->mapWithKeys(fn (PaymentType $case) => [$case->value => $case->value])->all();
    }

    private function baseQuery(): Builder
    {
        return Payment::query()->with([
            'user',
            'contract' => fn ($query) => $query
                ->with('client')
                ->withSum('payments', 'amount_paid'),
        ]);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $search = $filters['search'] ?? '';

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('contract', function (Builder $contract) use ($search) {
                        $contract->where('contract_name', 'like', "%{$search}%")
                            ->orWhereHas('client', fn (Builder $client) => $client->where('client_name', 'like', "%{$search}%"));
                    });
            });
        }

        if (filled($filters['method'] ?? null)) {
            $query->where('payment_method', $filters['method']);
        }

        if (filled($filters['type'] ?? null)) {
            $query->where('payment_type', $filters['type']);
        }

        if ($range = $this->dateRange($filters)) {
            $query->whereBetween('payment_date', $range);
        }

        return $query;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function dateRange(array $filters): ?array
    {
        return match ($filters['date_filter'] ?? 'all') {
            'this-month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last-month' => [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ],
            'year-to-date' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => $this->customRange($filters['start_date'] ?? '', $filters['end_date'] ?? ''),
            default => null,
        };
    }

    /**
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function customRange(string $start, string $end): ?array
    {
        if ($start === '' || $end === '') {
            return null;
        }

        try {
            $from = Carbon::parse($start)->startOfDay();
            $to = Carbon::parse($end)->endOfDay();
        } catch (\Throwable) {
            return null;
        }

        return $from->lessThanOrEqualTo($to) ? [$from, $to] : null;
    }

    private function paymentDate(?string $value): Carbon
    {
        if (blank($value)) {
            return now();
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return now();
        }
    }
}
