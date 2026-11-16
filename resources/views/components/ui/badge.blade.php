@props(['label', 'variant' => 'default', 'size' => 'sm', 'class' => null])

@php
    $variants = [
        'default' => 'bg-surface-muted text-ink border border-border',
        'brand'   => 'bg-brand-soft text-brand-ink border-brand-soft-border',
        'success' => 'bg-success-soft text-success-strong border-success-soft-border',
        'warning' => 'bg-warning-soft text-warning-strong border-warning-soft-border',
        'danger'  => 'bg-danger-soft text-danger-strong border-danger-soft-border',
        'info'    => 'bg-info-soft text-info-strong border-info-soft-border',
    ];
    $sizes = [
        'xs' => 'px-2 py-0.5 text-[10px]',
        'sm' => 'px-2.5 py-1 text-xs',
        'md' => 'px-3 py-1.5 text-sm',
    ];
    $variantClass = $variants[$variant] ?? $variants['default'];
    $sizeClass = $sizes[$size] ?? $sizes['sm'];
@endphp

<span
    {{ $attributes->merge([
        'class' => 'inline-flex items-center rounded-sm font-medium border ' . $sizeClass . ' ' . $variantClass . ' ' . ($class ?? ''),
    ]) }}
>
    {{ $slot ?? $label ?? '' }}
</span>
