<?php

namespace App\Services;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Models\Client;
use App\Models\Contract;
use Illuminate\Http\Request;

class ClientAccountService
{
    /**
     * Build the shared client accounts index payload used by admin and billing.
     *
     * @return array<string, mixed>
     */
    public function indexData(Request $request): array
    {
        $search = trim((string) $request->input('search', ''));
        $filterType = (string) $request->input('type', '');
        $filterStatus = (string) $request->input('status', '');
        $tab = (string) $request->input('tab', 'clients');

        if (! in_array($tab, ['clients', 'contracts', 'archived'], true)) {
            $tab = 'clients';
        }

        $clients = null;
        $contracts = null;

        if ($tab === 'contracts') {
            $contracts = Contract::query()
                ->with('client')
                ->withSum('payments', 'amount_paid')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('contract_name', 'like', "%{$search}%")
                            ->orWhereHas('client', fn ($c) => $c->where('client_name', 'like', "%{$search}%"));
                    });
                })
                ->when($filterStatus !== '', fn ($q) => $q->where('contract_status', $filterStatus))
                ->orderByDesc('created_at')
                ->paginate(25)
                ->withQueryString();
        } else {
            $status = $tab === 'archived' ? ClientStatus::Archived : null;

            $clients = Client::query()
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('client_name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
                })
                ->when($filterType !== '', fn ($q) => $q->where('client_type', $filterType))
                ->when($filterStatus !== '', fn ($q) => $q->where('status', $filterStatus))
                ->when($filterStatus === '' && $status !== null, fn ($q) => $q->where('status', $status->value))
                ->when($filterStatus === '' && $status === null, fn ($q) => $q->where('status', '!=', ClientStatus::Archived->value))
                ->orderByDesc('created_at')
                ->paginate(25)
                ->withQueryString();
        }

        $kpis = [
            'total' => Client::count(),
            'active' => Client::where('status', ClientStatus::Active->value)->count(),
            'inactive' => Client::where('status', ClientStatus::Inactive->value)->count(),
            'archived' => Client::where('status', ClientStatus::Archived->value)->count(),
            'commercial' => Client::where('client_type', ClientType::Commercial->value)->count(),
            'residential' => Client::where('client_type', ClientType::Residential->value)->count(),
        ];

        return [
            'clients' => $clients,
            'contracts' => $contracts,
            'kpis' => $kpis,
            'search' => $search,
            'filterType' => $filterType,
            'filterStatus' => $filterStatus,
            'tab' => $tab,
        ];
    }

    /**
     * Build the shared client detail payload used by admin and billing.
     *
     * @return array<string, mixed>
     */
    public function showData(Client $client): array
    {
        $client->load([
            'contracts' => fn ($q) => $q->withSum('payments', 'amount_paid')->orderByDesc('created_at'),
        ]);

        $payments = $client->payments()
            ->with('contract')
            ->orderByDesc('payment_date')
            ->limit(10)
            ->get();

        $contractValue = (float) $client->contracts->sum('contract_value');
        $paidTotal = (float) $client->contracts->sum(fn (Contract $contract) => $contract->paid_amount);

        return [
            'client' => $client,
            'contracts' => $client->contracts,
            'payments' => $payments,
            'contractValue' => $contractValue,
            'paidTotal' => $paidTotal,
            'balance' => max(0.0, $contractValue - $paidTotal),
        ];
    }
}
