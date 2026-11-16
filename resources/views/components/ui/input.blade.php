@props(['label' => null, 'name', 'id' => null, 'type' => 'text', 'value' => null, 'placeholder' => null, 'required' => false, 'readonly' => false, 'autocomplete' => null, 'error' => null, 'helper' => null, 'icon' => null])

@php
    $id = $id ?? $name;
    $current = old($name, $value);
    if ($current instanceof \BackedEnum) {
        $current = $current->value;
    }
    $error = $error ?? $errors->first($name);
    $hasError = filled($error);
    $hasTrailing = isset($trailing);
    $inputClasses = 'block w-full rounded-sm border border-border bg-surface text-ink placeholder-ink-muted/60 shadow-sm '
        . 'focus-visible:border-brand-600 focus-visible:ring-2 focus-visible:ring-brand-600/20 focus-visible:outline-none '
        . 'disabled:cursor-not-allowed disabled:opacity-60 transition-colors duration-150';
    if ($hasError) {
        $inputClasses = str_replace('border-border', 'border-danger', $inputClasses);
        $inputClasses = str_replace('focus-visible:ring-brand-600/20', 'focus-visible:ring-danger/20', $inputClasses);
        $inputClasses = str_replace('focus-visible:border-brand-600', 'focus-visible:border-danger', $inputClasses);
    }
    $inputClasses .= ($icon ? ' pl-9' : '') . ($hasTrailing ? ' pr-16' : '');
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-semibold text-ink/90">
            {{ $label }}
            @if ($required) <span class="ml-1 text-danger">*</span> @endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none w-4 h-4">
                {!! $icon !!}
            </span>
        @endif

        <input
            {{ $attributes->merge([
                'id' => $id,
                'name' => $name,
                'type' => $type,
                'value' => $current,
                'placeholder' => $placeholder,
                'required' => $required,
                'readonly' => $readonly,
                'autocomplete' => $autocomplete,
                'class' => $inputClasses,
            ]) }}
        >

        @if ($hasTrailing)
            <div class="absolute inset-y-0 right-1.5 flex items-center">
                {{ $trailing }}
            </div>
        @endif
    </div>

    @if ($hasError)
        <p class="text-[11px] text-danger">{{ $error }}</p>
    @elseif ($helper)
        <p class="text-[11px] text-ink-muted">{{ $helper }}</p>
    @endif
</div>
