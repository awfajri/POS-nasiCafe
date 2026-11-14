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
        <div class="min-h-screen flex flex-col lg:flex-row">
            <!-- Sidebar / Navigasi -->
            <aside class="w-full lg:w-64 bg-surface-card border-b lg:border-b-0 lg:border-r border-surface-border shrink-0">
                <div class="p-5 flex items-center justify-between">
                    <a href="/" class="text-xl font-bold text-brand-600">Nasi Cafe</a>
                    @if (auth()->check())
                        <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-600 capitalize">
                            {{ auth()->user()->role ?? 'User' }}
                        </span>
                    @endif
                </div>

                <nav class="px-3 pb-4 space-y-1" aria-label="Navigasi Utama">
                    <a href="/" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-brand-600 bg-brand-50">
                        Dashboard
                    </a>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Top Header -->
                <header class="bg-surface-card border-b border-surface-border px-6 py-4 flex items-center justify-between">
                    <div>
                        <h1 class="text-lg font-semibold">@yield('header', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                            <p class="text-xs text-ink-muted">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="p-6 flex-1">
                    <x-alert />
                    @yield('content')
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
