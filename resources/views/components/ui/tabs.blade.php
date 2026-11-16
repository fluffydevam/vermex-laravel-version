@props(['items' => [], 'active' => null])

<div class="flex gap-1">
    @foreach ($items as $item)
        @php
            $isActive = $active === $item;
            $classes = 'rounded-sm px-3 py-1.5 text-xs font-medium transition-colors duration-150 '
                . ($isActive ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-surface-muted hover:text-ink');
        @endphp
        <a {{ $attributes->merge(['href' => $item, 'class' => $classes]) }}>
            {{ $slot ?? ucfirst(str_replace(['-', '_'], ' ', $item)) }}
        </a>
    @endforeach
</div>
