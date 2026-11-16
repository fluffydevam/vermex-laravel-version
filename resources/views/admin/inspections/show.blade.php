@extends('layouts.app')

@section('title', 'Inspection #' . str_pad((string) $inspection->id, 4, '0', STR_PAD_LEFT))

@php use App\Enums\InspectionStatus; @endphp

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between print:hidden">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-lg font-bold tracking-tight text-ink">{{ $inspection->client_name }}</h1>
                <x-ui.badge :variant="match ($inspection->inspection_status) {
                    InspectionStatus::PestsFound => 'danger',
                    InspectionStatus::InspectionComplete => 'success',
                    default => 'warning',
                }">{{ $inspection->inspection_status->value }}</x-ui.badge>
            </div>
            <p class="mt-1 font-mono text-[11px] text-ink-muted">
                Inspection #{{ str_pad((string) $inspection->id, 4, '0', STR_PAD_LEFT) }} ·
                {{ $inspection->inspection_date?->format('M d, Y') ?? 'No date' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button variant="ghost" :href="route('admin.inspections')">Back</x-ui.button>
            <x-ui.button variant="ghost" :href="route('admin.inspections.edit', $inspection)">Edit</x-ui.button>
            <x-ui.button variant="ghost" type="button" onclick="window.print()">Print</x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="downloadPdf('printable-report', 'vermex-inspection-{{ str_pad((string) $inspection->id, 4, '0', STR_PAD_LEFT) }}.pdf')">Download PDF</x-ui.button>
        </div>
    </div>

    <div id="printable-report" class="space-y-6">
        <x-ui.card>
            <div class="mb-4 flex items-baseline justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Visit details</h2>
                <span class="font-mono text-[11px] text-ink-muted">{{ $inspection->account_type }}</span>
            </div>
            <dl class="grid grid-cols-1 gap-4 text-xs sm:grid-cols-2 lg:grid-cols-3">
                <div><dt class="text-ink-muted">Service address</dt><dd class="mt-0.5 text-ink">{{ $inspection->service_address }}</dd></div>
                <div><dt class="text-ink-muted">Contract</dt><dd class="mt-0.5 text-ink">{{ $inspection->contract?->contract_name ?? '—' }}</dd></div>
                <div><dt class="text-ink-muted">Technician</dt><dd class="mt-0.5 text-ink">{{ $inspection->technician?->full_name ?? 'Unassigned' }}</dd></div>
                <div><dt class="text-ink-muted">Time in</dt><dd class="mt-0.5 font-mono text-ink">{{ $inspection->time_in ?? '—' }}</dd></div>
                <div><dt class="text-ink-muted">Time out</dt><dd class="mt-0.5 font-mono text-ink">{{ $inspection->time_out ?? '—' }}</dd></div>
                <div><dt class="text-ink-muted">Pests found</dt><dd class="mt-0.5 text-ink">{{ $inspection->pests_found ? 'Yes' : 'No' }}</dd></div>
                <div><dt class="text-ink-muted">Vermex representative</dt><dd class="mt-0.5 text-ink">{{ $inspection->vermex_representative ?? '—' }}</dd></div>
                <div><dt class="text-ink-muted">Client representative</dt><dd class="mt-0.5 text-ink">{{ $inspection->client_representative ?? '—' }}</dd></div>
            </dl>
        </x-ui.card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-ui.card :padded="false">
                <div class="border-b border-border px-5 py-4"><h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Devices installed</h2></div>
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-border bg-surface-muted">
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Device</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Qty</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach (['ilt' => 'ILT', 'rat_cage' => 'Rat Cage', 'rat_bait' => 'Rat Bait Station', 'glue_trap' => 'Glue Trap'] as $key => $label)
                            <tr>
                                <td class="px-4 py-3 text-xs font-medium text-ink">{{ $label }}</td>
                                <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">{{ $inspection->{$key . '_qty'} }}</td>
                                <td class="px-4 py-3 text-xs text-ink-muted">{{ $inspection->{$key . '_remarks'} ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-ui.card>

            <x-ui.card :padded="false">
                <div class="border-b border-border px-5 py-4"><h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Site conditions noted</h2></div>
                <div class="p-5">
                    @forelse ($inspection->conditions_json ?? [] as $condition)
                        <div class="flex items-start justify-between gap-3 border-b border-border py-2 text-xs last:border-0">
                            <span class="text-ink">{{ $condition['label'] ?? 'Condition' }}</span>
                            <span class="text-right text-ink-muted">
                                {{ ($condition['status'] ?? '') === 'yes' ? 'Noted' : 'Checked' }}
                                @if (! empty($condition['area'])) · {{ $condition['area'] }} @endif
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-ink-muted">No site conditions recorded.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <x-ui.card :padded="false">
            <div class="border-b border-border px-5 py-4"><h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Findings &amp; actions</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-border bg-surface-muted">
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Area</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Findings</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Action taken</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($inspection->findings_json ?? [] as $finding)
                            <tr>
                                <td class="px-4 py-3 text-xs font-medium text-ink">{{ $finding['area'] ?: '—' }}</td>
                                <td class="px-4 py-3 text-xs text-ink/90">{{ $finding['findings'] ?: '—' }}</td>
                                <td class="px-4 py-3 text-xs text-ink/90">{{ $finding['action_taken'] ?: '—' }}</td>
                                <td class="px-4 py-3 text-xs text-ink-muted">{{ $finding['remarks'] ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-2">
                                    <x-ui.empty-state title="No findings" message="No pest or site findings were recorded for this visit." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    @include('partials.pdf')
@endsection
