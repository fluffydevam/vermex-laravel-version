@props(['icon' => null, 'title' => 'No data found', 'message' => null, 'action' => null, 'actionLabel' => null])

<div class="text-center py-12">
    @if ($icon)
        <div class="mx-auto w-12 h-12 rounded-lg bg-surface-muted flex items-center justify-center text-ink/20 mb-4">
            {!! $icon !!}
        </div>
    @endif
    <h3 class="text-sm font-semibold text-ink">{{ $title }}</h3>
    @if ($message)
        <p class="text-xs text-ink-muted mt-1 max-w-xs mx-auto">{{ $message }}</p>
    @endif
    @if ($action && $actionLabel)
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
