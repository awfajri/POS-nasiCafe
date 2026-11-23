@extends('layouts.app')

@section('title', 'Dasbor Kasir')
@section('header', 'Dasbor Kasir')

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Antrean Pending" :value="$pendingCount" icon="receipt" color="warning" />
            <x-stat-card title="Sedang Diproses" :value="$processingCount" icon="shopping-bag" color="info" />
            <x-stat-card title="Selesai Hari Ini" :value="$completedToday" icon="success" color="success" />
            <x-stat-card title="Total Hari Ini" :value="'Rp '.number_format($totalToday, 0, ',', '.')" icon="money" color="brand" />
        </div>

        <x-low-stock-banner
            :should-display="$lowStockMenus->isNotEmpty()"
            :count="$lowStockMenus->count()"
            :low-stock-menus="$lowStockMenus"
        />

        <section class="rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Antrean Aktif</h2>
                    <p class="mt-1 text-sm text-slate-500">Pesanan yang perlu dipantau atau diproses.</p>
                </div>
                <a href="{{ route('transactions.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Lihat semua</a>
            </div>

            @if ($activeQueue->isEmpty())
                <p class="rounded-2xl bg-slate-50 p-6 text-center text-sm text-slate-500">Belum ada antrean aktif.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-3 py-3">Invoice</th>
                                <th class="px-3 py-3">Pelanggan</th>
                                <th class="px-3 py-3">Sumber / Meja</th>
                                <th class="px-3 py-3">Status</th>
                                <th class="px-3 py-3">Pembayaran</th>
                                <th class="px-3 py-3 text-right">Total</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($activeQueue as $transaction)
                                <tr>
                                    <td class="px-3 py-4 font-semibold text-slate-900">{{ $transaction->invoice_code }}</td>
                                    <td class="px-3 py-4 text-slate-700">{{ $transaction->customer_name }}</td>
                                    <td class="px-3 py-4 text-slate-600">
                                        <span class="capitalize">{{ str_replace('_', ' ', $transaction->source) }}</span>
                                        @if ($transaction->table_number)
                                            <span class="block text-xs text-slate-400">Meja {{ $transaction->table_number }}</span>
                                        @endif
                                        @if ($transaction->source === \App\Models\Transaction::SOURCE_SELF_ORDER && $transaction->payment_status !== \App\Models\Transaction::PAYMENT_PAID)
                                            <span class="mt-1 block text-xs font-semibold text-amber-600">Menunggu pembayaran QRIS</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4"><x-status-badge :status="$transaction->status" /></td>
                                    <td class="px-3 py-4"><x-payment-badge :status="$transaction->payment_status" /></td>
                                    <td class="px-3 py-4 text-right font-semibold text-slate-900">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-3 py-4 text-right"><a href="{{ route('transactions.show', $transaction) }}" class="font-semibold text-brand-600 hover:text-brand-700">Detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection