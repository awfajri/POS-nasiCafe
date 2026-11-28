@extends('layouts.app')

@section('title', 'Laporan Keuangan')

@section('content')
    <div class="space-y-6">
        <!-- ===== Header ===== -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Laporan Keuangan</h1>
                <p class="text-sm text-slate-500 mt-0.5">Rekap transaksi dan omzet pada periode tertentu.</p>
            </div>
        </div>

        <!-- ===== Form Filter Periode ===== -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs">
            <form method="GET" action="{{ route('owner.reports.index') }}" class="flex flex-col sm:flex-row items-end gap-4">
                <div class="flex-1 min-w-0">
                    <label for="start_date" class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Awal</label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        value="{{ old('start_date', $start->format('Y-m-d')) }}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                    >
                    @error('start_date')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex-1 min-w-0">
                    <label for="end_date" class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Akhir</label>
                    <input
                        type="date"
                        id="end_date"
                        name="end_date"
                        value="{{ old('end_date', $end->format('Y-m-d')) }}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                    >
                    @error('end_date')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#2b2d6e] text-white hover:bg-[#202253] transition shrink-0"
                >
                    <x-lucide-filter class="w-4 h-4" />
                    <span>Terapkan</span>
                </button>
            </form>
        </div>

        <!-- ===== Kartu Total Pemasukan + Export ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <x-stat-card
                title="Total Pemasukan"
                :value="rupiah($summary['omzet'])"
                subtitle="Omzet periode terpilih"
                icon="revenue"
                color="success"
            />

            <x-stat-card
                title="Total Transaksi"
                :value="number_format($summary['total_transactions'])"
                subtitle="Semua transaksi periode"
                icon="transactions"
                color="brand"
            />

            <x-stat-card
                title="Omzet per Metode"
                :value="rupiah($summary['cash'] + $summary['qris'])"
                subtitle="Tunai {{ rupiah($summary['cash']) }} · QRIS {{ rupiah($summary['qris']) }}"
                icon="credit-card"
                color="info"
            />
        </div>

        <!-- ===== Tombol Ekspor ===== -->
        <div class="flex flex-wrap gap-3">
            <a
                href="{{ route('owner.reports.excel', ['start_date' => $start->format('Y-m-d'), 'end_date' => $end->format('Y-m-d')]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition"
            >
                <x-lucide-file-spreadsheet class="w-4 h-4" />
                <span>Export Excel</span>
            </a>
            <a
                href="{{ route('owner.reports.pdf', ['start_date' => $start->format('Y-m-d'), 'end_date' => $end->format('Y-m-d')]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-rose-600 text-white hover:bg-rose-700 transition"
            >
                <x-lucide-file-text class="w-4 h-4" />
                <span>Cetak PDF</span>
            </a>
        </div>

        <!-- ===== Tabel Transaksi ===== -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-slate-900">Rincian Transaksi</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Periode {{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}
                </p>
            </div>

            @if ($transactions->isEmpty())
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-10 text-center text-sm text-slate-500">
                    Tidak ada transaksi pada periode ini.
                </div>
            @else
                <div class="overflow-x-auto -mx-5 px-5">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="py-3 pr-4 font-semibold">Kode Nota</th>
                                <th class="py-3 pr-4 font-semibold">Tanggal</th>
                                <th class="py-3 pr-4 font-semibold">Pelanggan</th>
                                <th class="py-3 pr-4 font-semibold">Kasir</th>
                                <th class="py-3 pr-4 font-semibold">Sumber</th>
                                <th class="py-3 pr-4 font-semibold">Status</th>
                                <th class="py-3 pr-4 font-semibold">Bayar</th>
                                <th class="py-3 pr-4 font-semibold text-right">Total</th>
                                <th class="py-3 font-semibold text-right">Dibayar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($transactions as $tx)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 pr-4 font-mono text-xs text-slate-700">{{ $tx->invoice_code }}</td>
                                    <td class="py-3 pr-4 text-slate-600 whitespace-nowrap">{{ $tx->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 pr-4 text-slate-800">{{ $tx->customer_name }}</td>
                                    <td class="py-3 pr-4 text-slate-500">{{ $tx->cashier?->name ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-slate-500 capitalize">{{ $tx->source }}</td>
                                    <td class="py-3 pr-4"><x-status-badge :status="$tx->status" /></td>
                                    <td class="py-3 pr-4"><x-payment-badge :status="$tx->payment_status" /></td>
                                    <td class="py-3 pr-4 text-right tabular-nums text-slate-700">{{ rupiah($tx->total_amount) }}</td>
                                    <td class="py-3 text-right tabular-nums font-semibold text-slate-800">{{ rupiah($tx->paid_amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
