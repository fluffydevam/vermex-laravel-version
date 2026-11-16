@props(['padded' => true, 'shadow' => true, 'bordered' => true])

<div
    {{ $attributes->merge([
        'class' => 'rounded-md border border-border bg-surface transition-shadow duration-150 hover:shadow-card'
            . ($shadow ? ' shadow-card' : '')
            . ($bordered ? '' : ' border-transparent')
            . ($padded ? ' p-6' : ''),
    ]) }}
>
    {{ $slot }}
</div>
