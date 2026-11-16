<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InspectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInspectionRequest;
use App\Http\Requests\UpdateInspectionRequest;
use App\Models\Client;
use App\Models\SiteInspection;
use App\Models\User;
use App\Services\InspectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InspectionController extends Controller
{
    public function __construct(private readonly InspectionService $inspections)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('view-any-inspection');

        $search = trim((string) $request->input('search', ''));
        $status = (string) $request->input('status', '');
        $technicianId = (string) $request->input('technician', '');

        $inspections = SiteInspection::query()
            ->with(['client', 'technician', 'contract'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('client_name', 'like', "%{$search}%")
                        ->orWhere('service_address', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($q) => $q->where('inspection_status', $status))
            ->when($technicianId !== '', fn ($q) => $q->where('technician_id', $technicianId))
            ->orderByDesc('inspection_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'pending' => SiteInspection::where('inspection_status', InspectionStatus::PendingVisit->value)->count(),
            'complete' => SiteInspection::where('inspection_status', InspectionStatus::InspectionComplete->value)->count(),
            'pests' => SiteInspection::where('inspection_status', InspectionStatus::PestsFound->value)->count(),
            'month' => SiteInspection::where('inspection_date', '>=', now()->startOfMonth())->count(),
        ];

        $technicians = User::where('role', 'Field Technician')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('admin.inspections.index', [
            'inspections' => $inspections,
            'stats' => $stats,
            'technicians' => $technicians,
            'search' => $search,
            'status' => $status,
            'technicianId' => $technicianId,
        ]);
    }

    public function create()
    {
        Gate::authorize('create', SiteInspection::class);

        return view('admin.inspections.create', $this->formData());
    }

    public function store(StoreInspectionRequest $request)
    {
        Gate::authorize('create', SiteInspection::class);

        $inspection = SiteInspection::create($this->inspections->normalise($request->validated()));

        return redirect()
            ->route('admin.inspections.show', $inspection)
            ->with('success', "Inspection for '{$inspection->client_name}' created.");
    }

    public function show(SiteInspection $inspection)
    {
        Gate::authorize('view', $inspection);

        $inspection->load(['client', 'contract', 'technician']);

        return view('admin.inspections.show', [
            'inspection' => $inspection,
        ]);
    }

    public function edit(SiteInspection $inspection)
    {
        Gate::authorize('update', $inspection);

        return view('admin.inspections.edit', array_merge($this->formData(), [
            'inspection' => $inspection,
        ]));
    }

    public function update(UpdateInspectionRequest $request, SiteInspection $inspection)
    {
        Gate::authorize('update', $inspection);

        $inspection->update($this->inspections->normalise($request->validated()));

        return redirect()
            ->route('admin.inspections.show', $inspection)
            ->with('success', "Inspection for '{$inspection->client_name}' updated.");
    }

    private function formData(): array
    {
        return [
            'clients' => Client::query()
                ->where('status', 'active')
                ->with('contracts')
                ->orderBy('client_name')
                ->get(),
            'technicians' => User::query()
                ->where('role', 'Field Technician')
                ->where('status', 'active')
                ->orderBy('first_name')
                ->get(),
            'conditionLabels' => SiteInspection::CONDITION_LABELS,
        ];
    }
}
