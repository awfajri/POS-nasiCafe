@php
    $currentUser = auth()->user();
    $role = $currentUser->role ?? 'customer';

    $getRouteUrl = function (string $name, string $default = '#'): string {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name) : $default;
    };

    $roleLabel = match ($role) {
        'owner' => 'Owner',
        'cashier' => 'Kasir',
        'customer' => 'Pelanggan',
        default => ucfirst($role),
    };

    // Navigasi Owner
    $ownerNavItems = [
        [
            'label' => 'Dashboard',
            'route' => 'owner.dashboard',
            'active' => request()->routeIs('owner.dashboard', 'owner.dashboard.*'),
            'icon' => 'grid',
        ],
        [
            'label' => 'Kelola Pengguna',
            'route' => 'owner.users.index',
            'active' => request()->routeIs('owner.users.*'),
            'icon' => 'user',
        ],
        [
            'label' => 'Kategori',
            'route' => 'owner.categories.index',
            'active' => request()->routeIs('owner.categories.*'),
            'icon' => 'book',
        ],
        [
            'label' => 'Menu & Stok',
            'route' => 'owner.menus.index',
            'active' => request()->routeIs('owner.menus.*'),
            'icon' => 'cart',
        ],
        [
            'label' => 'Riwayat Transaksi',
            'route' => 'transactions.index',
            'active' => request()->routeIs('transactions.*'),
            'icon' => 'receipt',
        ],
        [
            'label' => 'Laporan Keuangan',
            'route' => 'owner.reports.index',
            'active' => request()->routeIs('owner.reports.*'),
            'icon' => 'card',
        ],
    ];

    // Navigasi Kasir
    $cashierNavItems = [
        [
            'label' => 'Dashboard',
            'route' => 'cashier.dashboard',
            'active' => request()->routeIs('cashier.dashboard', 'cashier.dashboard.*'),
            'icon' => 'grid',
        ],
        [
            'label' => 'Kasir POS',
            'route' => 'pos.index',
            'active' => request()->routeIs('pos.*'),
            'icon' => 'bag',
        ],
        [
            'label' => 'Riwayat Transaksi',
            'route' => 'transactions.index',
            'active' => request()->routeIs('transactions.*'),
            'icon' => 'receipt',
        ],
    ];

    // Navigasi Pelanggan
    $customerNavItems = [
        [
            'label' => 'Pesan Menu',
            'route' => 'customer.order',
            'active' => request()->routeIs('customer.order', 'customer.order.*'),
            'icon' => 'cart',
        ],
        [
            'label' => 'Pesanan Saya',
            'route' => 'customer.dashboard',
            'active' => request()->routeIs('customer.dashboard', 'customer.dashboard.*'),
            'icon' => 'receipt',
        ],
    ];

    $navItems = match ($role) {
        'owner' => $ownerNavItems,
        'cashier' => $cashierNavItems,
        default => $customerNavItems,
    };

    $pageTitle = trim($__env->yieldContent('header') ?: $__env->yieldContent('title', 'NasiCafe POS'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name', 'NasiCafe POS'))</title>

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-surface-light text-ink-primary min-h-screen flex flex-col" x-data="{ sidebarOpen: false, profileDropdown: false }">
        <!-- Top Navigation Bar (Card Style) -->
        <header class="w-full px-4 pt-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto rounded-2xl bg-white border border-surface-border px-4 py-3 sm:px-6 shadow-xs">
                <div class="flex items-center justify-between gap-4">
                    <!-- Brand Logo -->
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ $currentUser && method_exists($currentUser, 'homeRoute') ? $getRouteUrl($currentUser->homeRoute(), '/') : '/' }}" class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-[#1b254b] flex items-center justify-center text-white font-bold text-sm shadow-xs">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </span>
                            <span class="text-xl font-bold tracking-tight text-[#1b254b]">NasiCafe</span>
                        </a>
                    </div>

                    <!-- Desktop Dynamic Role Navigation Items -->
                    <nav class="hidden lg:flex items-center gap-2 flex-wrap" aria-label="Navigasi Utama">
                        @foreach ($navItems as $item)
                            <a
                                href="{{ $getRouteUrl($item['route']) }}"
                                @class([
                                    'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition shrink-0',
                                    'bg-[#2b2d6e] text-white shadow-xs' => $item['active'],
                                    'border border-surface-border bg-white text-slate-500 hover:text-slate-800 hover:border-slate-300 hover:bg-slate-50' => ! $item['active'],
                                ])
                            >
                                @if ($item['icon'] === 'grid')
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>
                                    </svg>
                                @elseif ($item['icon'] === 'user')
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                @elseif ($item['icon'] === 'book')
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                @elseif ($item['icon'] === 'cart')
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                @elseif ($item['icon'] === 'bag')
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                @elseif ($item['icon'] === 'receipt')
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                @elseif ($item['icon'] === 'card')
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                @endif
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>

                    <!-- Right Section: Notification & Profile -->
                    <div class="flex items-center gap-3">
                        <!-- Notification Bell Button -->
                        <button
                            type="button"
                            class="p-2.5 rounded-xl border border-surface-border text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition focus:outline-none"
                            aria-label="Notifikasi"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </button>

                        @if ($currentUser)
                            <!-- Profile Trigger with Dropdown -->
                            <div class="relative" @click.outside="profileDropdown = false">
                                <button
                                    @click="profileDropdown = !profileDropdown"
                                    type="button"
                                    class="flex items-center gap-3 pl-2 pr-3 py-1.5 rounded-xl hover:bg-slate-50 transition focus:outline-none"
                                    :aria-expanded="profileDropdown.toString()"
                                    aria-haspopup="true"
                                >
                                    <span class="w-9 h-9 rounded-full bg-[#1b254b] text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                        {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
                                    </span>
                                    <div class="text-left hidden sm:block">
                                        <p class="text-xs font-medium text-slate-400 capitalize leading-tight">{{ $roleLabel }}</p>
                                        <p class="text-sm font-semibold text-slate-800 leading-tight truncate max-w-[140px]">{{ $currentUser->name ?? 'Pengguna' }}</p>
                                    </div>
                                    <svg
                                        class="w-4 h-4 text-slate-500 transition-transform duration-200"
                                        :class="profileDropdown ? 'rotate-180' : ''"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <!-- Profile Dropdown Card (Matching Image 3) -->
                                <div
                                    x-show="profileDropdown"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute right-0 mt-2 w-72 bg-white rounded-2xl border border-surface-border p-4 shadow-xl z-50"
                                    style="display: none;"
                                >
                                    <div class="flex items-center justify-between pb-3">
                                        <div class="flex items-center gap-3">
                                            <span class="w-10 h-10 rounded-full bg-[#1b254b] text-white flex items-center justify-center font-bold text-sm uppercase shrink-0">
                                                {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
                                            </span>
                                            <div>
                                                <p class="text-xs font-medium text-slate-400 capitalize leading-tight">{{ $roleLabel }}</p>
                                                <p class="text-sm font-bold text-slate-800 leading-tight truncate max-w-[150px]">{{ $currentUser->name ?? 'Pengguna' }}</p>
                                            </div>
                                        </div>
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    </div>

                                    <div class="border-t border-slate-100 my-2"></div>

                                    <div class="py-1">
                                        <a
                                            href="{{ $getRouteUrl('password.edit') }}"
                                            @class([
                                                'block px-3 py-2 text-sm font-medium rounded-xl transition',
                                                'bg-slate-100 text-slate-900 font-semibold' => request()->routeIs('password.edit', 'password.edit.*'),
                                                'text-slate-600 hover:text-slate-900 hover:bg-slate-50' => ! request()->routeIs('password.edit', 'password.edit.*'),
                                            ])
                                        >
                                            Ubah Kata Sandi
                                        </a>
                                    </div>

                                    <!-- Logout Form POST (Matching Image 3) -->
                                    <form method="POST" action="{{ $getRouteUrl('logout', '/logout') }}" class="mt-3">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="w-full flex items-center justify-center gap-2 bg-[#c5221f] hover:bg-[#a81c19] text-white font-bold py-3 px-4 rounded-xl shadow-xs transition"
                                        >
                                            <span>Logout</span>
                                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ $getRouteUrl('login', '/login') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-[#2b2d6e] text-white hover:bg-[#202253] transition">
                                Masuk
                            </a>
                        @endif

                        <!-- Mobile Hamburger Button -->
                        <button
                            @click="sidebarOpen = !sidebarOpen"
                            type="button"
                            class="lg:hidden p-2 rounded-xl border border-surface-border text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition focus:outline-none"
                            :aria-expanded="sidebarOpen.toString()"
                            aria-controls="mobile-navigation"
                            aria-label="Toggle navigasi mobile"
                        >
                            <svg x-show="!sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            <svg x-show="sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Collapsible Navigation (Sidebar Collapse di Mobile) -->
                <nav
                    id="mobile-navigation"
                    x-show="sidebarOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="lg:hidden mt-4 pt-4 border-t border-surface-border space-y-2"
                    style="display: none;"
                    aria-label="Navigasi Mobile"
                >
                    <div class="px-2 py-1 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Menu {{ $roleLabel }}
                    </div>

                    @foreach ($navItems as $item)
                        <a
                            href="{{ $getRouteUrl($item['route']) }}"
                            @class([
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition',
                                'bg-[#2b2d6e] text-white shadow-xs' => $item['active'],
                                'border border-surface-border bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-50' => ! $item['active'],
                            ])
                        >
                            @if ($item['icon'] === 'grid')
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>
                                </svg>
                            @elseif ($item['icon'] === 'user')
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            @elseif ($item['icon'] === 'book')
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            @elseif ($item['icon'] === 'cart')
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            @elseif ($item['icon'] === 'bag')
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            @elseif ($item['icon'] === 'receipt')
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            @elseif ($item['icon'] === 'card')
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            @endif
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach

                    @if ($currentUser)
                        <div class="border-t border-surface-border pt-3 mt-3">
                            <a
                                href="{{ $getRouteUrl('password.edit') }}"
                                @class([
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition',
                                    'bg-slate-100 text-slate-900 font-semibold' => request()->routeIs('password.edit', 'password.edit.*'),
                                    'text-slate-600 hover:text-slate-900 hover:bg-slate-50' => ! request()->routeIs('password.edit', 'password.edit.*'),
                                ])
                            >
                                <svg class="w-5 h-5 shrink-0 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                                <span>Ubah Kata Sandi</span>
                            </a>

                            <form method="POST" action="{{ $getRouteUrl('logout', '/logout') }}" class="mt-2">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full flex items-center justify-center gap-2 bg-[#c5221f] hover:bg-[#a81c19] text-white font-bold py-3 px-4 rounded-xl shadow-xs transition"
                                >
                                    <span>Logout</span>
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </nav>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-24 lg:pb-8">
            <!-- Banner Peringatan Stok Menipis (Hanya untuk Owner & Kasir) -->
            <x-low-stock-banner />

            <!-- Banner Notifikasi Flash / Validasi -->
            <x-alert />

            <!-- Page Slot / Content -->
            @yield('content')
            {{ $slot ?? '' }}
        </main>

        @if ($currentUser && ($currentUser->isCustomer() || $role === 'customer'))
            <!-- Customer Bottom Navigation Bar on Mobile (Matching Image 4) -->
            <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-surface-border rounded-t-3xl shadow-lg px-6 pt-3 pb-2 flex flex-col" aria-label="Navigasi Pelanggan Bawah">
                <div class="flex items-center justify-around w-full">
                    <!-- Pesan Menu -->
                    <a
                        href="{{ $getRouteUrl('customer.order') }}"
                        @class([
                            'flex flex-col items-center gap-1 text-xs font-semibold transition',
                            'text-[#242b5a]' => request()->routeIs('customer.order', 'customer.order.*'),
                            'text-slate-400 hover:text-slate-600' => ! request()->routeIs('customer.order', 'customer.order.*'),
                        ])
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Pesan Menu</span>
                    </a>

                    <!-- Pesanan Saya -->
                    <a
                        href="{{ $getRouteUrl('customer.dashboard') }}"
                        @class([
                            'flex flex-col items-center gap-1 text-xs font-semibold transition',
                            'text-[#242b5a]' => request()->routeIs('customer.dashboard', 'customer.dashboard.*'),
                            'text-slate-400 hover:text-slate-600' => ! request()->routeIs('customer.dashboard', 'customer.dashboard.*'),
                        ])
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Pesanan Saya</span>
                    </a>

                    <!-- Profil / Ubah Kata Sandi -->
                    <a
                        href="{{ $getRouteUrl('password.edit') }}"
                        @class([
                            'flex flex-col items-center gap-1 text-xs font-semibold transition',
                            'text-[#242b5a]' => request()->routeIs('password.edit', 'password.edit.*'),
                            'text-slate-400 hover:text-slate-600' => ! request()->routeIs('password.edit', 'password.edit.*'),
                        ])
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Profil</span>
                    </a>
                </div>

                <!-- Bottom Home Indicator Bar (Matching Image 4) -->
                <div class="w-32 h-1 bg-slate-300 rounded-full mx-auto mt-2"></div>
            </nav>
        @endif

        @stack('scripts')
    </body>
</html>
