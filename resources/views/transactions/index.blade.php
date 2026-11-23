@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('header', 'Riwayat Transaksi')

@section('content')
    <div class="space-y-6">
        <form method="GET" class="grid gap-3 rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs sm:grid-cols-[1fr_180px_auto]">
            <input name="search" value="{{ request('search') }}" placeholder="Cari invoice atau pelanggan..." class="rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
            <select name="status" class="rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">Semua status</option>
                @foreach (['pending', 'processing', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Filter</button>
        </form>

        <section class="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                        <tr><th class="px-5 py-4">Invoice</th><th class="px-5 py-4">Pelanggan</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Pembayaran</th><th class="px-5 py-4">Total</th><th class="px-5 py-4"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td class="px-5 py-4 font-semibold">{{ $transaction->invoice_code }}</td>
                                <td class="px-5 py-4">{{ $transaction->customer_name }}</td>
                                <td class="px-5 py-4"><x-status-badge :status="$transaction->status" /></td>
                                <td class="px-5 py-4"><x-payment-badge :status="$transaction->payment_status" /></td>
                                <td class="px-5 py-4">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right"><a class="font-semibold text-brand-600" href="{{ route('transactions.show', $transaction) }}">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Transaksi tidak ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($transactions->hasPages())
                <div class="border-t border-slate-100 p-5">{{ $transactions->links() }}</div>
            @endif
        </section>
    </div>
@endsection