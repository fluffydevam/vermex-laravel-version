@props(['paginator'])

@if ($paginator->total() > 0)
    <nav class="flex flex-col items-center justify-between gap-3 border-t border-border pt-4 text-xs sm:flex-row">
        <p class="text-ink-muted">
            Showing
            <span class="font-medium text-ink">{{ $paginator->firstItem() }}</span>–<span class="font-medium text-ink">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium text-ink">{{ number_format($paginator->total()) }}</span>
        </p>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="cursor-not-allowed rounded-sm border border-border px-3 py-1.5 text-ink-muted opacity-60">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-sm border border-border px-3 py-1.5 text-ink transition-colors hover:bg-surface-muted">Previous</a>
            @endif

            <span class="px-2 text-ink-muted">Page {{ $paginator->currentPage() }} of {{ max(1, $paginator->lastPage()) }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-sm border border-border px-3 py-1.5 text-ink transition-colors hover:bg-surface-muted">Next</a>
            @else
                <span class="cursor-not-allowed rounded-sm border border-border px-3 py-1.5 text-ink-muted opacity-60">Next</span>
            @endif
        </div>
    </nav>
@endif
