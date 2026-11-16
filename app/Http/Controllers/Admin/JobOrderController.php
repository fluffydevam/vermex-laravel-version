<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobOrderRequest;
use App\Http\Requests\UpdateJobOrderRequest;
use App\Models\Client;
use App\Models\Contract;
use App\Models\JobOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class JobOrderController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view-any-job-order');

        $search = trim((string) $request->input('search', ''));
        $status = (string) $request->input('status', '');
        $technicianId = (string) $request->input('technician', '');
        $date = (string) $request->input('date', '');

        $jobs = JobOrder::query()
            ->with(['client', 'technician'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('client_name', 'like', "%{$search}%")
                        ->orWhere('service_type', 'like', "%{$search}%")
                        ->orWhere('service_address', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($q) => $q->where('route_status', $status))
            ->when($technicianId !== '', fn ($q) => $q->where('technician_id', $technicianId))
            ->when($date !== '', fn ($q) => $q->whereDate('scheduled_date', $date))
            ->orderByDesc('scheduled_date')
            ->orderBy('service_window')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'today' => JobOrder::whereDate('scheduled_date', today())->count(),
            'open' => JobOrder::whereIn('route_status', [
                JobOrderStatus::Scheduled->value,
                JobOrderStatus::EnRoute->value,
                JobOrderStatus::OnSite->value,
            ])->count(),
            'completed_month' => JobOrder::where('route_status', JobOrderStatus::Completed->value)
                ->where('scheduled_date', '>=', now()->startOfMonth())
                ->count(),
            'delayed' => JobOrder::where('route_status', JobOrderStatus::Delayed->value)->count(),
        ];

        $technicians = User::where('role', 'Field Technician')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('admin.job-orders.index', [
            'jobs' => $jobs,
            'stats' => $stats,
            'technicians' => $technicians,
            'search' => $search,
            'status' => $status,
            'technicianId' => $technicianId,
            'date' => $date,
        ]);
    }

    public function create()
    {
        Gate::authorize('create', JobOrder::class);

        return view('admin.job-orders.create', $this->formData());
    }

    public function store(StoreJobOrderRequest $request)
    {
        Gate::authorize('create', JobOrder::class);

        $data = $this->withSnapshot($request->validated());
        $data['route_status'] = JobOrderStatus::Scheduled->value;

        $job = JobOrder::create($data);

        return redirect()
            ->route('admin.job-orders')
            ->with('success', "Job order for '{$job->client_name}' scheduled on {$job->scheduled_date->format('M d, Y')}.");
    }

    public function edit(JobOrder $jobOrder)
    {
        Gate::authorize('update', $jobOrder);

        return view('admin.job-orders.edit', array_merge($this->formData(), [
            'job' => $jobOrder,
        ]));
    }

    public function update(UpdateJobOrderRequest $request, JobOrder $jobOrder)
    {
        Gate::authorize('update', $jobOrder);

        $jobOrder->update($this->withSnapshot($request->validated()));

        return redirect()
            ->route('admin.job-orders')
            ->with('success', "Job order for '{$jobOrder->client_name}' updated.");
    }

    /**
     * Snapshot the client name and service address so printed job orders never change retroactively.
     */
    private function withSnapshot(array $data): array
    {
        $client = Client::findOrFail($data['client_id']);

        if (filled($data['contract_id'] ?? null)) {
            $contract = Contract::find($data['contract_id']);

            if (! $contract || (int) $contract->client_id !== (int) $client->id) {
                throw ValidationException::withMessages([
                    'contract_id' => 'The selected contract does not belong to this client.',
                ]);
            }
        }

        $findings = trim((string) ($data['area_findings'] ?? ''));
        $data['area_findings'] = $findings === ''
            ? null
            : collect(preg_split('/\r\n|\r|\n/', $findings))
                ->filter()
                ->map(fn ($line) => ['area' => trim($line), 'action' => ''])
                ->values()
                ->all();

        $data['client_name'] = $client->client_name;
        $data['service_address'] = $client->address;

        return $data;
    }

    private function formData(): array
    {
        $clients = Client::query()
            ->where('status', 'active')
            ->with('contracts')
            ->orderBy('client_name')
            ->get();

        $technicians = User::query()
            ->where('role', 'Field Technician')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return [
            'clients' => $clients,
            'technicians' => $technicians,
        ];
    }
}
