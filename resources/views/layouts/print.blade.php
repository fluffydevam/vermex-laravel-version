<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Vermex Report' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        @media print {
            body { background: white; color: #212529; }
            .no-print, header, aside, .no-print * { display: none !important; }
            main { overflow: visible !important; margin: 0; padding: 0; }
            .card { border: 1px solid #e5e7eb; box-shadow: none !important; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #e5e7eb; padding: 6px 8px; font-size: 11px; }
        }
    </style>
</head>
<body class="theme-light bg-white text-ink font-sans antialiased min-h-[100dvh]">
    <div class="max-w-4xl mx-auto p-6 sm:p-10">
        {{ $slot }}
    </div>
    <script>window.print();</script>
</body>
</html>
