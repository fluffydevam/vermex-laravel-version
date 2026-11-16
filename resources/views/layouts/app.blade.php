<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vermex Pest Solutions') · Vermex</title>
    @include('partials.theme-script')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="{{ asset('js/vendor/alpine.min.js') }}"></script>
    <script src="{{ asset('js/vendor/lucide.min.js') }}"></script>
    @stack('styles')
</head>
<body
    x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
    class="min-h-[100dvh] bg-surface-muted font-sans text-ink antialiased selection:bg-brand-600 selection:text-white"
>
    <div class="flex min-h-[100dvh]">
        <x-nav.sidebar :user="auth()->user()" :current-route="request()->route()?->getName()" />

        <main class="min-h-[100dvh] w-full min-w-0 lg:flex-1">
            <header class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-border bg-surface/95 px-6 py-3 shadow-sm backdrop-blur-sm print:hidden">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="sidebarOpen = !sidebarOpen"
                        class="rounded-sm p-2 text-ink-muted transition-colors hover:bg-surface-muted hover:text-ink lg:hidden"
                        aria-label="Toggle menu"
                    >
                        <i data-lucide="menu" class="h-5 w-5"></i>
                    </button>
                    <span class="text-xs font-semibold text-ink">{{ $header ?? '' }}</span>
                </div>

                @auth
                    <div class="flex items-center gap-3">
                        <x-ui.theme-toggle />
                        <span class="hidden text-[11px] text-ink-muted sm:inline">{{ auth()->user()->full_name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-link transition-colors hover:text-link-hover">
                                Logout
                            </button>
                        </form>
                    </div>
                @endauth
            </header>

            <div class="mx-auto max-w-[1400px] space-y-8 p-6 sm:p-8 lg:p-10 print:max-w-none print:p-0">
                @if (session('success'))
                    <x-ui.alert type="success" title="Done">{{ session('success') }}</x-ui.alert>
                @endif
                @if (session('error'))
                    <x-ui.alert type="error" title="Error">{{ session('error') }}</x-ui.alert>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
    <script>
        lucide.createIcons();
        document.addEventListener('alpine:initialized', () => lucide.createIcons());
    </script>
</body>
</html>
