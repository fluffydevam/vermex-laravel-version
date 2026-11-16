@extends('layouts.app')

@section('title', 'Inventory Dashboard')

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Inventory Dashboard</h1>
            <p class="mt-1 text-xs text-ink-muted">Chemical stock position as of {{ now()->format('F j, Y') }}.</p>
        </div>
        <x-ui.button variant="ghost" :href="route('admin.dashboard')">Operations overview</x-ui.button>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Tracked Items" :value="$stats['items']" />
        <x-ui.stat-card label="Low Stock" :value="$stats['low_stock']" :variant="$stats['low_stock'] > 0 ? 'warning' : 'success'" />
        <x-ui.stat-card label="Out of Stock" :value="$stats['out_of_stock']" :variant="$stats['out_of_stock'] > 0 ? 'danger' : 'success'" />
        <x-ui.stat-card label="Adjustments This Month" :value="$stats['adjustments_month']" variant="info" />
    </div>

    <x-ui.card :padded="false">
        <div class="flex items-center justify-between border-b border-border px-5 py-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Items at or below threshold</h2>
            <span class="font-mono text-[11px] text-ink-muted">{{ $lowStockItems->count() }} shown</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-border bg-surface-muted">
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Item</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Category</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">In Stock</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Threshold</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Unit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($lowStockItems as $item)
                        <tr class="transition-colors hover:bg-brand-50/40">
                            <td class="px-4 py-3 text-xs font-medium text-ink">{{ $item->item_name }}</td>
                            <td class="px-4 py-3 text-xs text-ink/90">{{ $item->category?->value ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-mono text-[11px] {{ $item->quantity_in_stock <= 0 ? 'text-danger font-semibold' : 'text-ink' }}">
                                {{ number_format((float) $item->quantity_in_stock, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-[11px] text-ink-muted">{{ number_format((float) $item->min_threshold, 2) }}</td>
                            <td class="px-4 py-3 text-xs text-ink-muted">{{ $item->unit_of_measure }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2">
                                <x-ui.empty-state title="Stock levels healthy" message="No items are at or below their minimum threshold." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
@endsection
