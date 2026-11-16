<?php

namespace App\Http\Controllers\Field;

use App\Enums\JobOrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\LogJobMaterialsRequest;
use App\Http\Requests\UpdateJobStatusRequest;
use App\Models\InventoryItem;
use App\Models\JobOrder;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class JobOrderController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('view-any-job-order');

        $user = $request->user();
        $view = (string) $request->input('view', 'upcoming');

        $jobs = JobOrder::query()
            ->with(['client', 'technician'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('technician_id', $user->id))
            ->when($view === 'today', fn ($q) => $q->whereDate('scheduled_date', today()))
            ->when($view === 'upcoming', fn ($q) => $q->whereDate('scheduled_date', '>=', today()))
            ->when($view === 'completed', fn ($q) => $q->where('route_status', JobOrderStatus::Completed->value))
            ->orderBy('scheduled_date')
            ->orderBy('service_window')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'today' => JobOrder::where('technician_id', $user->id)->whereDate('scheduled_date', today())->count(),
            'open' => JobOrder::where('technician_id', $user->id)->whereIn('route_status', [
                JobOrderStatus::Scheduled->value,
                JobOrderStatus::EnRoute->value,
                JobOrderStatus::OnSite->value,
            ])->count(),
            'completed_month' => JobOrder::where('technician_id', $user->id)
                ->where('route_status', JobOrderStatus::Completed->value)
                ->where('scheduled_date', '>=', now()->startOfMonth())
                ->count(),
            'delayed' => JobOrder::where('technician_id', $user->id)
                ->where('route_status', JobOrderStatus::Delayed->value)
                ->count(),
        ];

        return view('field.job-orders.index', compact('jobs', 'stats', 'view'));
    }

    public function show(JobOrder $jobOrder)
    {
        Gate::authorize('view', $jobOrder);

        $this->ensureOwned($jobOrder);

        $jobOrder->load([
            'client',
            'contract',
            'technician',
            'materials.inventoryItem',
        ]);

        $inventoryItems = InventoryItem::query()
            ->where('quantity_in_stock', '>', 0)
            ->orderBy('item_name')
            ->get();

        return view('field.job-orders.show', [
            'job' => $jobOrder,
            'inventoryItems' => $inventoryItems,
        ]);
    }

    public function updateStatus(UpdateJobStatusRequest $request, JobOrder $jobOrder)
    {
        Gate::authorize('update', $jobOrder);

        $this->ensureOwned($jobOrder);

        $status = JobOrderStatus::from($request->string('route_status')->toString());

        $attributes = [
            'route_status' => $status->value,
            'comments' => $request->input('comments'),
        ];

        if ($status !== JobOrderStatus::Scheduled && $jobOrder->time_in === null) {
            $attributes['time_in'] = now()->format('H:i:s');
        }

        if ($status === JobOrderStatus::Completed && $jobOrder->time_out === null) {
            $attributes['time_out'] = now()->format('H:i:s');
        }

        $jobOrder->update($attributes);

        return back()->with('success', "Job order marked as {$status->value}.");
    }

    public function logMaterials(LogJobMaterialsRequest $request, JobOrder $jobOrder)
    {
        Gate::authorize('update', $jobOrder);

        $this->ensureOwned($jobOrder);

        if ($jobOrder->route_status === JobOrderStatus::Scheduled) {
            return back()->with('error', 'Start the job before recording materials.');
        }

        $item = InventoryItem::findOrFail($request->integer('inventory_item_id'));

        try {
            $this->inventory->consumeForJob(
                $item,
                (float) $request->input('quantity_used'),
                $request->user(),
                $jobOrder,
                $request->filled('remarks') ? $request->string('remarks')->toString() : null,
            );
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return back()->withErrors(['quantity_used' => $e->getMessage()])->withInput();
        }

        return back()->with('success', "Recorded {$request->input('quantity_used')} {$item->unit} of {$item->item_name}.");
    }

    /**
     * Technicians may only touch their own jobs; admins keep oversight access.
     */
    private function ensureOwned(JobOrder $jobOrder): void
    {
        $user = auth()->user();

        if ($user->role !== UserRole::Admin && (int) $jobOrder->technician_id !== (int) $user->id) {
            abort(403, 'This job order is assigned to another technician.');
        }
    }
}
