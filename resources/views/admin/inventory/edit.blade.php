@extends('layouts.app')

@section('title', 'Edit Inventory Item')

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">{{ $item->item_name }}</h1>
            <p class="mt-1 font-mono text-[11px] text-ink-muted">
                Item #{{ str_pad((string) $item->id, 4, '0', STR_PAD_LEFT) }} · {{ $item->status }} ·
                {{ number_format((float) $item->quantity_in_stock, 2) }} {{ $item->unit }} on hand
            </p>
        </div>
        <x-ui.button variant="ghost" :href="route('admin.inventory')">Back to inventory</x-ui.button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($errors->any())
                <div class="mb-6">
                    <x-ui.alert type="error" title="Please review the form">{{ $errors->first() }}</x-ui.alert>
                </div>
            @endif

            <x-ui.card>
                <form method="POST" action="{{ route('admin.inventory.update', $item) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <x-ui.input label="Item name" name="item_name" :value="$item->item_name" required />
                        <x-ui.select label="Category" name="category" :value="$item->category" required :placeholder="null" :options="[
                            'Chemical' => 'Chemical',
                            'Device/Trap' => 'Device / Trap',
                            'Equipment' => 'Equipment',
                            'PPE' => 'PPE',
                        ]" />
                        <x-ui.input label="Unit" name="unit" :value="$item->unit" required />
                        <x-ui.input label="Unit cost (₱)" name="unit_cost" type="number" step="0.01" min="0" :value="$item->unit_cost" required />
                        <x-ui.input label="Minimum threshold" name="min_threshold" type="number" step="0.01" min="0" :value="$item->min_threshold" required />
                        <x-ui.input label="Batch number" name="batch_number" :value="$item->batch_number" />
                        <x-ui.input label="Storage location" name="storage_location" :value="$item->storage_location" />
                    </div>

                    <div class="flex gap-3 border-t border-border pt-6">
                        <x-ui.button variant="primary" type="submit">Save changes</x-ui.button>
                        <x-ui.button variant="ghost" :href="route('admin.inventory')">Cancel</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <div class="mt-6">
                <x-ui.card :padded="false">
                    <div class="border-b border-border px-5 py-4">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Movement history</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr class="border-b border-border bg-surface-muted">
                                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Date</th>
                                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Action</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Change</th>
                                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">By</th>
                                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse ($logs as $log)
                                    <tr>
                                        <td class="px-4 py-3 font-mono text-[11px] text-ink-muted">{{ $log->created_at?->format('M d, Y H:i') }}</td>
                                        <td class="px-4 py-3 text-xs text-ink/90">{{ $log->action_type }}</td>
                                        <td class="px-4 py-3 text-right font-mono text-[11px] {{ $log->isAddition ? 'text-success' : 'text-danger' }}">
                                            {{ $log->isAddition ? '+' : '' }}{{ number_format((float) $log->quantity_changed, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-ink-muted">{{ $log->performedBy?->full_name ?? 'System' }}</td>
                                        <td class="px-4 py-3 text-xs text-ink-muted">{{ $log->remarks ?? '—' }}</td>
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
        </div>

        <x-ui.card class="h-fit">
            <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-ink-muted">Adjust stock</h2>
            <form method="POST" action="{{ route('admin.inventory.adjust', $item) }}" class="space-y-4">
                @csrf
                <x-ui.select label="Movement" name="adjustment_type" :placeholder="null" :options="[
                    'add' => 'Add stock',
                    'subtract' => 'Deduct stock',
                    'set' => 'Set exact count',
                ]" />
                <x-ui.input label="Amount ({{ $item->unit }})" name="adjust_amount" type="number" step="0.01" min="0.01" required />
                <x-ui.input label="Remarks" name="remarks" placeholder="Optional note" />
                <x-ui.button variant="primary" type="submit" class="w-full">Apply adjustment</x-ui.button>
            </form>
        </x-ui.card>
    </div>
@endsection
