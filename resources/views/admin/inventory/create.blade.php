@extends('layouts.app')

@section('title', 'Add Inventory Item')

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Add Inventory Item</h1>
            <p class="mt-1 text-xs text-ink-muted">Register a new chemical, trap, equipment, or PPE item.</p>
        </div>
        <x-ui.button variant="ghost" :href="route('admin.inventory')">Back to inventory</x-ui.button>
    </div>

    <div class="max-w-3xl">
        @if ($errors->any())
            <div class="mb-6">
                <x-ui.alert type="error" title="Please review the form">{{ $errors->first() }}</x-ui.alert>
            </div>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('admin.inventory.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-5">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Item</h2>

                        <x-ui.input label="Item name" name="item_name" required />
                        <x-ui.select label="Category" name="category" required :placeholder="null" :options="[
                            'Chemical' => 'Chemical',
                            'Device/Trap' => 'Device / Trap',
                            'Equipment' => 'Equipment',
                            'PPE' => 'PPE',
                        ]" />
                        <x-ui.input label="Unit" name="unit" value="Pcs" required />
                        <x-ui.input label="Unit cost (₱)" name="unit_cost" type="number" step="0.01" min="0" value="0" required />
                    </div>

                    <div class="space-y-5">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Stock</h2>

                        <x-ui.input label="Opening quantity" name="quantity_in_stock" type="number" step="0.01" min="0" value="0" required />
                        <x-ui.input label="Minimum threshold" name="min_threshold" type="number" step="0.01" min="0" value="10" required />
                        <x-ui.input label="Batch number" name="batch_number" />
                        <x-ui.input label="Storage location" name="storage_location" />
                    </div>
                </div>

                <div class="flex gap-3 border-t border-border pt-6">
                    <x-ui.button variant="primary" type="submit">Create item</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('admin.inventory')">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
