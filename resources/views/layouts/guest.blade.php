<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', config('app.name', 'NasiCafe POS'))</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-surface-light text-ink-primary">
        <div class="min-h-screen flex flex-col items-center justify-center p-6">
            <div class="w-full max-w-md bg-surface-card p-8 rounded-2xl shadow-sm border border-surface-border">
                <div class="text-center mb-6">
                    <a href="/" class="text-2xl font-bold text-brand-600">Nasi Cafe</a>
                    <p class="text-xs text-ink-muted mt-1">Sistem Point of Sales & Self-Order</p>
                </div>

                <x-alert />

                @yield('content')
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
