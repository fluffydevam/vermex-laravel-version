<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContractStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Models\Contract;
use Illuminate\Support\Facades\Gate;

class ContractController extends Controller
{
    public function store(StoreContractRequest $request)
    {
        Gate::authorize('create', Contract::class);

        $validated = $request->validated();

        $hasDates = filled($validated['contract_start_date'] ?? null) && filled($validated['contract_end_date'] ?? null);
        $validated['contract_status'] = $hasDates ? ContractStatus::Active : ContractStatus::ToBeContracted;

        $contract = Contract::create($validated);

        return redirect()
            ->route('admin.clients.show', $contract->client_id)
            ->with('success', "Contract '{$contract->contract_name}' created.");
    }

    public function update(UpdateContractRequest $request, Contract $contract)
    {
        Gate::authorize('update', $contract);

        $contract->update($request->validated());

        return redirect()
            ->route('admin.clients.show', $contract->client_id)
            ->with('success', "Contract '{$contract->contract_name}' updated.");
    }

    public function destroy(Contract $contract)
    {
        Gate::authorize('delete', $contract);

        $clientId = $contract->client_id;
        $name = $contract->contract_name;
        $contract->delete();

        return redirect()
            ->route('admin.clients.show', $clientId)
            ->with('success', "Contract '{$name}' deleted.");
    }

    public function archive(Contract $contract)
    {
        Gate::authorize('archive', $contract);

        $contract->update(['contract_status' => ContractStatus::Archived]);

        return back()->with('success', "Contract '{$contract->contract_name}' archived.");
    }

    public function restore(Contract $contract)
    {
        Gate::authorize('restore', $contract);

        $contract->update(['contract_status' => ContractStatus::ToBeContracted]);

        return back()->with('success', "Contract '{$contract->contract_name}' restored.");
    }
}
