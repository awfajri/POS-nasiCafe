@extends('layouts.app')

@section('title', 'Detail '.$transaction->invoice_code)
@section('header', 'Detail Transaksi')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div><a href="{{ route('transactions.index') }}" class="text-sm font-semibold text-brand-600">&larr; Kembali ke transaksi</a><h1 class="mt-2 text-2xl font-semibold text-slate-900">{{ $transaction->invoice_code }}</h1><p class="mt-1 text-sm text-slate-500">{{ $transaction->created_at?->format('d M Y, H:i') }}</p></div>
            <div class="flex gap-2"><x-status-badge :status="$transaction->status" /><x-payment-badge :status="$transaction->payment_status" /></div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <section class="rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs sm:p-6">
                <h2 class="mb-4 text-lg font-semibold">Item Pesanan</h2>
                <div class="overflow-x-auto"><table class="w-full min-w-[520px] text-left text-sm"><thead class="border-b border-slate-200 text-xs uppercase text-slate-500"><tr><th class="py-3">Menu</th><th class="py-3">Harga</th><th class="py-3">Qty</th><th class="py-3">Dibayar</th><th class="py-3 text-right">Subtotal</th></tr></thead><tbody class="divide-y divide-slate-100">
                    @foreach ($transaction->details as $detail)<tr><td class="py-4 font-medium">{{ $detail->menu?->name ?? 'Menu dihapus' }}</td><td class="py-4">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td><td class="py-4">{{ $detail->quantity }}</td><td class="py-4">{{ $detail->paid_quantity }}</td><td class="py-4 text-right font-semibold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td></tr>@endforeach
                </tbody></table></div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs"><h2 class="mb-4 text-lg font-semibold">Ringkasan</h2><dl class="space-y-3 text-sm"><div class="flex justify-between"><dt class="text-slate-500">Pelanggan</dt><dd class="font-medium">{{ $transaction->customer_name }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Meja</dt><dd class="font-medium">{{ $transaction->table_number ?: '-' }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Total</dt><dd class="font-semibold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Dibayar</dt><dd class="font-semibold text-emerald-600">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</dd></div><div class="flex justify-between border-t border-slate-100 pt-3"><dt class="font-semibold">Sisa</dt><dd class="font-bold">Rp {{ number_format(max(0, $transaction->total_amount - $transaction->paid_amount), 0, ',', '.') }}</dd></div></dl></section>
                @include('transactions._status-actions', ['transaction' => $transaction, 'transitions' => $transitions])
                @includeIf('payments._actions', ['transaction' => $transaction])
            </aside>
        </div>
    </div>
@endsection