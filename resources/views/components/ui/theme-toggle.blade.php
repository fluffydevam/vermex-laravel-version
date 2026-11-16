<button
    type="button"
    x-on:click="$store.theme.toggle()"
    aria-label="Toggle dark mode"
    x-bind:title="$store.theme.dark ? 'Switch to light mode' : 'Switch to dark mode'"
    x-bind:aria-pressed="$store.theme.dark"
    class="theme-toggle inline-flex h-8 w-8 items-center justify-center rounded-sm text-ink-muted transition-colors duration-150 hover:bg-surface-muted hover:text-ink focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:outline-none print:hidden"
>
    {{-- Moon: shown in light mode --}}
    <svg class="h-4 w-4 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
    </svg>

    {{-- Sun: shown in dark mode --}}
    <svg class="hidden h-4 w-4 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="12" r="4" stroke-width="2" />
        <path stroke-linecap="round" stroke-width="2" d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" />
    </svg>
</button>
