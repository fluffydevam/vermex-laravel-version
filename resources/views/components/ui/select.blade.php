@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'options' => [],
    'value' => null,
    'placeholder' => 'Select…',
    'required' => false,
    'error' => null,
    'helper' => null,
    'icon' => null,
])

@php
    $id = $id ?? $name;
    $error = $error ?? $errors->first($name);
    $hasError = filled($error);

    $current = old($name, $value);
    if ($current instanceof \BackedEnum) {
        $current = $current->value;
    }

    $selectClasses = 'block w-full rounded-sm border border-border bg-surface text-ink shadow-sm '
        . 'focus-visible:border-brand-600 focus-visible:ring-2 focus-visible:ring-brand-600/20 focus-visible:outline-none '
        . 'disabled:cursor-not-allowed disabled:opacity-60 transition-colors duration-150 appearance-none pr-10';

    if ($hasError) {
        $selectClasses = str_replace('border-border', 'border-danger', $selectClasses);
        $selectClasses = str_replace('focus-visible:ring-brand-600/20', 'focus-visible:ring-danger/20', $selectClasses);
        $selectClasses = str_replace('focus-visible:border-brand-600', 'focus-visible:border-danger', $selectClasses);
    }
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-semibold text-ink/90">
            {{ $label }}
            @if ($required)<span class="ml-1 text-danger">*</span>@endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted">{!! $icon !!}</span>
        @endif

        <select
            {{ $attributes->merge([
                'id' => $id,
                'name' => $name,
                'required' => $required,
                'class' => $selectClasses . ($icon ? ' pl-9' : ''),
            ]) }}
        >
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @else
                @if ($placeholder)
                    <option value="" @disabled($required) @selected($current === null || $current === '')>{{ $placeholder }}</option>
                @endif

                @foreach ($options as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>
                        {{ $optionLabel }}
                    </option>
                @endforeach
            @endif
        </select>

        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>

    @if ($hasError)
        <p class="text-[11px] text-danger">{{ $error }}</p>
    @elseif ($helper)
        <p class="text-[11px] text-ink-muted">{{ $helper }}</p>
    @endif
</div>
