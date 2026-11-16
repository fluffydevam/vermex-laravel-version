<?php

namespace App\Http\Controllers\Billing;

use App\Enums\ClientStatus;
use App\Enums\ContractStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Payment;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __invoke()
    {
        Gate::authorize('view-any-payment');

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $stats = [
            'collected_month' => (float) Payment::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount_paid'),
            'collected_total' => (float) Payment::sum('amount_paid'),
            'payments_month' => Payment::whereBetween('payment_date', [$monthStart, $monthEnd])->count(),
            'active_contracts' => Contract::where('contract_status', ContractStatus::Active->value)->count(),
            'active_clients' => Client::where('status', ClientStatus::Active->value)->count(),
        ];

        $recentPayments = Payment::query()
            ->with(['contract.client', 'user'])
            ->orderByDesc('payment_date')
            ->limit(8)
            ->get();

        return view('billing.dashboard', [
            'stats' => $stats,
            'recentPayments' => $recentPayments,
        ]);
    }
}
