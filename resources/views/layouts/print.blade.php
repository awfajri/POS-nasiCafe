<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Cetak Struk - NasiCafe')</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @media print {
                .no-print {
                    display: none !important;
                }
                body {
                    background: #ffffff !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }
                .receipt-sheet {
                    box-shadow: none !important;
                    border: none !important;
                    max-width: 80mm !important;
                    width: 100% !important;
                    margin: 0 auto !important;
                    padding: 4mm !important;
                }
            }
        </style>
        @stack('styles')
    </head>
    <body class="bg-slate-100 text-slate-900 min-h-screen py-6 px-4 antialiased">
        <main class="max-w-md mx-auto">
            @yield('content')
        </main>
        @stack('scripts')
    </body>
</html>