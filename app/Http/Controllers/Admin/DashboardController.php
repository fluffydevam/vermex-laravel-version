<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClientStatus;
use App\Enums\ContractStatus;
use App\Enums\InspectionStatus;
use App\Enums\JobOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\InventoryItem;
use App\Models\JobOrder;
use App\Models\Payment;
use App\Models\SiteInspection;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __invoke()
    {
        Gate::authorize('view-any-client');

        $monthStart = now()->startOfMonth();

        $stats = [
            'clients_active' => Client::where('status', ClientStatus::Active->value)->count(),
            'clients_total' => Client::count(),
            'contracts_active' => Contract::where('contract_status', ContractStatus::Active->value)->count(),
            'contracts_expiring' => Contract::where('contract_status', ContractStatus::ExpiringSoon->value)->count(),
            'jobs_today' => JobOrder::whereDate('scheduled_date', today())->count(),
            'jobs_pending' => JobOrder::whereIn('route_status', [
                JobOrderStatus::Scheduled->value,
                JobOrderStatus::EnRoute->value,
                JobOrderStatus::OnSite->value,
            ])->count(),
            'inspections_pending' => SiteInspection::where('inspection_status', InspectionStatus::PendingVisit->value)->count(),
            'collected_month' => (float) Payment::where('payment_date', '>=', $monthStart)->sum('amount_paid'),
            'low_stock' => InventoryItem::whereColumn('quantity_in_stock', '<=', 'min_threshold')->count(),
            'users_active' => User::where('status', 'active')->count(),
        ];

        $upcomingJobs = JobOrder::query()
            ->with(['client', 'technician'])
            ->whereDate('scheduled_date', '>=', today())
            ->orderBy('scheduled_date')
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'upcomingJobs' => $upcomingJobs,
        ]);
    }
}
