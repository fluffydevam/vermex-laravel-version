@props(['label', 'name', 'id' => null, 'checked' => old($name ?? null, false), 'error' => null, 'helper' => null])

@php
    $id = $id ?? $name;
    $hasError = filled($error);
    $checkedAttr = $checked ? 'checked' : null;
@endphp

<div class="flex items-start gap-3">
    <input
        {{ $attributes->merge([
            'id' => $id,
            'name' => $name,
            'type' => 'checkbox',
            'checked' => $checkedAttr,
            'class' => 'w-4 h-4 rounded-sm border border-border bg-surface text-brand-600 shadow-sm focus-visible:ring-2 focus-visible:ring-brand-600/20 focus-visible:border-brand-600 transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-60',
        ]) }}
    >

    <div class="space-y-0.5 leading-none">
        @if ($label)
            <label for="{{ $id }}" class="text-xs font-semibold text-ink/90">{{ $label }}</label>
        @endif
        @if ($hasError)
            <p class="text-[11px] text-danger">{{ $error }}</p>
        @elseif ($helper)
            <p class="text-[11px] text-ink-muted">{{ $helper }}</p>
        @endif
    </div>
</div>
