@php
    $isEdit = isset($job);
    $clientOptions = $clients->mapWithKeys(fn ($client) => [$client->id => $client->client_name])->all();
    $contractsByClient = $clients->mapWithKeys(fn ($client) => [
        (string) $client->id => $client->contracts->map(fn ($contract) => [
            'id' => (string) $contract->id,
            'label' => "{$contract->contract_name} ({$contract->contract_status->value})",
        ])->values()->all(),
    ])->all();
    $technicianOptions = ['' => 'Unassigned'] + $technicians->mapWithKeys(fn ($tech) => [$tech->id => $tech->full_name])->all();
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div
        class="space-y-5"
        x-data="{
            clientId: @js((string) old('client_id', $job->client_id ?? '')),
            contractId: @js((string) old('contract_id', $job->contract_id ?? '')),
            contractsByClient: @js($contractsByClient),
            get availableContracts() {
                return this.clientId ? (this.contractsByClient[this.clientId] ?? []) : [];
            },
        }"
        x-init="$watch('clientId', () => contractId = '')"
    >
        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Client</h2>

        <x-ui.select label="Client" name="client_id" x-model="clientId" :value="$job->client_id ?? null" required :placeholder="null" :options="$clientOptions" />
        <x-ui.select label="Contract" name="contract_id" x-model="contractId" x-init="$nextTick(() => { $el.value = contractId })" helper="Optional — links the visit to a service contract.">
            <option value="">No contract</option>
            <template x-for="contract in availableContracts" :key="contract.id">
                <option :value="contract.id" x-text="contract.label"></option>
            </template>
        </x-ui.select>
    </div>

    <div class="space-y-5">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Assignment</h2>

        <x-ui.select label="Technician" name="technician_id" :value="$job->technician_id ?? null" :options="$technicianOptions" />
        <x-ui.select label="Priority" name="priority" :value="$job->priority ?? 'Standard'" required :placeholder="null" :options="[
            'Standard' => 'Standard',
            'High' => 'High',
            'Urgent' => 'Urgent',
        ]" />
        @if ($isEdit)
            <x-ui.select label="Status" name="route_status" :value="$job->route_status" required :placeholder="null" :options="[
                'Scheduled' => 'Scheduled',
                'En route' => 'En route',
                'On site' => 'On site',
                'Completed' => 'Completed',
                'Delayed' => 'Delayed',
            ]" />
        @endif
    </div>

    <div class="space-y-5">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Visit</h2>

        <x-ui.input label="Service type" name="service_type" :value="$job->service_type ?? null" placeholder="General pest control" required />
        <x-ui.input label="Scheduled date" name="scheduled_date" type="date" :value="isset($job) ? $job->scheduled_date?->format('Y-m-d') : null" required />
        <x-ui.input label="Service window" name="service_window" :value="$job->service_window ?? null" placeholder="09:00–10:30" required />
    </div>

    <div class="space-y-5">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Notes</h2>

        <x-ui.textarea label="Dispatch notes" name="comments" :value="$job->comments ?? null" rows="3" />
        <x-ui.textarea label="Area findings" name="area_findings" :value="isset($job) ? collect($job->area_findings ?? [])->pluck('area')->filter()->implode(PHP_EOL) : null" rows="3" helper="One area per line." />
    </div>
</div>
