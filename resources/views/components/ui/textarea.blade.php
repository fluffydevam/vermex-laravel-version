@props(['label', 'name', 'id' => null, 'value' => null, 'placeholder' => null, 'required' => false, 'error' => null, 'helper' => null, 'rows' => 4])

@php
    $id = $id ?? $name;
    $current = old($name, $value);
    $error = $error ?? $errors->first($name);
    $hasError = filled($error);
    $classes = 'block w-full rounded-sm border border-border bg-surface text-ink shadow-sm '
        . 'focus-visible:border-brand-600 focus-visible:ring-2 focus-visible:ring-brand-600/20 focus-visible:outline-none '
        . 'disabled:cursor-not-allowed disabled:opacity-60 transition-colors duration-150';
    if ($hasError) {
        $classes = str_replace('border-border', 'border-danger', $classes);
        $classes = str_replace('focus-visible:ring-brand-600/20', 'focus-visible:ring-danger/20', $classes);
        $classes = str_replace('focus-visible:border-brand-600', 'focus-visible:border-danger', $classes);
    }
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-semibold text-ink/90">
            {{ $label }}
            @if ($required) <span class="ml-1 text-danger">*</span> @endif
        </label>
    @endif

    <textarea
        {{ $attributes->merge([
            'id' => $id,
            'name' => $name,
            'placeholder' => $placeholder,
            'required' => $required,
            'rows' => $rows,
            'class' => $classes,
        ]) }}
    >{{ $current }}</textarea>

    @if ($hasError)
        <p class="text-[11px] text-danger">{{ $error }}</p>
    @elseif ($helper)
        <p class="text-[11px] text-ink-muted">{{ $helper }}</p>
    @endif
</div>
