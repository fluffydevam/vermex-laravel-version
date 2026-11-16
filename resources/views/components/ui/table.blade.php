@props(['columns' => [], 'rows' => [], 'rowKey' => 'id', 'emptyMessage' => 'No records found.', 'emptyIcon' => null, 'striped' => true, 'hover' => true])

<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-border">
                @foreach ($columns as $column)
                    <th {{ $attributes->merge(['class' => 'px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted bg-surface-muted']) }}>
                        {{ $column }}
                    </th>
                @endforeach
                @if (isset($actions))
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted bg-surface-muted w-24"></th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @if (empty($rows))
                <tr>
                    <td colspan="{{ count($columns) + (isset($actions) ? 1 : 0) }}" class="px-4 py-8 text-center">
                        <x-ui.empty-state :icon="$emptyIcon" :title="$emptyMessage" />
                    </td>
                </tr>
            @else
                @foreach ($rows as $row)
                    <tr class="{{ $striped ? 'bg-surface-muted' : 'bg-surface' }} {{ $hover ? 'hover:bg-brand-50/30' : '' }} transition-colors duration-150">
                        @foreach ($columns as $column)
                            <td class="px-4 py-3 text-xs text-ink/90">
                                {{ $row[$column] ?? '' }}
                            </td>
                        @endforeach
                        @if (isset($actions))
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    {{ $actions($row) }}
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
