@props(['id' => 'modal', 'title' => null, 'open' => false, 'size' => 'md', 'closeLabel' => 'Close'])

@php
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        'full' => 'max-w-4xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $display = $open ? 'block' : 'hidden';
@endphp

<!-- Overlay -->
<div
    {{ $attributes->merge([
        'id' => $id . '-overlay',
        'class' => 'fixed inset-0 z-50 bg-black/40 backdrop-blur-sm transition-opacity duration-200 ' . $display,
    ]) }}
>
    <div class="absolute inset-full flex items-center justify-center p-4" onclick="{{ $attributes->whereStartsWith('wire:') ? '' : 'closeModal(\'' . $id . '\')' }}">
        <!-- Backdrop click handled by parent via x-data or inline -->
    </div>
</div>

<!-- Panel -->
<div
    {{ $attributes->merge([
        'id' => $id,
        'class' => 'fixed left-[50%] top-[50%] z-50 w-full ' . $sizeClass . ' rounded-lg border border-border bg-surface shadow-card transition-all duration-200 -translate-x-1/2 -translate-y-1/2 scale-95 opacity-0 ' . ($open ? 'scale-100 opacity-100' : ''),
    ]) }}
>
    <div class="flex items-center justify-between px-6 py-4 border-b border-border">
        <h3 class="text-sm font-semibold text-ink">{{ $title }}</h3>
        <button
            type="button"
            class="rounded-sm p-2 text-ink-muted hover:bg-surface-muted hover:text-ink transition-colors"
            onclick="closeModal('{{ $id }}')"
            aria-label="{{ $closeLabel }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    <div class="p-6">
        {{ $slot }}
    </div>
</div>

<script>
    function closeModal(id) {
        const el = document.getElementById(id);
        const ov = document.getElementById(id + '-overlay');
        if (el) { el.classList.remove('scale-100', 'opacity-100'); el.classList.add('scale-95', 'opacity-0'); setTimeout(() => el.classList.add('hidden'), 200); }
        if (ov) { ov.classList.add('hidden'); }
    }
    function openModal(id) {
        const el = document.getElementById(id);
        const ov = document.getElementById(id + '-overlay');
        if (el) { el.classList.remove('hidden', 'scale-95', 'opacity-0'); el.classList.add('scale-100', 'opacity-100'); }
        if (ov) { ov.classList.remove('hidden'); }
    }
</script>
