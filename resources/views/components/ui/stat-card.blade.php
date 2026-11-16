@props(['label', 'value', 'icon' => null, 'trend' => null, 'variant' => 'default'])

@php
    $variants = [
        'default' => ['card' => 'bg-surface border-border', 'label' => 'text-ink-muted', 'value' => 'text-ink', 'icon' => 'bg-surface-muted text-ink-muted'],
        'brand' => ['card' => 'bg-brand-soft border-brand-soft-border', 'label' => 'text-brand-ink/70', 'value' => 'text-brand-ink', 'icon' => 'bg-surface/60 text-brand-ink'],
        'success' => ['card' => 'bg-success-soft border-success-soft-border', 'label' => 'text-success-strong/70', 'value' => 'text-success-strong', 'icon' => 'bg-surface/60 text-success-strong'],
        'warning' => ['card' => 'bg-warning-soft border-warning-soft-border', 'label' => 'text-warning-strong/70', 'value' => 'text-warning-strong', 'icon' => 'bg-surface/60 text-warning-strong'],
        'danger' => ['card' => 'bg-danger-soft border-danger-soft-border', 'label' => 'text-danger-strong/70', 'value' => 'text-danger-strong', 'icon' => 'bg-surface/60 text-danger-strong'],
        'info' => ['card' => 'bg-info-soft border-info-soft-border', 'label' => 'text-info-strong/70', 'value' => 'text-info-strong', 'icon' => 'bg-surface/60 text-info-strong'],
    ];
    $style = $variants[$variant] ?? $variants['default'];
@endphp

<div class="rounded-md border p-5 shadow-card transition-shadow duration-150 hover:shadow-card-hover {{ $style['card'] }}">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-[11px] font-semibold uppercase tracking-wider {{ $style['label'] }}">{{ $label }}</p>
            <p class="mt-1 font-mono text-lg font-bold {{ $style['value'] }}">{{ $value }}</p>

            @if ($trend)
                <span class="mt-1.5 inline-flex items-center gap-1 text-[10px] font-medium {{ $trend['up'] ? 'text-success-strong' : 'text-danger' }}">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if ($trend['up'])
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l5-5 5 5"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l5 5 5-5"></path>
                        @endif
                    </svg>
                    {{ $trend['label'] }}
                </span>
            @endif
        </div>

        @if ($icon)
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-sm {{ $style['icon'] }}">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
