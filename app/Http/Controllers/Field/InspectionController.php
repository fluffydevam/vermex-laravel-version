<?php

namespace App\Http\Controllers\Field;

use App\Enums\InspectionStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInspectionRequest;
use App\Models\SiteInspection;
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

        $user = $request->user();
        $status = (string) $request->input('status', '');

        $inspections = SiteInspection::query()
            ->with(['client', 'technician'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('technician_id', $user->id))
            ->when($status !== '', fn ($q) => $q->where('inspection_status', $status))
            ->orderByDesc('inspection_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending' => SiteInspection::where('technician_id', $user->id)
                ->where('inspection_status', InspectionStatus::PendingVisit->value)->count(),
            'complete' => SiteInspection::where('technician_id', $user->id)
                ->where('inspection_status', InspectionStatus::InspectionComplete->value)->count(),
            'pests' => SiteInspection::where('technician_id', $user->id)
                ->where('inspection_status', InspectionStatus::PestsFound->value)->count(),
            'month' => SiteInspection::where('technician_id', $user->id)
                ->where('inspection_date', '>=', now()->startOfMonth())->count(),
        ];

        return view('field.inspections.index', compact('inspections', 'stats', 'status'));
    }

    public function show(SiteInspection $inspection)
    {
        Gate::authorize('view', $inspection);

        $this->ensureOwned($inspection);

        $inspection->load(['client', 'contract', 'technician']);

        return view('field.inspections.show', [
            'inspection' => $inspection,
            'conditionLabels' => SiteInspection::CONDITION_LABELS,
        ]);
    }

    public function update(UpdateInspectionRequest $request, SiteInspection $inspection)
    {
        Gate::authorize('update', $inspection);

        $this->ensureOwned($inspection);

        $data = $request->validated();

        // Technicians cannot reassign an inspection; admins keep oversight access.
        if ($request->user()->role !== UserRole::Admin) {
            $data['technician_id'] = $inspection->technician_id;
        }

        $inspection->update($this->inspections->normalise($data));

        return back()->with('success', 'Inspection report saved.');
    }

    private function ensureOwned(SiteInspection $inspection): void
    {
        $user = auth()->user();

        if ($user->role !== UserRole::Admin && (int) $inspection->technician_id !== (int) $user->id) {
            abort(403, 'This inspection is assigned to another technician.');
        }
    }
}
