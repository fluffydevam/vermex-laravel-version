@extends('layouts.app')

@section('title', 'Reports')

@push('styles')
    <style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between print:hidden">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">{{ $showOperations ? 'Reports & Analytics' : 'Financial Reports' }}</h1>
            <p class="mt-1 text-xs text-ink-muted">{{ $showOperations ? 'Financial, service, and field productivity performance.' : 'Collections, portfolio, and outstanding balances.' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button variant="ghost" type="button" onclick="window.print()">Print</x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="downloadPdf('printable-report', 'vermex-report-{{ $financial['from']->format('Y-m-d') }}-{{ $financial['to']->format('Y-m-d') }}.pdf')">Download PDF</x-ui.button>
        </div>
    </div>

    <div class="mb-6 rounded-md border border-border bg-surface p-4 shadow-card print:hidden">
        <form method="GET" action="{{ route($routeBase) }}" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-5" x-data="{ filter: @js($filters['filter']) }">
            <x-ui.select label="Period" name="filter" x-model="filter" :value="$filters['filter']" :placeholder="null" :options="[
                'this-month' => 'This month',
                'last-month' => 'Last month',
                'year-to-date' => 'Year to date',
                'custom' => 'Custom range',
            ]" />
            <x-ui.input label="From" name="start_date" type="date" :value="$filters['start_date']" x-bind:disabled="filter !== 'custom'" />
            <x-ui.input label="To" name="end_date" type="date" :value="$filters['end_date']" x-bind:disabled="filter !== 'custom'" />
            <div class="flex gap-2 lg:col-span-2">
                <x-ui.button variant="primary" type="submit">Apply</x-ui.button>
                <x-ui.button variant="ghost" :href="route($routeBase)">Reset</x-ui.button>
            </div>
        </form>
    </div>

    <div id="printable-report" class="space-y-6">
        <div class="hidden border-b border-border pb-4 print:block">
            <p class="text-sm font-bold uppercase tracking-wider text-ink">Vermex Pest Solutions</p>
            <p class="mt-1 text-xs text-ink-muted">{{ $showOperations ? 'Reports & Analytics' : 'Financial Report' }} · {{ $financial['period_label'] }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-ui.stat-card label="Revenue ({{ $financial['period_label'] }})" :value="'₱' . number_format($financial['revenue'], 2)" variant="success" />
            <x-ui.stat-card label="Outstanding Balance" :value="'₱' . number_format($financial['outstanding'], 2)" :variant="$financial['outstanding'] > 0 ? 'danger' : 'success'" />
            <x-ui.stat-card label="Contract Portfolio" :value="'₱' . number_format($financial['portfolio'], 2)" variant="brand" />
            <x-ui.stat-card label="Transactions" :value="$financial['transactions']" variant="info" />
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-ui.card>
                <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-ink-muted">Collections — last six months</h2>
                <div class="space-y-3">
                    @foreach ($financial['monthly'] as $month)
                        <div>
                            <div class="mb-1 flex items-baseline justify-between text-[11px]">
                                <span class="font-medium text-ink">{{ $month['label'] }}</span>
                                <span class="font-mono text-ink-muted">₱{{ number_format($month['total'], 2) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-sm bg-surface-muted">
                                <div class="h-full rounded-sm bg-brand-600" style="width: {{ $month['percent'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card :padded="false">
                <div class="border-b border-border px-5 py-4">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Collections by method</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted">
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Method</th>
                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Txns</th>
                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Total</th>
                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse ($financial['methods'] as $method)
                                <tr>
                                    <td class="px-4 py-3 text-xs font-medium text-ink">{{ $method->payment_method }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-[11px] text-ink-muted">{{ $method->transactions }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">₱{{ number_format((float) $method->total, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-[11px] text-ink-muted">
                                        {{ $financial['revenue'] > 0 ? number_format(((float) $method->total / $financial['revenue']) * 100, 1) : '0.0' }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2">
                                        <x-ui.empty-state title="No collections in this period" message="Payments recorded in the selected period will appear here." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card :padded="false">
            <div class="border-b border-border px-5 py-4">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Contract portfolio by status</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-border bg-surface-muted">
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Status</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Contracts</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($financial['statuses'] as $status)
                            <tr>
                                <td class="px-4 py-3 text-xs font-medium text-ink">
                                    {{ $status->contract_status instanceof \BackedEnum ? $status->contract_status->value : $status->contract_status }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-[11px] text-ink-muted">{{ $status->count }}</td>
                                <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">₱{{ number_format((float) $status->value, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-2">
                                    <x-ui.empty-state title="No contracts" message="Contract data will appear here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        @if ($showOperations && $operations)
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.stat-card label="Completed Jobs" :value="$operations['jobs_completed'] . ' / ' . $operations['jobs_total']" variant="success" />
                <x-ui.stat-card label="Fulfillment Rate" :value="$operations['fulfillment_rate'] . '%'" :variant="$operations['fulfillment_rate'] >= 80 ? 'success' : 'warning'" />
                <x-ui.stat-card label="Inspections" :value="$operations['inspections']" variant="info" />
                <x-ui.stat-card label="Chemical Cost" :value="'₱' . number_format($operations['chemical_cost'], 2)" variant="brand" />
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-ui.card>
                    <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-ink-muted">Service type distribution</h2>
                    @php $peakService = max(1, (int) $operations['service_types']->max('count')); @endphp
                    <div class="space-y-3">
                        @forelse ($operations['service_types'] as $service)
                            <div>
                                <div class="mb-1 flex items-baseline justify-between text-[11px]">
                                    <span class="font-medium text-ink">{{ $service->service_type }}</span>
                                    <span class="font-mono text-ink-muted">{{ $service->count }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-sm bg-surface-muted">
                                    <div class="h-full rounded-sm bg-brand-500" style="width: {{ round(($service->count / $peakService) * 100, 1) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-ink-muted">No job orders in this period.</p>
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card :padded="false">
                    <div class="border-b border-border px-5 py-4">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Chemical consumption</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr class="border-b border-border bg-surface-muted">
                                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Item</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Used</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse ($operations['top_chemicals'] as $chemical)
                                    <tr>
                                        <td class="px-4 py-3 text-xs font-medium text-ink">{{ $chemical->item_name }}</td>
                                        <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">{{ number_format((float) $chemical->total_qty, 2) }} {{ $chemical->unit }}</td>
                                        <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">₱{{ number_format((float) $chemical->estimated_cost, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-2">
                                            <x-ui.empty-state title="No chemical usage" message="Materials logged on job orders will appear here." />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>

            <x-ui.card :padded="false">
                <div class="border-b border-border px-5 py-4">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Field technician productivity</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted">
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Technician</th>
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Region</th>
                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Completed jobs</th>
                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Inspections</th>
                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Materials used</th>
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse ($operations['technicians'] as $technician)
                                <tr>
                                    <td class="px-4 py-3 text-xs font-medium text-ink">{{ $technician->full_name }}</td>
                                    <td class="px-4 py-3 text-xs text-ink-muted">{{ $technician->sector_region ?: '—' }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">{{ $technician->completed_jobs }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">{{ $technician->inspection_count }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">{{ number_format((float) $technician->material_quantity, 2) }}</td>
                                    <td class="px-4 py-3 text-xs">
                                        <x-ui.badge :variant="$technician->status->value === 'active' ? 'success' : 'default'">{{ ucfirst($technician->status->value) }}</x-ui.badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-2">
                                        <x-ui.empty-state title="No technicians" message="Active field technicians will appear here." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>

    @include('partials.pdf')
@endsection
