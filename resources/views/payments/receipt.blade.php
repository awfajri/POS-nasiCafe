@extends('layouts.print')

@section('title', 'Struk Pembayaran - ' . ($payment->transaction?->invoice_code ?? 'NasiCafe'))

@php
    $transaction = $payment->transaction;
    $remainingBalance = $transaction
        ? max(0, (int) $transaction->total_amount - (int) $transaction->paid_amount)
        : 0;
@endphp

@section('content')
<div class="space-y-4">
    <!-- Bar Tombol Aksi (Sembunyi saat Print) -->
    <div class="no-print flex flex-wrap items-center justify-between gap-2 bg-white p-3 rounded-2xl border border-slate-200 shadow-xs">
        <a
            href="{{ $transaction && \Illuminate\Support\Facades\Route::has('transactions.show') ? route('transactions.show', $transaction) : (\Illuminate\Support\Facades\Route::has('transactions.index') ? route('transactions.index') : '#') }}"
            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
        >
            <x-lucide-arrow-left class="w-4 h-4" />
            <span>Detail Nota</span>
        </a>

        <div class="flex items-center gap-2">
            @if (auth()->user()?->isCashier() && $transaction && $transaction->canBePaid())
                <a
                    href="{{ route('payments.checkout', $transaction) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition"
                >
                    <span>Lanjut Split Bill</span>
                </a>
            @endif

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-[#2b2d6e] hover:bg-[#1b254b] text-white shadow-xs transition"
            >
                <x-lucide-printer class="w-4 h-4" />
                <span>Cetak Struk</span>
            </button>
        </div>
    </div>

    <!-- Kertas Struk Thermal -->
    <div class="receipt-sheet bg-white rounded-2xl border border-slate-200 shadow-sm p-6 font-['JetBrains_Mono',monospace] text-xs text-slate-800">
        <!-- Kop Struk -->
        <div class="text-center border-b border-dashed border-slate-300 pb-4">
            <h1 class="text-lg font-bold tracking-wider uppercase text-slate-900">NasiCafe</h1>
            <p class="text-[11px] text-slate-500 mt-0.5">Struk Pembayaran Resmi</p>
        </div>

        <!-- Informasi Transaksi -->
        <div class="py-3 border-b border-dashed border-slate-300 space-y-1 text-[11px]">
            <div class="flex justify-between">
                <span class="text-slate-500">No. Nota</span>
                <span class="font-bold">{{ $transaction?->invoice_code ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">No. Bayar</span>
                <span>#PAY-{{ str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Tanggal</span>
                <span>{{ $payment->created_at?->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Kasir</span>
                <span>{{ $payment->cashier?->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Pelanggan</span>
                <span>{{ $transaction?->customer_name ?? '-' }}</span>
            </div>
            @if ($payment->payer_name && $payment->payer_name !== $transaction?->customer_name)
                <div class="flex justify-between">
                    <span class="text-slate-500">Pembayar</span>
                    <span class="font-semibold">{{ $payment->payer_name }}</span>
                </div>
            @endif
            @if ($transaction?->table_number)
                <div class="flex justify-between">
                    <span class="text-slate-500">Meja</span>
                    <span>{{ $transaction->table_number }}</span>
                </div>
            @endif
        </div>

        <!-- Daftar Item yang Dibayar -->
        <div class="py-3 border-b border-dashed border-slate-300 space-y-2">
            @foreach ($paidItems as $item)
                <div>
                    <div class="font-semibold text-slate-900">{{ $item['menu_name'] }}</div>
                    <div class="flex justify-between text-[11px] text-slate-600">
                        <span>{{ $item['qty'] }} porsi</span>
                        <span class="font-semibold text-slate-800">{{ rupiah($item['subtotal']) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Ringkasan Nominal Pembayaran -->
        <div class="py-3 border-b border-dashed border-slate-300 space-y-1.5 text-[11px]">
            <div class="flex justify-between text-sm font-bold text-slate-900">
                <span>TOTAL BAYAR</span>
                <span>{{ rupiah($payment->amount_paid) }}</span>
            </div>

            <div class="flex justify-between">
                <span class="text-slate-500">Metode</span>
                <span class="font-bold uppercase">{{ strtoupper($payment->payment_method) }}</span>
            </div>

            @if ($payment->payment_method === 'cash')
                <div class="flex justify-between">
                    <span class="text-slate-500">Tunai Diterima</span>
                    <span>{{ rupiah($payment->cash_received ?? 0) }}</span>
                </div>
                <div class="flex justify-between font-bold text-slate-900">
                    <span>Kembalian</span>
                    <span>{{ rupiah($payment->change_amount ?? 0) }}</span>
                </div>
            @endif
        </div>

        <!-- Status Pelunasan Nota -->
        <div class="pt-3 text-center space-y-1">
            @if ($transaction?->payment_status === \App\Models\Transaction::PAYMENT_PAID)
                <p class="font-bold uppercase tracking-wider text-slate-900">*** LUNAS ***</p>
            @else
                <p class="font-bold uppercase tracking-wider text-amber-700">*** PEMBAYARAN SEBAGIAN ***</p>
                <p class="text-[11px] text-slate-500">
                    Sisa tagihan nota: {{ rupiah($remainingBalance) }}
                </p>
            @endif
            <p class="text-[11px] text-slate-500 pt-2">Terima kasih telah berkunjung ke NasiCafe!</p>
        </div>
    </div>
</div>
@endsection