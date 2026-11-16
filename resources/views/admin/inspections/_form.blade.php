@php
    $inspection = $inspection ?? null;
    $isEdit = isset($inspection);
    $locked = $locked ?? false;
    $clients = $clients ?? collect();
    $technicians = $technicians ?? collect();
    $conditionMap = collect($inspection->conditions_json ?? [])->keyBy('label');
    $findings = $inspection->findings_json ?? [];
    $clientOptions = $clients->mapWithKeys(fn ($client) => [$client->id => $client->client_name])->all();
    $contractOptions = ['' => 'No contract'];
    foreach ($clients as $client) {
        foreach ($client->contracts as $contract) {
            $contractOptions[$contract->id] = "{$client->client_name} — {$contract->contract_name}";
        }
    }
    $technicianOptions = ['' => 'Unassigned'] + $technicians->mapWithKeys(fn ($tech) => [$tech->id => $tech->full_name])->all();
@endphp

<div class="space-y-8">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="space-y-5">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Client &amp; contract</h2>

            @if ($locked)
                <input type="hidden" name="client_id" value="{{ $inspection->client_id }}">
                <input type="hidden" name="contract_id" value="{{ $inspection->contract_id }}">
                <input type="hidden" name="technician_id" value="{{ $inspection->technician_id }}">
                <dl class="space-y-2 rounded-sm border border-border bg-surface-muted p-4 text-xs">
                    <div><dt class="text-ink-muted">Client</dt><dd class="font-medium text-ink">{{ $inspection->client_name }}</dd></div>
                    <div><dt class="text-ink-muted">Address</dt><dd class="text-ink">{{ $inspection->service_address }}</dd></div>
                    <div><dt class="text-ink-muted">Contract</dt><dd class="text-ink">{{ $inspection->contract?->contract_name ?? '—' }}</dd></div>
                </dl>
            @else
                <x-ui.select label="Client" name="client_id" :value="$inspection->client_id ?? null" required :placeholder="null" :options="$clientOptions" />
                <x-ui.select label="Contract" name="contract_id" :value="$inspection->contract_id ?? null" :options="$contractOptions" />
                <x-ui.select label="Technician" name="technician_id" :value="$inspection->technician_id ?? null" :options="$technicianOptions" />
            @endif
        </div>

        <div class="space-y-5">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Visit</h2>

            <x-ui.select label="Status" name="inspection_status" :value="$inspection->inspection_status ?? 'Pending Visit'" required :placeholder="null" :options="[
                'Pending Visit' => 'Pending Visit',
                'Inspection Complete' => 'Inspection Complete',
                'Inspection Complete (Pests Found)' => 'Inspection Complete (Pests Found)',
            ]" />
            <x-ui.input label="Inspection date" name="inspection_date" type="date" :value="isset($inspection) ? $inspection->inspection_date?->format('Y-m-d') : null" />
            <div class="grid grid-cols-2 gap-4">
                <x-ui.input label="Time in" name="time_in" type="time" :value="isset($inspection) && $inspection->time_in ? substr($inspection->time_in, 0, 5) : null" />
                <x-ui.input label="Time out" name="time_out" type="time" :value="isset($inspection) && $inspection->time_out ? substr($inspection->time_out, 0, 5) : null" />
            </div>
            <x-ui.input label="Vermex representative" name="vermex_representative" :value="$inspection->vermex_representative ?? null" />
            <x-ui.input label="Client representative" name="client_representative" :value="$inspection->client_representative ?? null" />
        </div>
    </div>

    <div>
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-ink-muted">Devices installed</h2>
        <div class="overflow-hidden rounded-md border border-border">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-border bg-surface-muted">
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Device</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Quantity</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ([
                        'ilt' => 'ILT',
                        'rat_cage' => 'Rat Cage',
                        'rat_bait' => 'Rat Bait Station',
                        'glue_trap' => 'Glue Trap',
                    ] as $key => $label)
                        <tr>
                            <td class="px-4 py-3 text-xs font-medium text-ink">{{ $label }}</td>
                            <td class="px-4 py-3">
                                <x-ui.input name="{{ $key }}_qty" type="number" min="0" :value="$inspection->{$key . '_qty'} ?? 0" />
                            </td>
                            <td class="px-4 py-3">
                                <x-ui.input name="{{ $key }}_remarks" :value="$inspection->{$key . '_remarks'} ?? null" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-ink-muted">Site conditions noted</h2>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ($conditionLabels as $index => $label)
                @php $row = $conditionMap->get($label); @endphp
                <div class="rounded-sm border border-border bg-surface p-3">
                    <label class="flex items-start gap-2 text-xs font-medium text-ink">
                        <input
                            type="checkbox"
                            name="conditions[{{ $index }}][status]"
                            value="yes"
                            @checked(($row['status'] ?? '') === 'yes')
                            class="mt-0.5 rounded-sm border-border text-brand-600 focus:ring-brand-600"
                        >
                        <span>{{ $label }}</span>
                    </label>
                    <input
                        type="text"
                        name="conditions[{{ $index }}][area]"
                        value="{{ $row['area'] ?? '' }}"
                        placeholder="Area / location"
                        class="mt-2 block w-full rounded-sm border border-border bg-surface px-2 py-1 text-[11px] text-ink placeholder-ink-muted/60 focus:border-brand-600 focus:ring-brand-600/20"
                    >
                </div>
            @endforeach
        </div>
    </div>

    <div x-data='{ findings: @json($findings) }'>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Findings &amp; actions</h2>
            <button type="button" @click="findings.push({ area: '', findings: '', action_taken: '', remarks: '' })" class="text-xs font-medium text-link hover:text-link-hover">
                + Add row
            </button>
        </div>

        <div class="space-y-3">
            <template x-for="(finding, index) in findings" :key="index">
                <div class="grid grid-cols-1 gap-3 rounded-sm border border-border bg-surface p-3 sm:grid-cols-4">
                    <input type="text" x-bind:name="'findings['+index+'][area]'" x-model="finding.area" placeholder="Area" class="block w-full rounded-sm border border-border bg-surface px-2 py-1.5 text-xs text-ink placeholder-ink-muted/60 focus:border-brand-600">
                    <input type="text" x-bind:name="'findings['+index+'][findings]'" x-model="finding.findings" placeholder="Findings" class="block w-full rounded-sm border border-border bg-surface px-2 py-1.5 text-xs text-ink placeholder-ink-muted/60 focus:border-brand-600">
                    <input type="text" x-bind:name="'findings['+index+'][action_taken]'" x-model="finding.action_taken" placeholder="Action taken" class="block w-full rounded-sm border border-border bg-surface px-2 py-1.5 text-xs text-ink placeholder-ink-muted/60 focus:border-brand-600">
                    <div class="flex gap-2">
                        <input type="text" x-bind:name="'findings['+index+'][remarks]'" x-model="finding.remarks" placeholder="Remarks" class="block w-full rounded-sm border border-border bg-surface px-2 py-1.5 text-xs text-ink placeholder-ink-muted/60 focus:border-brand-600">
                        <button type="button" @click="findings.splice(index, 1)" class="px-2 text-xs font-medium text-danger hover:text-danger/80">Remove</button>
                    </div>
                </div>
            </template>

            <p x-show="findings.length === 0" class="rounded-sm border border-dashed border-border px-4 py-6 text-center text-xs text-ink-muted">
                No findings recorded. Add a row if pests or conditions were observed.
            </p>
        </div>
    </div>
</div>
