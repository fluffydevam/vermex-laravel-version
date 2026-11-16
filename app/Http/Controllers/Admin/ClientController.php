<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClientStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\Contract;
use App\Services\ClientAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClientController extends Controller
{
    public function __construct(private readonly ClientAccountService $accounts)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('view-any-client');

        return view('admin.clients.index', array_merge($this->accounts->indexData($request), [
            'indexRoute' => 'admin.clients',
            'showRoute' => 'admin.clients.show',
        ]));
    }

    public function create()
    {
        Gate::authorize('create', Client::class);

        return view('admin.clients.create');
    }

    public function store(StoreClientRequest $request)
    {
        Gate::authorize('create', Client::class);

        $client = Client::create($request->validated());

        return redirect()
            ->route('admin.clients', ['tab' => 'clients'])
            ->with('success', "Client '{$client->client_name}' registered successfully.");
    }

    public function show(Client $client)
    {
        Gate::authorize('view', $client);

        return view('admin.clients.show', array_merge($this->accounts->showData($client), [
            'indexRoute' => 'admin.clients',
            'showRoute' => 'admin.clients.show',
        ]));
    }

    public function edit(Client $client)
    {
        Gate::authorize('update', $client);

        return view('admin.clients.edit', ['client' => $client]);
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        Gate::authorize('update', $client);

        $client->update($request->validated());

        $tab = $client->status === ClientStatus::Archived ? 'archived' : 'clients';

        return redirect()
            ->route('admin.clients', ['tab' => $tab])
            ->with('success', "Client '{$client->client_name}' updated.");
    }

    public function archive(Client $client)
    {
        Gate::authorize('archive', $client);

        $contracts = $client->contracts()->get();

        if ($contracts->isEmpty()) {
            $name = $client->client_name;
            $client->delete();

            return redirect()
                ->route('admin.clients', ['tab' => 'clients'])
                ->with('success', "Client '{$name}' deleted (no associated contracts).");
        }

        $balance = (float) $contracts->sum(
            fn (Contract $contract) => max(0.0, (float) $contract->contract_value - (float) $contract->paid_amount)
        );

        if ($balance > 0) {
            return back()->with(
                'error',
                'Cannot archive client with an outstanding balance of ₱' . number_format($balance, 2) . '.'
            );
        }

        $client->update(['status' => ClientStatus::Archived]);

        return redirect()
            ->route('admin.clients', ['tab' => 'clients'])
            ->with('success', "Client '{$client->client_name}' archived.");
    }

    public function restore(Client $client)
    {
        Gate::authorize('restore', $client);

        $client->update(['status' => ClientStatus::Active]);

        return redirect()
            ->route('admin.clients', ['tab' => 'archived'])
            ->with('success', "Client '{$client->client_name}' restored to active.");
    }
}
