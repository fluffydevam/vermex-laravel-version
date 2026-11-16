@extends('layouts.app')

@section('title', 'Payments & Billing')

@push('styles')
    <style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
    <div
        x-data="{
            open: {{ ($errors->any() || request()->filled('contract_id')) ? 'true' : 'false' }},
            contract: '{{ old('contract_id', request('contract_id')) }}',
            balances: @js($contractBalances),
            fmt(value) { return '₱' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
            get balance() { return this.fmt(this.balances[this.contract] ?? 0); }
        }"
    >
        <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-lg font-bold tracking-tight text-ink">Payments &amp; Billing</h1>
                <p class="mt-1 text-xs text-ink-muted">Record collections and monitor live contract balances.</p>
            </div>
            <x-ui.button variant="primary" type="button" @click="open = true">Record payment</x-ui.button>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-ui.stat-card label="Collected (filtered)" :value="'₱' . number_format($metrics['collected'], 2)" variant="success" />
            <x-ui.stat-card label="Outstanding Balance" :value="'₱' . number_format($metrics['outstanding'], 2)" :variant="$metrics['outstanding'] > 0 ? 'danger' : 'success'" />
            <x-ui.stat-card label="Contract Portfolio" :value="'₱' . number_format($metrics['portfolio'], 2)" variant="brand" />
            <x-ui.stat-card label="Transactions (filtered)" :value="$metrics['transactions']" variant="info" />
        </div>

        <div class="mb-6 rounded-md border border-border bg-surface p-4 shadow-card">
            <form method="GET" action="{{ route($routeBase) }}" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-6" x-data="{ dateFilter: @js($filters['date_filter']) }">
                <div class="lg:col-span-2">
                    <x-ui.input label="Search" name="search" :value="$filters['search']" placeholder="Client, contract, reference…" />
                </div>
                <x-ui.select label="Date range" name="date_filter" x-model="dateFilter" :value="$filters['date_filter']" :placeholder="null" :options="[
                    'all' => 'All time',
                    'this-month' => 'This month',
                    'last-month' => 'Last month',
                    'year-to-date' => 'Year to date',
                    'custom' => 'Custom range',
                ]" />
                <x-ui.select label="Method" name="method" :value="$filters['method']" :options="['' => 'All methods'] + $methodOptions" />
                <x-ui.select label="Type" name="type" :value="$filters['type']" :options="['' => 'All types'] + $typeOptions" />
                <div class="flex gap-2">
                    <x-ui.button variant="primary" type="submit">Filter</x-ui.button>
                    <x-ui.button variant="ghost" :href="route($routeBase)">Reset</x-ui.button>
                </div>
                <div x-show="dateFilter === 'custom'" x-cloak class="flex items-end gap-2 sm:col-span-2 lg:col-span-6">
                    <x-ui.input label="From" name="start_date" type="date" :value="$filters['start_date']" />
                    <x-ui.input label="To" name="end_date" type="date" :value="$filters['end_date']" />
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-md border border-border bg-surface shadow-card">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-border bg-surface-muted">
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Ref / transaction</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Client &amp; contract</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Type</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Method</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Contract value</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Amount paid</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Remaining</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Date</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Logged by</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($payments as $payment)
                            @php $balance = $payment->contract?->final_balance ?? 0; @endphp
                            <tr class="transition-colors hover:bg-brand-50/40">
                                <td class="px-4 py-3 text-xs">
                                    <p class="font-mono font-semibold text-ink">#PAY-{{ str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT) }}</p>
                                    @if ($payment->reference_number)
                                        <p class="mt-0.5 font-mono text-[10px] text-ink-muted">Ref: {{ $payment->reference_number }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <p class="font-medium text-ink">{{ $payment->contract?->client?->client_name ?? '—' }}</p>
                                    <p class="mt-0.5 text-[10px] text-ink-muted">
                                        {{ $payment->contract?->contract_name ?? 'General' }}
                                        @if ($payment->contract)
                                            · <span class="font-mono">#CONTRACT-{{ $payment->contract->id }}</span>
                                        @endif
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <x-ui.badge variant="brand">{{ $payment->payment_type->value }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-xs text-ink/90">{{ $payment->payment_method->value }}</td>
                                <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">₱{{ number_format((float) ($payment->contract?->contract_value ?? 0), 2) }}</td>
                                <td class="px-4 py-3 text-right font-mono text-[11px] font-semibold text-success">₱{{ number_format((float) $payment->amount_paid, 2) }}</td>
                                <td class="px-4 py-3 text-right font-mono text-[11px] {{ $balance > 0 ? 'font-semibold text-danger' : 'text-ink-muted' }}">
                                    ₱{{ number_format((float) $balance, 2) }}
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px] text-ink-muted">{{ $payment->payment_date?->format('M d, Y g:i A') }}</td>
                                <td class="px-4 py-3 text-xs text-ink-muted">{{ $payment->user?->full_name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-2">
                                    <x-ui.empty-state title="No payments recorded" message="Record a payment or adjust the filters to see results." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <x-ui.pagination :paginator="$payments" />
        </div>

        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="payment-modal-title"
        >
            <div class="absolute inset-0 bg-black/50" @click="open = false"></div>

            <div class="relative w-full max-w-lg overflow-hidden rounded-md border border-border bg-surface shadow-card">
                <div class="flex items-center justify-between border-b border-border px-5 py-4">
                    <h2 id="payment-modal-title" class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Record contract payment</h2>
                    <button type="button" @click="open = false" class="text-ink-muted transition-colors hover:text-ink" aria-label="Close">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route($routeBase . '.store') }}" class="space-y-4 p-5">
                    @csrf

                    <x-ui.select
                        label="Contract"
                        name="contract_id"
                        x-model="contract"
                        :options="$contractOptions"
                        :placeholder="null"
                        required
                    />

                    <div class="flex items-center justify-between rounded-sm border border-border bg-surface-muted px-3 py-2 text-xs text-ink-muted">
                        <span>Current outstanding balance</span>
                        <span class="font-mono font-semibold text-danger" x-text="balance">₱0.00</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.input label="Amount paid (₱)" name="amount_paid" type="number" step="0.01" min="0.01" required />
                        <x-ui.select label="Payment type" name="payment_type" :value="'Downpayment'" :placeholder="null" :options="$typeOptions" required />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.select label="Payment method" name="payment_method" :value="'Cash'" :placeholder="null" :options="$methodOptions" required />
                        <x-ui.input label="Reference no." name="reference_number" placeholder="OR# or Ref#" />
                    </div>

                    <x-ui.input label="Payment date & time" name="payment_date" type="datetime-local" :value="now()->format('Y-m-d\TH:i')" required />

                    <x-ui.textarea label="Remarks" name="remarks" rows="2" placeholder="Optional notes…" />

                    <div class="flex justify-end gap-2 border-t border-border pt-4">
                        <x-ui.button variant="ghost" type="button" @click="open = false">Cancel</x-ui.button>
                        <x-ui.button variant="primary" type="submit">Submit payment</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
