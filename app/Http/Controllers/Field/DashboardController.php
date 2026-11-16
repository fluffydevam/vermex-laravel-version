<?php

namespace App\Http\Controllers\Field;

use App\Enums\InspectionStatus;
use App\Enums\JobOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use App\Models\SiteInspection;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __invoke()
    {
        Gate::authorize('view-any-job-order');

        $userId = auth()->id();

        $stats = [
            'today' => JobOrder::where('technician_id', $userId)->whereDate('scheduled_date', today())->count(),
            'open' => JobOrder::where('technician_id', $userId)->whereIn('route_status', [
                JobOrderStatus::Scheduled->value,
                JobOrderStatus::EnRoute->value,
                JobOrderStatus::OnSite->value,
            ])->count(),
            'completed_month' => JobOrder::where('technician_id', $userId)
                ->where('route_status', JobOrderStatus::Completed->value)
                ->where('scheduled_date', '>=', now()->startOfMonth())
                ->count(),
            'inspections_pending' => SiteInspection::where('technician_id', $userId)
                ->where('inspection_status', InspectionStatus::PendingVisit->value)
                ->count(),
        ];

        $jobs = JobOrder::query()
            ->with(['client'])
            ->where('technician_id', $userId)
            ->whereDate('scheduled_date', '>=', today()->subDay())
            ->orderBy('scheduled_date')
            ->limit(8)
            ->get();

        return view('field.dashboard', [
            'stats' => $stats,
            'jobs' => $jobs,
        ]);
    }
}
