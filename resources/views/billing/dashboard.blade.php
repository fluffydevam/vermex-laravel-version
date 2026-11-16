@extends('layouts.app')

@section('title', 'Billing Dashboard')

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Billing Dashboard</h1>
            <p class="mt-1 text-xs text-ink-muted">Collections and account position for {{ now()->format('F Y') }}.</p>
        </div>
        <x-ui.button variant="ghost" :href="route('admin.clients', ['tab' => 'contracts'])">Contract ledger</x-ui.button>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Collected This Month" :value="'₱' . number_format($stats['collected_month'], 2)" variant="brand" />
        <x-ui.stat-card label="Collected All Time" :value="'₱' . number_format($stats['collected_total'], 2)" variant="success" />
        <x-ui.stat-card label="Payments This Month" :value="$stats['payments_month']" variant="info" />
        <x-ui.stat-card label="Active Contracts" :value="$stats['active_contracts']" />
    </div>

    <x-ui.card :padded="false">
        <div class="flex items-center justify-between border-b border-border px-5 py-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Recent payments</h2>
            <span class="font-mono text-[11px] text-ink-muted">{{ $stats['active_clients'] }} active clients</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-border bg-surface-muted">
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Date</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Client</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Contract</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Method</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($recentPayments as $payment)
                        <tr class="transition-colors hover:bg-brand-50/40">
                            <td class="px-4 py-3 font-mono text-[11px] text-ink-muted">{{ $payment->payment_date?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-xs font-medium text-ink">{{ $payment->contract?->client?->client_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-ink/90">{{ $payment->contract?->contract_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-ink/90">{{ $payment->payment_method->value }}</td>
                            <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">₱{{ number_format((float) $payment->amount_paid, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2">
                                <x-ui.empty-state title="No payments yet" message="Recorded payments will appear here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
@endsection
