@props(['user' => null, 'currentRoute' => null])

@php
    $role = $user?->role?->value;
    $navItems = $role ? config('navigation.' . $role, []) : [];
    $firstName = $user->first_name ?? null;
    $initial = filled($firstName) ? strtoupper(mb_substr($firstName, 0, 1)) : 'U';
    $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->username ?? 'Guest');
@endphp

<aside
    class="fixed left-0 top-0 z-40 flex h-[100dvh] w-64 min-w-[16rem] max-w-[16rem] flex-col overflow-y-auto bg-sidebar text-white select-none transition-transform duration-200 print:hidden lg:sticky lg:top-0 lg:translate-x-0 lg:transition-none"
    :class="{ '-translate-x-full': !sidebarOpen }"
>
    <div class="flex flex-col">
        <!-- Brand header -->
        <div class="mb-2 flex items-center gap-3 border-b border-sidebar-line px-4 py-4">
            <div class="flex-shrink-0 rounded-sm bg-brand-600 p-2 text-white shadow-sm">
                <i data-lucide="shield-check" class="h-5 w-5"></i>
            </div>
            <div class="min-w-0 overflow-hidden">
                <h1 class="text-sm font-bold leading-none uppercase tracking-wider text-white">VERMEX</h1>
                <p class="mt-1 text-[10px] font-medium tracking-tight text-sidebar-brand">Pest Solutions</p>
            </div>
            <button
                type="button"
                @click="sidebarOpen = false"
                class="ml-auto p-1.5 text-sidebar-text hover:text-white lg:hidden"
                aria-label="Close menu"
            >
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>

        <!-- Navigation -->
        <div class="mb-2 px-4 text-[11px] font-semibold uppercase tracking-wider text-sidebar-brand">Workspace</div>
        <nav class="space-y-1 px-1">
            @foreach ($navItems as $item)
                @php
                    $isActive = $currentRoute === $item['route']
                        || str_starts_with((string) $currentRoute, $item['route'] . '.');
                    $routeExists = \Illuminate\Support\Facades\Route::has($item['route']);
                    $link = $routeExists ? route($item['route']) : '#';
                    $base = 'flex items-center gap-3 px-3 py-2.5 text-xs font-medium rounded-lg transition-colors duration-150 w-full';
                    $active = $isActive
                        ? 'bg-sidebar-active text-white shadow-sm border-l-4 border-brand-500 font-semibold'
                        : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white';
                    $iconColor = $isActive ? 'text-sidebar-accent' : 'text-sidebar-text';
                @endphp
                <a
                    href="{{ $link }}"
                    @click="if (window.innerWidth < 1024) sidebarOpen = false"
                    @if (! $routeExists) aria-disabled="true" tabindex="-1" @endif
                    class="{{ $base }} {{ $active }} {{ $routeExists ? '' : 'pointer-events-none opacity-50' }}"
                >
                    <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4 flex-shrink-0 {{ $iconColor }}"></i>
                    <span class="truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <!-- Bottom profile footer -->
    <div class="mt-auto space-y-3 border-t border-sidebar-line px-4 pb-4 pt-4">
        <div class="flex min-w-0 items-center gap-2.5">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-brand-600 text-[11px] font-bold text-white">
                {{ $initial }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-xs font-semibold leading-tight text-white">{{ $fullName }}</p>
                <p class="truncate text-[10px] text-sidebar-text">{{ $role ?? 'Guest' }}</p>
            </div>
        </div>
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center gap-2 rounded-lg px-1 py-1.5 text-xs font-medium text-sidebar-text transition hover:bg-sidebar-hover hover:text-white"
                    title="Logout"
                >
                    <i data-lucide="log-out" class="h-4 w-4"></i>
                    <span>Logout</span>
                </button>
            </form>
        @endauth
    </div>
</aside>

<!-- Mobile backdrop -->
<div
    x-show="sidebarOpen"
    x-transition.opacity
    @click="sidebarOpen = false"
    class="fixed inset-0 z-30 bg-black/50 lg:hidden print:hidden"
></div>
