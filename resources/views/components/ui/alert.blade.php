@props(['type' => 'success', 'title' => null, 'message' => null, 'dismissible' => true])

@php
    $types = [
        'success' => 'bg-success-soft border-success-soft-border text-success-ink',
        'error'   => 'bg-danger-soft border-danger-soft-border text-danger-ink',
        'warning' => 'bg-warning-soft border-warning-soft-border text-warning-ink',
        'info'    => 'bg-info-soft border-info-soft-border text-info-ink',
    ];
    $typeClass = $types[$type] ?? $types['success'];
    $icons = [
        'success' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        'error'   => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
        'warning' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'info'    => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border p-4 flex items-start gap-3 ' . $typeClass]) }}>
    <div class="flex-shrink-0 mt-0.5">{!! $icons[$type] ?? $icons['success'] !!}</div>
    <div class="text-sm leading-relaxed">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        @if ($message)
            <p class="{{ $title ? 'mt-1' : '' }}">{{ $message }}</p>
        @endif
        {{ $slot }}
    </div>
    @if ($dismissible)
        <button
            class="ml-auto flex-shrink-0 text-ink-muted hover:text-ink transition-colors"
            onclick="this.closest('div').remove()"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    @endif
</div>
