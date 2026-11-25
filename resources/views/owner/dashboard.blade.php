@extends('layouts.app')

@section('title', 'Dashboard Owner')

@section('content')
    <div class="space-y-6">
        <!-- ===== Header Halaman ===== -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard Owner</h1>
                <p class="text-sm text-slate-500 mt-0.5">
                    Ringkasan omzet, transaksi, dan stok menu hari ini.
                </p>
            </div>
        </div>

        <!-- ===== 4 Kartu Ringkasan ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-stat-card
                title="Omzet Hari Ini"
                :value="rupiah($todayRevenue)"
                subtitle="Pendapatan diterima hari ini"
                icon="revenue"
                color="success"
            />
            <x-stat-card
                title="Omzet Bulan Ini"
                :value="rupiah($monthRevenue)"
                subtitle="Pendapatan bulan berjalan"
                icon="trending-up"
                color="info"
            />
            <x-stat-card
                title="Total Transaksi"
                :value="number_format($todayTransactions)"
                subtitle="Transaksi hari ini"
                icon="transactions"
                color="brand"
            />
            <x-stat-card
                title="Menu Kritis"
                :value="number_format($criticalCount)"
                subtitle="Stok di bawah batas minimum"
                :badge="$criticalCount > 0 ? 'Perlu Restock' : 'Aman'"
                icon="warning"
                :color="$criticalCount > 0 ? 'danger' : 'success'"
            />
        </div>

        <!-- ===== Grafik Penjualan ===== -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs" x-data="ownerDashboardApp()">
            <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Grafik Penjualan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Omzet harian (14 hari) & bulanan (6 bulan)</p>
                </div>
                <div class="inline-flex rounded-xl border border-slate-200 bg-slate-50 p-1" role="tablist" aria-label="Pilih rentang grafik">
                    <button
                        type="button"
                        role="tab"
                        :aria-selected="chartPeriod === 'daily' ? 'true' : 'false'"
                        @click="chartPeriod = 'daily'; refreshChart()"
                        :class="chartPeriod === 'daily' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-700'"
                        class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                    >
                        Harian
                    </button>
                    <button
                        type="button"
                        role="tab"
                        :aria-selected="chartPeriod === 'monthly' ? 'true' : 'false'"
                        @click="chartPeriod = 'monthly'; refreshChart()"
                        :class="chartPeriod === 'monthly' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-700'"
                        class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                    >
                        Bulanan
                    </button>
                </div>
            </div>

            <div class="relative h-72">
                <canvas id="salesChart" aria-label="Grafik omzet penjualan" role="img"></canvas>
            </div>
        </div>

        <!-- ===== Tabel Low Stock Alert ===== -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs">
            <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Low Stock Alert</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Menu dengan stok {{ '≤' }} batas minimum ({{ $criticalCount }} menu)
                    </p>
                </div>
                @if (\Illuminate\Support\Facades\Route::has('owner.menus.index'))
                    <a
                        href="{{ route('owner.menus.index') }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold bg-[#2b2d6e] text-white hover:bg-[#202253] transition"
                    >
                        <x-lucide-shopping-cart class="w-4 h-4" />
                        <span>Kelola Menu & Stok</span>
                    </a>
                @endif
            </div>

            @if ($criticalMenus->isEmpty())
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/70 px-4 py-4 text-sm text-emerald-800">
                    <x-lucide-check-circle class="w-5 h-5 shrink-0 text-emerald-600" />
                    <span>Semua menu memiliki stok di atas batas minimum. Stok aman.</span>
                </div>
            @else
                <div class="overflow-x-auto -mx-5 px-5">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="py-3 pr-4 font-semibold">Menu</th>
                                <th class="py-3 pr-4 font-semibold">Kategori</th>
                                <th class="py-3 pr-4 font-semibold text-right">Harga</th>
                                <th class="py-3 pr-4 font-semibold text-center">Stok</th>
                                <th class="py-3 pr-4 font-semibold text-center">Min. Stok</th>
                                <th class="py-3 font-semibold text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($criticalMenus as $menu)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 pr-4 font-medium text-slate-800">{{ $menu->name }}</td>
                                    <td class="py-3 pr-4 text-slate-500">{{ $menu->category?->name ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-right tabular-nums text-slate-600">{{ rupiah($menu->price) }}</td>
                                    <td class="py-3 pr-4 text-center">
                                        <span class="inline-flex items-center gap-1 tabular-nums font-semibold {{ $menu->stock === 0 ? 'text-rose-600' : 'text-amber-600' }}">
                                            <x-lucide-package class="w-3.5 h-3.5" />
                                            {{ $menu->stock }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4 text-center tabular-nums text-slate-500">{{ $menu->min_stock }}</td>
                                    <td class="py-3 text-right">
                                        @if ($menu->stock === 0)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Habis
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menipis
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.ownerDashboard = {
            daily: @json($chartDaily),
            monthly: @json($chartMonthly),
        };
    </script>
    @vite('resources/js/owner-dashboard.js')
@endpush
