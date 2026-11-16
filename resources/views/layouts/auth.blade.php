<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Log in') · Vermex</title>
    @include('partials.theme-script')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="{{ asset('js/vendor/alpine.min.js') }}"></script>
    <script src="{{ asset('js/vendor/lucide.min.js') }}"></script>
</head>
<body class="flex min-h-[100dvh] items-center justify-center bg-surface-muted p-4 font-sans text-ink antialiased">
    <div class="fixed right-4 top-4 z-50">
        <x-ui.theme-toggle />
    </div>

    <div class="w-full max-w-md">
        @if (session('error'))
            <div class="mb-4">
                <x-ui.alert type="error" title="Login failed">{{ session('error') }}</x-ui.alert>
            </div>
        @endif

        <div class="rounded-md border border-border bg-surface p-8 shadow-card">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-sm bg-brand-600 text-white shadow-sm">
                    <i data-lucide="shield-check" class="h-5 w-5"></i>
                </div>
                <h1 class="text-lg font-bold tracking-tight text-ink">VERMEX</h1>
                <p class="mt-1 text-[11px] text-ink-muted">Pest Solutions — Transaction Processing &amp; Inventory</p>
            </div>

            @yield('content')
        </div>

        <p class="mt-6 text-center text-[11px] text-ink-muted">
            Vermex Pest Solutions © {{ date('Y') }} · Authorized access only
        </p>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
