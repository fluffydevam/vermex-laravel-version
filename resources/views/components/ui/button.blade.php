@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'disabled' => false,
    'loading' => false,
])

@php
    $variants = [
        'primary' => 'bg-brand-600 text-white hover:bg-brand-700 focus-visible:ring-brand-600',
        'ghost' => 'bg-transparent border border-border text-ink hover:bg-surface-muted focus-visible:ring-brand-600',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:ring-danger',
        'success' => 'bg-green-700 text-white hover:bg-green-800 focus-visible:ring-success',
        'outline' => 'bg-transparent border border-border text-ink hover:bg-surface focus-visible:ring-brand-600',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $baseClass = 'inline-flex items-center justify-center gap-2 font-medium rounded-sm transition-colors duration-150 '
        . 'outline-none focus-visible:ring-2 focus-visible:ring-offset-2 '
        . $variantClass . ' ' . $sizeClass
        . ($disabled ? ' opacity-50 pointer-events-none' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClass]) }}>
        @if ($loading)
            <svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button
        {{ $attributes->merge([
            'type' => $type,
            'disabled' => $disabled,
            'class' => $baseClass,
        ]) }}
    >
        @if ($loading)
            <svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif
