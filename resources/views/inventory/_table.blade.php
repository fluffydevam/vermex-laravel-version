@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $items */
    $adjustRoute = $adjustRoute ?? 'admin.inventory.adjust';
    $editRoute = $editRoute ?? null;
    $showUnitCost = $showUnitCost ?? true;
@endphp

<div class="overflow-hidden rounded-md border border-border bg-surface shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left">
            <thead>
                <tr class="border-b border-border bg-surface-muted">
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Item</th>
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Category</th>
                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Stock</th>
                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Threshold</th>
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Status</th>
                    @if ($showUnitCost)
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Unit cost</th>
                    @endif
                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($items as $item)
                    @php
                        $status = $item->status;
                        $variant = match ($status) {
                            'In Stock' => 'success',
                            'Low Stock' => 'warning',
                            default => 'danger',
                        };
                    @endphp
                    <tr class="transition-colors hover:bg-brand-50/40">
                        <td class="px-4 py-3 text-xs">
                            <p class="font-medium text-ink">{{ $item->item_name }}</p>
                            <p class="mt-0.5 font-mono text-[10px] text-ink-muted">
                                {{ $item->batch_number ?: 'No batch' }} · {{ $item->storage_location ?: 'Unassigned storage' }}
                            </p>
                        </td>
                        <td class="px-4 py-3 text-xs text-ink/90">{{ $item->category->value }}</td>
                        <td class="px-4 py-3 text-right font-mono text-[11px] {{ (float) $item->quantity_in_stock <= 0 ? 'font-semibold text-danger' : 'text-ink' }}">
                            {{ number_format((float) $item->quantity_in_stock, 2) }} <span class="text-ink-muted">{{ $item->unit }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-[11px] text-ink-muted">{{ number_format((float) $item->min_threshold, 2) }}</td>
                        <td class="px-4 py-3 text-xs"><x-ui.badge :variant="$variant">{{ $status }}</x-ui.badge></td>
                        @if ($showUnitCost)
                            <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">₱{{ number_format((float) $item->unit_cost, 2) }}</td>
                        @endif
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-start justify-end gap-2">
                                @if ($editRoute)
                                    <x-ui.button variant="ghost" size="sm" :href="route($editRoute, $item)">Edit</x-ui.button>
                                @endif

                                <details class="relative">
                                    <summary class="inline-flex cursor-pointer list-none items-center justify-center gap-2 rounded-sm bg-brand-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-brand-700">
                                        Adjust
                                    </summary>
                                    <form
                                        method="POST"
                                        action="{{ route($adjustRoute, $item) }}"
                                        class="mt-2 w-64 space-y-3 rounded-md border border-border bg-surface p-4 shadow-card"
                                    >
                                        @csrf
                                        <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-muted">Adjust {{ $item->item_name }}</p>

                                        <x-ui.select label="Movement" name="adjustment_type" :placeholder="null" :options="[
                                            'add' => 'Add stock',
                                            'subtract' => 'Deduct stock',
                                            'set' => 'Set exact count',
                                        ]" />

                                        <x-ui.input label="Amount" name="adjust_amount" type="number" step="0.01" min="0.01" required />

                                        <x-ui.input label="Remarks" name="remarks" placeholder="Optional note" />

                                        <x-ui.button variant="primary" size="sm" type="submit" class="w-full">Apply adjustment</x-ui.button>
                                    </form>
                                </details>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $showUnitCost ? 7 : 6 }}" class="px-4 py-2">
                            <x-ui.empty-state title="No inventory items" message="Add an item or adjust the filters to see results." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <x-ui.pagination :paginator="$items" />
</div>
