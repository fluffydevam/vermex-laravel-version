<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AccountController extends Controller
{
    public function __construct(private readonly ClientAccountService $accounts)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('view-any-client');

        return view('admin.clients.index', array_merge($this->accounts->indexData($request), [
            'indexRoute' => 'billing.accounts',
            'showRoute' => 'billing.accounts.show',
        ]));
    }

    public function show(Client $client)
    {
        Gate::authorize('view', $client);

        return view('admin.clients.show', array_merge($this->accounts->showData($client), [
            'indexRoute' => 'billing.accounts',
            'showRoute' => 'billing.accounts.show',
        ]));
    }
}
