@extends('layouts.app')

@section('title', $client->client_name . ' · Clients')

@php use App\Enums\ClientStatus; use App\Enums\ContractStatus; @endphp

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-lg font-bold tracking-tight text-ink">{{ $client->client_name }}</h1>
                <x-ui.badge :variant="match ($client->status) {
                    ClientStatus::Active => 'success',
                    ClientStatus::Inactive => 'warning',
                    default => 'danger',
                }">{{ ucfirst($client->status->value) }}</x-ui.badge>
                <x-ui.badge variant="brand">{{ $client->client_type->value }}</x-ui.badge>
            </div>
            <p class="mt-1 font-mono text-[11px] text-ink-muted">Client #{{ str_pad((string) $client->id, 4, '0', STR_PAD_LEFT) }} · since {{ $client->created_at?->format('M d, Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button variant="ghost" :href="route($indexRoute)">Back to list</x-ui.button>
            @can('update', $client)
                <x-ui.button variant="ghost" :href="route('admin.clients.edit', $client)">Edit client</x-ui.button>
            @endcan
            @if ($client->status !== ClientStatus::Archived)
                @can('archive', $client)
                    <form action="{{ route('admin.clients.archive', $client) }}" method="POST" onsubmit="return confirm('Archive this client?')">
                        @csrf
                        @method('DELETE')
                        <x-ui.button variant="danger" type="submit">Archive</x-ui.button>
                    </form>
                @endcan
            @else
                @can('restore', $client)
                    <form action="{{ route('admin.clients.restore', $client) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <x-ui.button variant="success" type="submit">Restore</x-ui.button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Contract Value" :value="'₱' . number_format($contractValue, 2)" />
        <x-ui.stat-card label="Paid to Date" :value="'₱' . number_format($paidTotal, 2)" variant="success" />
        <x-ui.stat-card label="Balance" :value="'₱' . number_format($balance, 2)" :variant="$balance > 0 ? 'danger' : 'brand'" />
        <x-ui.stat-card label="Contracts" :value="$contracts->count()" variant="info" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-ink-muted">Contact Details</h2>
            <dl class="space-y-3 text-xs">
                <div>
                    <dt class="text-ink-muted">Contact person</dt>
                    <dd class="mt-0.5 font-medium text-ink">{{ $client->first_name }} {{ $client->last_name }}</dd>
                </div>
                <div>
                    <dt class="text-ink-muted">Phone</dt>
                    <dd class="mt-0.5 font-mono text-ink">{{ $client->phone_number }}</dd>
                </div>
                <div>
                    <dt class="text-ink-muted">Email</dt>
                    <dd class="mt-0.5 font-mono text-ink">{{ $client->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-ink-muted">Service address</dt>
                    <dd class="mt-0.5 text-ink">{{ $client->address }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-md border border-border bg-surface shadow-card">
                <div class="flex items-center justify-between border-b border-border px-5 py-4">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Contracts</h2>
                    <span class="font-mono text-[11px] text-ink-muted">{{ $contracts->count() }} total</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted">
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Name</th>
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Term</th>
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Status</th>
                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Value</th>
                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse ($contracts as $contract)
                                <tr class="transition-colors hover:bg-brand-50/40">
                                    <td class="px-4 py-3 text-xs font-medium text-ink">{{ $contract->contract_name }}</td>
                                    <td class="px-4 py-3 font-mono text-[11px] text-ink-muted">
                                        {{ $contract->contract_start_date?->format('M d, Y') ?? '—' }}
                                        →
                                        {{ $contract->contract_end_date?->format('M d, Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <x-ui.badge :variant="match ($contract->contract_status) {
                                            ContractStatus::Active => 'success',
                                            ContractStatus::ExpiringSoon => 'warning',
                                            ContractStatus::Expired, ContractStatus::Cancelled => 'danger',
                                            default => 'default',
                                        }">{{ str_replace('_', ' ', ucfirst($contract->contract_status->value)) }}</x-ui.badge>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">₱{{ number_format((float) $contract->contract_value, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-[11px] {{ $contract->final_balance > 0 ? 'text-danger' : 'text-ink-muted' }}">
                                        ₱{{ number_format($contract->final_balance, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-2">
                                        <x-ui.empty-state title="No contracts yet" :message="auth()->user()->can('create', App\Models\Contract::class) ? 'Add the first contract using the form below.' : 'No contracts have been created for this client.'" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @can('create', App\Models\Contract::class)
                    <details class="border-t border-border">
                        <summary class="cursor-pointer px-5 py-3 text-xs font-medium text-link hover:text-link-hover">+ Add contract</summary>
                        <form method="POST" action="{{ route('admin.contracts.store') }}" class="grid grid-cols-1 gap-4 border-t border-border bg-surface-muted p-5 sm:grid-cols-2">
                            @csrf
                            <input type="hidden" name="client_id" value="{{ $client->id }}">

                            <div class="sm:col-span-2">
                                <x-ui.input label="Contract name" name="contract_name" required />
                            </div>
                            <x-ui.input label="Start date" name="contract_start_date" type="date" />
                            <x-ui.input label="End date" name="contract_end_date" type="date" />
                            <x-ui.input label="Contract value (₱)" name="contract_value" type="number" step="0.01" min="0" required />
                            <x-ui.select label="Status" name="contract_status" :options="[
                                'to_be_contracted' => 'To be contracted',
                                'active' => 'Active',
                                'expiring_soon' => 'Expiring soon',
                            ]" :placeholder="null" />
                            <div class="sm:col-span-2">
                                <x-ui.button variant="primary" type="submit">Create contract</x-ui.button>
                            </div>
                        </form>
                    </details>
                @endcan
            </div>

            <div class="rounded-md border border-border bg-surface shadow-card">
                <div class="flex items-center justify-between border-b border-border px-5 py-4">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Recent payments</h2>
                    <span class="font-mono text-[11px] text-ink-muted">Latest 10</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted">
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Date</th>
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Contract</th>
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Type</th>
                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse ($payments as $payment)
                                <tr class="transition-colors hover:bg-brand-50/40">
                                    <td class="px-4 py-3 font-mono text-[11px] text-ink-muted">{{ $payment->payment_date?->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-xs text-ink">{{ $payment->contract?->contract_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs text-ink/90">{{ $payment->payment_type->value }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">₱{{ number_format((float) $payment->amount_paid, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2">
                                        <x-ui.empty-state title="No payments recorded" message="Payments recorded against this client's contracts will appear here." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
