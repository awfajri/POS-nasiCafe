@php
    $currentUser = auth()->user();
    $isGuest = auth()->guest();
    $role = $currentUser->role ?? null;

    $getRouteUrl = function (string $name, string $default = '#'): string {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name) : $default;
    };

    $roleLabel = match ($role) {
        'owner' => 'Owner',
        'cashier' => 'Kasir',
        'customer' => 'Pelanggan',
        default => 'Pengguna',
    };

    // Navigasi Owner (6 menu horizontal)
    $ownerNavItems = [
        [
            'label' => 'Dashboard',
            'route' => 'owner.dashboard',
            'active' => request()->routeIs('owner.dashboard', 'owner.dashboard.*'),
            'icon' => 'layout-grid',
        ],
        [
            'label' => 'Kelola Pengguna',
            'route' => 'owner.users.index',
            'active' => request()->routeIs('owner.users.*'),
            'icon' => 'users',
        ],
        [
            'label' => 'Kategori',
            'route' => 'owner.categories.index',
            'active' => request()->routeIs('owner.categories.*'),
            'icon' => 'book-open',
        ],
        [
            'label' => 'Menu & Stok',
            'route' => 'owner.menus.index',
            'active' => request()->routeIs('owner.menus.*'),
            'icon' => 'shopping-cart',
        ],
        [
            'label' => 'Riwayat Transaksi',
            'route' => 'transactions.index',
            'active' => request()->routeIs('transactions.*'),
            'icon' => 'file-text',
        ],
        [
            'label' => 'Laporan Keuangan',
            'route' => 'owner.reports.index',
            'active' => request()->routeIs('owner.reports.*'),
            'icon' => 'credit-card',
        ],
    ];

    // Navigasi Kasir (3 menu di tengah)
    $cashierNavItems = [
        [
            'label' => 'Dashboard',
            'route' => 'cashier.dashboard',
            'active' => request()->routeIs('cashier.dashboard', 'cashier.dashboard.*'),
            'icon' => 'layout-grid',
        ],
        [
            'label' => 'Kasir POS',
            'route' => 'pos.index',
            'active' => request()->routeIs('pos.*'),
            'icon' => 'shopping-bag',
        ],
        [
            'label' => 'Riwayat Transaksi',
            'route' => 'transactions.index',
            'active' => request()->routeIs('transactions.*'),
            'icon' => 'file-text',
        ],
    ];

    // Navigasi atas hanya untuk Owner dan Kasir (Customer menggunakan bottom nav)
    $navItems = match ($role) {
        'owner' => $ownerNavItems,
        'cashier' => $cashierNavItems,
        default => [],
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name', 'NasiCafe POS'))</title>

        <!-- Google Fonts: Outfit -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-surface-light text-ink-primary min-h-screen flex flex-col {{ $role === 'customer' ? 'pb-24 sm:pb-28' : '' }}" x-data="{ sidebarOpen: false, profileDropdown: false }">
        <!-- Top Navigation Bar Card -->
        <header class="w-full px-4 pt-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto rounded-2xl bg-white border border-surface-border px-4 py-3 sm:px-6 shadow-xs">
                @if ($role === 'owner')
                    <!-- ================= OWNER: 2 BARIS ================= -->
                    <!-- Baris 1: Logo di kiri & Profil di kanan -->
                    <div class="flex items-center justify-between gap-4">
                        <a href="{{ $currentUser && method_exists($currentUser, 'homeRoute') ? $getRouteUrl($currentUser->homeRoute(), '/') : '/' }}" class="flex items-center gap-2.5 shrink-0">
                            <span class="w-9 h-9 rounded-xl bg-[#1b254b] flex items-center justify-center text-white font-bold text-sm shadow-xs">
                                <x-lucide-utensils class="w-5 h-5 text-white" />
                            </span>
                            <span class="text-xl font-bold tracking-tight text-[#1b254b]">NasiCafe</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <!-- Profile Dropdown -->
                            <div class="relative" @click.outside="profileDropdown = false">
                                <button
                                    @click="profileDropdown = !profileDropdown"
                                    type="button"
                                    class="flex items-center gap-3 pl-2 pr-3 py-1.5 rounded-xl hover:bg-slate-50 transition focus:outline-none"
                                    :aria-expanded="profileDropdown.toString()"
                                    aria-haspopup="true"
                                >
                                    <span class="w-9 h-9 rounded-full bg-[#1b254b] text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                        {{ strtoupper(substr($currentUser->name ?? 'O', 0, 1)) }}
                                    </span>
                                    <div class="text-left hidden sm:block">
                                        <p class="text-xs font-medium text-slate-400 capitalize leading-tight">Owner</p>
                                        <p class="text-sm font-semibold text-slate-800 leading-tight truncate max-w-[140px]">{{ $currentUser->name ?? 'Owner' }}</p>
                                    </div>
                                    <x-lucide-chevron-down class="w-4 h-4 text-slate-500 transition-transform duration-200" ::class="profileDropdown ? 'rotate-180' : ''" />
                                </button>

                                <!-- Dropdown Card -->
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
                                                {{ strtoupper(substr($currentUser->name ?? 'O', 0, 1)) }}
                                            </span>
                                            <div>
                                                <p class="text-xs font-medium text-slate-400 capitalize leading-tight">Owner</p>
                                                <p class="text-sm font-bold text-slate-800 leading-tight truncate max-w-[150px]">{{ $currentUser->name ?? 'Owner' }}</p>
                                            </div>
                                        </div>
                                        <x-lucide-chevron-up class="w-4 h-4 text-slate-400" />
                                    </div>

                                    <div class="border-t border-slate-100 my-2"></div>

                                    <div class="py-1">
                                        <a
                                            href="{{ $getRouteUrl('password.edit') }}"
                                            @class([
                                                'flex items-center gap-2.5 px-3 py-2 text-sm font-medium rounded-xl transition',
                                                'bg-slate-100 text-slate-900 font-semibold' => request()->routeIs('password.edit', 'password.edit.*'),
                                                'text-slate-600 hover:text-slate-900 hover:bg-slate-50' => ! request()->routeIs('password.edit', 'password.edit.*'),
                                            ])
                                        >
                                            <x-lucide-key class="w-4 h-4 text-slate-500" />
                                            <span>Ubah Kata Sandi</span>
                                        </a>
                                    </div>

                                    <!-- Logout Form POST -->
                                    <form method="POST" action="{{ $getRouteUrl('logout', '/logout') }}" class="mt-3">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="w-full flex items-center justify-center gap-2 bg-[#c5221f] hover:bg-[#a81c19] text-white font-bold py-3 px-4 rounded-xl shadow-xs transition"
                                        >
                                            <span>Logout</span>
                                            <x-lucide-log-out class="w-5 h-5 shrink-0" />
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Mobile Hamburger Button -->
                            <button
                                @click="sidebarOpen = !sidebarOpen"
                                type="button"
                                class="lg:hidden p-2 rounded-xl border border-surface-border text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition focus:outline-none"
                                :aria-expanded="sidebarOpen.toString()"
                                aria-label="Toggle navigasi mobile"
                            >
                                <span x-show="!sidebarOpen"><x-lucide-menu class="w-6 h-6" /></span>
                                <span x-show="sidebarOpen" style="display: none;"><x-lucide-x class="w-6 h-6" /></span>
                            </button>
                        </div>
                    </div>

                    <!-- Baris 2: Ke-6 menu Owner berjajar rapi 1 baris horizontal -->
                    <div class="border-t border-slate-100 pt-3 mt-3 hidden lg:block">
                        <nav class="flex items-center gap-2 overflow-x-auto pb-1" aria-label="Navigasi Owner">
                            @foreach ($ownerNavItems as $item)
                                <a
                                    href="{{ $getRouteUrl($item['route']) }}"
                                    @class([
                                        'inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold transition shrink-0 whitespace-nowrap',
                                        'bg-[#2b2d6e] text-white shadow-xs' => $item['active'],
                                        'border border-surface-border bg-white text-slate-500 hover:text-slate-800 hover:border-slate-300 hover:bg-slate-50' => ! $item['active'],
                                    ])
                                >
                                    @if ($item['icon'] === 'layout-grid')
                                        <x-lucide-layout-grid class="w-4 h-4 shrink-0" />
                                    @elseif ($item['icon'] === 'users')
                                        <x-lucide-users class="w-4 h-4 shrink-0" />
                                    @elseif ($item['icon'] === 'book-open')
                                        <x-lucide-book-open class="w-4 h-4 shrink-0" />
                                    @elseif ($item['icon'] === 'shopping-cart')
                                        <x-lucide-shopping-cart class="w-4 h-4 shrink-0" />
                                    @elseif ($item['icon'] === 'file-text')
                                        <x-lucide-file-text class="w-4 h-4 shrink-0" />
                                    @elseif ($item['icon'] === 'credit-card')
                                        <x-lucide-credit-card class="w-4 h-4 shrink-0" />
                                    @endif
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>
                    </div>

                @elseif ($role === 'cashier')
                    <!-- ================= CASHIER: 1 BARIS ================= -->
                    <div class="flex items-center justify-between gap-4">
                        <!-- Logo NasiCafe di kiri -->
                        <a href="{{ $currentUser && method_exists($currentUser, 'homeRoute') ? $getRouteUrl($currentUser->homeRoute(), '/') : '/' }}" class="flex items-center gap-2.5 shrink-0">
                            <span class="w-9 h-9 rounded-xl bg-[#1b254b] flex items-center justify-center text-white font-bold text-sm shadow-xs">
                                <x-lucide-utensils class="w-5 h-5 text-white" />
                            </span>
                            <span class="text-xl font-bold tracking-tight text-[#1b254b]">NasiCafe</span>
                        </a>

                        <!-- 3 Pill Navigasi di tengah -->
                        <nav class="hidden lg:flex items-center gap-2" aria-label="Navigasi Kasir">
                            @foreach ($cashierNavItems as $item)
                                <a
                                    href="{{ $getRouteUrl($item['route']) }}"
                                    @class([
                                        'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition shrink-0',
                                        'bg-[#2b2d6e] text-white shadow-xs' => $item['active'],
                                        'border border-surface-border bg-white text-slate-500 hover:text-slate-800 hover:border-slate-300 hover:bg-slate-50' => ! $item['active'],
                                    ])
                                >
                                    @if ($item['icon'] === 'layout-grid')
                                        <x-lucide-layout-grid class="w-4 h-4 shrink-0" />
                                    @elseif ($item['icon'] === 'shopping-bag')
                                        <x-lucide-shopping-bag class="w-4 h-4 shrink-0" />
                                    @elseif ($item['icon'] === 'file-text')
                                        <x-lucide-file-text class="w-4 h-4 shrink-0" />
                                    @endif
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>

                        <!-- Profil Kasir di kanan -->
                        <div class="flex items-center gap-2">
                            <div class="relative" @click.outside="profileDropdown = false">
                                <button
                                    @click="profileDropdown = !profileDropdown"
                                    type="button"
                                    class="flex items-center gap-3 pl-2 pr-3 py-1.5 rounded-xl hover:bg-slate-50 transition focus:outline-none"
                                    :aria-expanded="profileDropdown.toString()"
                                    aria-haspopup="true"
                                >
                                    <span class="w-9 h-9 rounded-full bg-[#1b254b] text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                        {{ strtoupper(substr($currentUser->name ?? 'K', 0, 1)) }}
                                    </span>
                                    <div class="text-left hidden sm:block">
                                        <p class="text-xs font-medium text-slate-400 capitalize leading-tight">Kasir</p>
                                        <p class="text-sm font-semibold text-slate-800 leading-tight truncate max-w-[140px]">{{ $currentUser->name ?? 'Kasir' }}</p>
                                    </div>
                                    <x-lucide-chevron-down class="w-4 h-4 text-slate-500 transition-transform duration-200" ::class="profileDropdown ? 'rotate-180' : ''" />
                                </button>

                                <!-- Dropdown Card -->
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
                                                {{ strtoupper(substr($currentUser->name ?? 'K', 0, 1)) }}
                                            </span>
                                            <div>
                                                <p class="text-xs font-medium text-slate-400 capitalize leading-tight">Kasir</p>
                                                <p class="text-sm font-bold text-slate-800 leading-tight truncate max-w-[150px]">{{ $currentUser->name ?? 'Kasir' }}</p>
                                            </div>
                                        </div>
                                        <x-lucide-chevron-up class="w-4 h-4 text-slate-400" />
                                    </div>

                                    <div class="border-t border-slate-100 my-2"></div>

                                    <div class="py-1">
                                        <a
                                            href="{{ $getRouteUrl('password.edit') }}"
                                            @class([
                                                'flex items-center gap-2.5 px-3 py-2 text-sm font-medium rounded-xl transition',
                                                'bg-slate-100 text-slate-900 font-semibold' => request()->routeIs('password.edit', 'password.edit.*'),
                                                'text-slate-600 hover:text-slate-900 hover:bg-slate-50' => ! request()->routeIs('password.edit', 'password.edit.*'),
                                            ])
                                        >
                                            <x-lucide-key class="w-4 h-4 text-slate-500" />
                                            <span>Ubah Kata Sandi</span>
                                        </a>
                                    </div>

                                    <!-- Logout Form POST -->
                                    <form method="POST" action="{{ $getRouteUrl('logout', '/logout') }}" class="mt-3">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="w-full flex items-center justify-center gap-2 bg-[#c5221f] hover:bg-[#a81c19] text-white font-bold py-3 px-4 rounded-xl shadow-xs transition"
                                        >
                                            <span>Logout</span>
                                            <x-lucide-log-out class="w-5 h-5 shrink-0" />
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Mobile Hamburger Button -->
                            <button
                                @click="sidebarOpen = !sidebarOpen"
                                type="button"
                                class="lg:hidden p-2 rounded-xl border border-surface-border text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition focus:outline-none"
                                :aria-expanded="sidebarOpen.toString()"
                                aria-label="Toggle navigasi mobile"
                            >
                                <span x-show="!sidebarOpen"><x-lucide-menu class="w-6 h-6" /></span>
                                <span x-show="sidebarOpen" style="display: none;"><x-lucide-x class="w-6 h-6" /></span>
                            </button>
                        </div>
                    </div>

                @elseif ($role === 'customer')
                    <!-- ================= CUSTOMER: TANPA TOP PILL & HAMBURGER ================= -->
                    <div class="flex items-center justify-between gap-4">
                        <a href="{{ $getRouteUrl('customer.order', '/') }}" class="flex items-center gap-2.5 shrink-0">
                            <span class="w-9 h-9 rounded-xl bg-[#1b254b] flex items-center justify-center text-white font-bold text-sm shadow-xs">
                                <x-lucide-utensils class="w-5 h-5 text-white" />
                            </span>
                            <span class="text-xl font-bold tracking-tight text-[#1b254b]">NasiCafe</span>
                        </a>

                        <!-- Profil Customer di kanan -->
                        <div class="relative" @click.outside="profileDropdown = false">
                            <button
                                @click="profileDropdown = !profileDropdown"
                                type="button"
                                class="flex items-center gap-3 pl-2 pr-3 py-1.5 rounded-xl hover:bg-slate-50 transition focus:outline-none"
                                :aria-expanded="profileDropdown.toString()"
                                aria-haspopup="true"
                            >
                                <span class="w-9 h-9 rounded-full bg-[#1b254b] text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                    {{ strtoupper(substr($currentUser->name ?? 'C', 0, 1)) }}
                                </span>
                                <div class="text-left hidden sm:block">
                                    <p class="text-xs font-medium text-slate-400 capitalize leading-tight">Pelanggan</p>
                                    <p class="text-sm font-semibold text-slate-800 leading-tight truncate max-w-[140px]">{{ $currentUser->name ?? 'Pelanggan' }}</p>
                                </div>
                                <x-lucide-chevron-down class="w-4 h-4 text-slate-500 transition-transform duration-200" ::class="profileDropdown ? 'rotate-180' : ''" />
                            </button>

                            <!-- Dropdown Card -->
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
                                            {{ strtoupper(substr($currentUser->name ?? 'C', 0, 1)) }}
                                        </span>
                                        <div>
                                            <p class="text-xs font-medium text-slate-400 capitalize leading-tight">Pelanggan</p>
                                            <p class="text-sm font-bold text-slate-800 leading-tight truncate max-w-[150px]">{{ $currentUser->name ?? 'Pelanggan' }}</p>
                                        </div>
                                    </div>
                                    <x-lucide-chevron-up class="w-4 h-4 text-slate-400" />
                                </div>

                                <div class="border-t border-slate-100 my-2"></div>

                                <div class="py-1">
                                    <a
                                        href="{{ $getRouteUrl('password.edit') }}"
                                        @class([
                                            'flex items-center gap-2.5 px-3 py-2 text-sm font-medium rounded-xl transition',
                                            'bg-slate-100 text-slate-900 font-semibold' => request()->routeIs('password.edit', 'password.edit.*'),
                                            'text-slate-600 hover:text-slate-900 hover:bg-slate-50' => ! request()->routeIs('password.edit', 'password.edit.*'),
                                        ])
                                    >
                                        <x-lucide-key class="w-4 h-4 text-slate-500" />
                                        <span>Ubah Kata Sandi</span>
                                    </a>
                                </div>

                                <!-- Logout Form POST -->
                                <form method="POST" action="{{ $getRouteUrl('logout', '/logout') }}" class="mt-3">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full flex items-center justify-center gap-2 bg-[#c5221f] hover:bg-[#a81c19] text-white font-bold py-3 px-4 rounded-xl shadow-xs transition"
                                    >
                                        <span>Logout</span>
                                        <x-lucide-log-out class="w-5 h-5 shrink-0" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                @else
                    <!-- ================= GUEST (auth()->guest()) ================= -->
                    <div class="flex items-center justify-between gap-4">
                        <a href="/" class="flex items-center gap-2.5 shrink-0">
                            <span class="w-9 h-9 rounded-xl bg-[#1b254b] flex items-center justify-center text-white font-bold text-sm shadow-xs">
                                <x-lucide-utensils class="w-5 h-5 text-white" />
                            </span>
                            <span class="text-xl font-bold tracking-tight text-[#1b254b]">NasiCafe</span>
                        </a>

                        <a href="{{ $getRouteUrl('login', '/login') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-[#2b2d6e] text-white hover:bg-[#202253] transition">
                            Masuk
                        </a>
                    </div>
                @endif

                <!-- Mobile Collapse Navigation (Hanya untuk Owner & Kasir saat tombol hamburger aktif) -->
                @if (in_array($role, ['owner', 'cashier']))
                    <nav
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
                                @if ($item['icon'] === 'layout-grid')
                                    <x-lucide-layout-grid class="w-5 h-5 shrink-0" />
                                @elseif ($item['icon'] === 'users')
                                    <x-lucide-users class="w-5 h-5 shrink-0" />
                                @elseif ($item['icon'] === 'book-open')
                                    <x-lucide-book-open class="w-5 h-5 shrink-0" />
                                @elseif ($item['icon'] === 'shopping-cart')
                                    <x-lucide-shopping-cart class="w-5 h-5 shrink-0" />
                                @elseif ($item['icon'] === 'shopping-bag')
                                    <x-lucide-shopping-bag class="w-5 h-5 shrink-0" />
                                @elseif ($item['icon'] === 'file-text')
                                    <x-lucide-file-text class="w-5 h-5 shrink-0" />
                                @elseif ($item['icon'] === 'credit-card')
                                    <x-lucide-credit-card class="w-5 h-5 shrink-0" />
                                @endif
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach

                        <div class="border-t border-surface-border pt-3 mt-3">
                            <a
                                href="{{ $getRouteUrl('password.edit') }}"
                                @class([
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition',
                                    'bg-slate-100 text-slate-900 font-semibold' => request()->routeIs('password.edit', 'password.edit.*'),
                                    'text-slate-600 hover:text-slate-900 hover:bg-slate-50' => ! request()->routeIs('password.edit', 'password.edit.*'),
                                ])
                            >
                                <x-lucide-key class="w-5 h-5 shrink-0 text-slate-500" />
                                <span>Ubah Kata Sandi</span>
                            </a>

                            <form method="POST" action="{{ $getRouteUrl('logout', '/logout') }}" class="mt-2">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full flex items-center justify-center gap-2 bg-[#c5221f] hover:bg-[#a81c19] text-white font-bold py-3 px-4 rounded-xl shadow-xs transition"
                                >
                                    <span>Logout</span>
                                    <x-lucide-log-out class="w-5 h-5 shrink-0" />
                                </button>
                            </form>
                        </div>
                    </nav>
                @endif
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Banner Peringatan Stok Menipis (Khusus Owner & Kasir) -->
            <x-low-stock-banner />

            <!-- Banner Notifikasi Flash / Validasi -->
            <x-alert />

            <!-- Page Slot / Content -->
            @yield('content')
            {{ $slot ?? '' }}
        </main>

        <!-- Customer Bottom Navigation Bar (Konsisten di Mobile maupun Desktop/Tablet) -->
        @if ($currentUser && ($currentUser->isCustomer() || $role === 'customer'))
            <nav class="fixed bottom-0 left-0 right-0 z-40 px-4 pb-3 pointer-events-none" aria-label="Navigasi Pelanggan">
                <div class="max-w-md mx-auto pointer-events-auto bg-white/95 backdrop-blur-md border border-surface-border rounded-2xl sm:rounded-3xl shadow-lg px-6 pt-3 pb-2 flex flex-col">
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
                            <x-lucide-shopping-cart class="w-6 h-6" />
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
                            <x-lucide-file-text class="w-6 h-6" />
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
                            <x-lucide-user class="w-6 h-6" />
                            <span>Profil</span>
                        </a>
                    </div>

                    <!-- Bottom Indicator Pill -->
                    <div class="w-28 h-1 bg-slate-300 rounded-full mx-auto mt-2"></div>
                </div>
            </nav>
        @endif

        @stack('scripts')
    </body>
</html>
