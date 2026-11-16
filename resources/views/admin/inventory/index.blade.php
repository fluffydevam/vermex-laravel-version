@extends('layouts.app')

@section('title', 'Inventory')

@php
    $categoryOptions = ['' => 'All categories'];
    foreach ($categories as $case) {
        $categoryOptions[$case->value] = $case->value;
    }
@endphp

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Inventory</h1>
            <p class="mt-1 text-xs text-ink-muted">Chemical, trap, equipment, and PPE stock position.</p>
        </div>
        <x-ui.button variant="primary" :href="route('admin.inventory.create')">Add item</x-ui.button>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Tracked Items" :value="$stats['items']" />
        <x-ui.stat-card label="Low Stock" :value="$stats['low']" :variant="$stats['low'] > 0 ? 'warning' : 'success'" />
        <x-ui.stat-card label="Out of Stock" :value="$stats['out']" :variant="$stats['out'] > 0 ? 'danger' : 'success'" />
        <x-ui.stat-card label="Stock Value" :value="'₱' . number_format($stats['value'], 2)" variant="info" />
    </div>

    <div class="mb-6 rounded-md border border-border bg-surface p-4 shadow-card">
        <form method="GET" action="{{ route('admin.inventory') }}" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-ui.input label="Search" name="search" :value="$search" placeholder="Item name…" />
            <x-ui.select label="Category" name="category" :value="$category" :options="$categoryOptions" />
            <x-ui.select label="Stock level" name="stock" :value="$stock" :options="[
                '' => 'All levels',
                'in' => 'Healthy',
                'low' => 'Low stock',
                'out' => 'Out of stock',
            ]" />
            <div class="flex gap-2">
                <x-ui.button variant="primary" type="submit">Filter</x-ui.button>
                <x-ui.button variant="ghost" :href="route('admin.inventory')">Reset</x-ui.button>
            </div>
        </form>
    </div>

    @include('inventory._table', [
        'items' => $items,
        'adjustRoute' => 'admin.inventory.adjust',
        'editRoute' => 'admin.inventory.edit',
    ])

    <div class="mt-8">
        <x-ui.card :padded="false">
            <div class="border-b border-border px-5 py-4">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Recent stock movements</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-border bg-surface-muted">
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Date</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Item</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Action</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Change</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-4 py-3 font-mono text-[11px] text-ink-muted">{{ $log->created_at?->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-3 text-xs text-ink">{{ $log->inventoryItem?->item_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-ink/90">{{ $log->action_type }}</td>
                                <td class="px-4 py-3 text-right font-mono text-[11px] {{ $log->isAddition ? 'text-success' : 'text-danger' }}">
                                    {{ $log->isAddition ? '+' : '' }}{{ number_format((float) $log->quantity_changed, 2) }}
                                </td>
                                <td class="px-4 py-3 text-xs text-ink-muted">{{ $log->performedBy?->full_name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-2">
                                    <x-ui.empty-state title="No movements yet" message="Stock adjustments will be recorded here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
@endsection
