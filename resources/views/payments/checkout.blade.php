@extends('layouts.app')

@section('title', 'Pembayaran & Split Bill - ' . $transaction->invoice_code)

@php
    $remainingTotal = max(0, (int) $transaction->total_amount - (int) $transaction->paid_amount);
    $itemsJson = $remainingItems->map(fn ($item) => [
        'detail_id' => $item->id,
        'name' => $item->menu?->name ?? 'Menu #' . $item->menu_id,
        'unit_price' => (int) $item->unit_price,
        'remaining_qty' => (int) $item->remaining_quantity,
        'qty' => 1,
        'selected' => false,
    ])->values();
@endphp

@section('content')
<div
    class="space-y-6"
    x-data="checkoutHandler({
        remainingTotal: {{ $remainingTotal }},
        items: @js($itemsJson),
        defaultPayer: @js(old('payer_name', $transaction->customer_name)),
        oldMethod: @js(old('payment_method', 'cash')),
        oldCashReceived: @js(old('cash_received', '')),
        hasSplitError: {{ $errors->has('items') || $errors->has('items.*') ? 'true' : 'false' }}
    })"
>
    <!-- Header & Navigasi Kembali -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a
                href="{{ \Illuminate\Support\Facades\Route::has('transactions.show') ? route('transactions.show', $transaction) : '#' }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-[#1b254b] transition mb-1"
            >
                <x-lucide-arrow-left class="w-4 h-4" />
                <span>Kembali ke Detail Nota</span>
            </a>
            <h1 class="text-2xl font-bold text-[#1b254b] tracking-tight">
                Pembayaran & Split Bill
            </h1>
            <p class="text-sm text-slate-500">
                Nota <span class="font-semibold text-slate-700">{{ $transaction->invoice_code }}</span>
                &bull; Pelanggan: <span class="font-semibold text-slate-700">{{ $transaction->customer_name }}</span>
                @if ($transaction->table_number)
                    &bull; Meja <span class="font-semibold text-slate-700">{{ $transaction->table_number }}</span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            <x-status-badge :status="$transaction->status" />
            <x-payment-badge :status="$transaction->payment_status" />
        </div>
    </div>

    <!-- Kartu Ringkasan Tagihan -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-surface-border p-4 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Tagihan</p>
            <p class="text-xl font-bold text-[#1b254b] mt-1">{{ rupiah($transaction->total_amount) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-surface-border p-4 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sudah Dibayar</p>
            <p class="text-xl font-bold text-emerald-600 mt-1">{{ rupiah($transaction->paid_amount) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-surface-border p-4 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sisa Tagihan</p>
            <p class="text-xl font-bold text-amber-600 mt-1">{{ rupiah($remainingTotal) }}</p>
        </div>
    </div>

    <!-- Grid Utama: Form Pembayaran (Kiri) & Riwayat Pembayaran Nota (Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Kolom Form Checkout (2 kolom di desktop) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-surface-border shadow-xs overflow-hidden">
            <!-- Tab Switcher: Bayar Semua vs Split Bill -->
            <div class="grid grid-cols-2 border-b border-surface-border bg-slate-50/70 p-1.5 gap-1.5">
                <button
                    type="button"
                    @click="tab = 'full'"
                    :class="tab === 'full'
                        ? 'bg-[#2b2d6e] text-white shadow-xs'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-white/70'"
                    class="flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold transition"
                >
                    <x-lucide-wallet class="w-4 h-4" />
                    <span>Bayar Semua Sisa</span>
                </button>

                <button
                    type="button"
                    @click="tab = 'split'"
                    :class="tab === 'split'
                        ? 'bg-[#2b2d6e] text-white shadow-xs'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-white/70'"
                    class="flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold transition"
                >
                    <x-lucide-users class="w-4 h-4" />
                    <span>Split Bill / Per Item</span>
                </button>
            </div>

            <!-- ==================== TAB 1: BAYAR SEMUA ==================== -->
            <form
                x-show="tab === 'full'"
                method="POST"
                action="{{ route('payments.pay-full', $transaction) }}"
                class="p-5 sm:p-6 space-y-5"
            >
                @csrf

                <!-- Daftar Item Sisa yang Akan Dilunasi -->
                <div>
                    <h2 class="text-sm font-bold text-[#1b254b] mb-2.5">Rincian Item yang Dilunasi</h2>
                    <div class="rounded-xl border border-surface-border divide-y divide-slate-100 bg-slate-50/50">
                        @foreach ($remainingItems as $item)
                            <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $item->menu?->name ?? 'Menu #' . $item->menu_id }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $item->remaining_quantity }} porsi &times; {{ rupiah($item->unit_price) }}
                                    </p>
                                </div>
                                <span class="font-bold text-slate-800">
                                    {{ rupiah($item->remaining_quantity * $item->unit_price) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Nama Pembayar -->
                <div>
                    <label for="full_payer_name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Nama Pembayar <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input
                        id="full_payer_name"
                        type="text"
                        name="payer_name"
                        x-model="payerName"
                        maxlength="100"
                        placeholder="Contoh: {{ $transaction->customer_name }}"
                        class="w-full rounded-xl border border-surface-border px-3.5 py-2.5 text-sm focus:border-[#2b2d6e] focus:ring-2 focus:ring-[#2b2d6e]/20 focus:outline-none"
                    >
                </div>

                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            :class="paymentMethod === 'cash' ? 'border-[#2b2d6e] bg-[#2b2d6e]/5 text-[#1b254b]' : 'border-surface-border bg-white text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition font-semibold text-sm"
                        >
                            <input type="radio" name="payment_method" value="cash" x-model="paymentMethod" class="sr-only">
                            <x-lucide-wallet class="w-5 h-5 text-[#2b2d6e]" />
                            <span>Tunai (Cash)</span>
                        </label>

                        <label
                            :class="paymentMethod === 'qris' ? 'border-[#2b2d6e] bg-[#2b2d6e]/5 text-[#1b254b]' : 'border-surface-border bg-white text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition font-semibold text-sm"
                        >
                            <input type="radio" name="payment_method" value="qris" x-model="paymentMethod" class="sr-only">
                            <x-lucide-qr-code class="w-5 h-5 text-[#2b2d6e]" />
                            <span>QRIS</span>
                        </label>
                    </div>
                </div>

                <!-- Input Uang Tunai & Kembalian (Khusus Cash) -->
                <div x-show="paymentMethod === 'cash'" class="space-y-3 rounded-xl bg-slate-50 border border-surface-border p-4">
                    <div>
                        <label for="full_cash_received" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Uang Tunai Diterima (Rp)
                        </label>
                        <input
                            id="full_cash_received"
                            type="number"
                            name="cash_received"
                            x-model.number="cashReceived"
                            :required="paymentMethod === 'cash'"
                            :disabled="paymentMethod !== 'cash'"
                            min="{{ $remainingTotal }}"
                            step="1"
                            placeholder="Masukkan nominal uang diterima..."
                            class="w-full rounded-xl border border-surface-border bg-white px-3.5 py-2.5 text-base font-bold text-[#1b254b] focus:border-[#2b2d6e] focus:ring-2 focus:ring-[#2b2d6e]/20 focus:outline-none"
                        >
                    </div>

                    <!-- Tombol Nominal Cepat -->
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="cashReceived = remainingTotal"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-surface-border text-slate-700 hover:bg-slate-100 transition"
                        >
                            Uang Pas ({{ rupiah($remainingTotal) }})
                        </button>
                        <button
                            type="button"
                            @click="cashReceived = 50000"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-surface-border text-slate-700 hover:bg-slate-100 transition"
                        >
                            Rp 50.000
                        </button>
                        <button
                            type="button"
                            @click="cashReceived = 100000"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-surface-border text-slate-700 hover:bg-slate-100 transition"
                        >
                            Rp 100.000
                        </button>
                    </div>

                    <!-- Pratinjau Kembalian -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                        <span class="text-sm font-medium text-slate-600">Pratinjau Kembalian</span>
                        <span
                            class="text-lg font-bold"
                            :class="fullChange >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                            x-text="fullChange >= 0 ? formatRupiah(fullChange) : 'Kurang ' + formatRupiah(Math.abs(fullChange))"
                        ></span>
                    </div>
                </div>

                <!-- Ringkasan & Tombol Submit Bayar Semua -->
                <div class="pt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-slate-100">
                    <div>
                        <p class="text-xs text-slate-500">Total yang akan dibayar</p>
                        <p class="text-xl font-bold text-[#1b254b]">{{ rupiah($remainingTotal) }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="paymentMethod === 'cash' && (!cashReceived || cashReceived < remainingTotal)"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-[#2b2d6e] hover:bg-[#1b254b] disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm shadow-xs transition"
                    >
                        <x-lucide-check-circle-2 class="w-4 h-4" />
                        <span>Proses Pelunasan</span>
                    </button>
                </div>
            </form>

            <!-- ==================== TAB 2: SPLIT BILL / PER ITEM ==================== -->
            <form
                x-show="tab === 'split'"
                style="display: none;"
                method="POST"
                action="{{ route('payments.pay-split', $transaction) }}"
                class="p-5 sm:p-6 space-y-5"
            >
                @csrf

                <!-- Checklist Item & Stepper Porsi -->
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <h2 class="text-sm font-bold text-[#1b254b]">Pilih Item & Jumlah Porsi</h2>
                        <span class="text-xs text-slate-500">Centang menu yang ingin dibayar</span>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="item in items" :key="item.detail_id">
                            <div
                                :class="item.selected ? 'border-[#2b2d6e] bg-[#2b2d6e]/5' : 'border-surface-border bg-white'"
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl border transition"
                            >
                                <label class="flex items-start gap-3 cursor-pointer flex-1">
                                    <input
                                        type="checkbox"
                                        x-model="item.selected"
                                        class="mt-1 rounded border-slate-300 text-[#2b2d6e] focus:ring-[#2b2d6e]"
                                    >
                                    <div>
                                        <p class="text-sm font-bold text-slate-800" x-text="item.name"></p>
                                        <p class="text-xs text-slate-500">
                                            <span x-text="formatRupiah(item.unit_price)"></span> / porsi
                                            &bull; Sisa belum dibayar: <span class="font-semibold text-slate-700" x-text="item.remaining_qty"></span>
                                        </p>
                                    </div>
                                </label>

                                <!-- Stepper Qty -->
                                <div class="flex items-center justify-between sm:justify-end gap-4">
                                    <div class="inline-flex items-center rounded-lg border border-surface-border bg-white">
                                        <button
                                            type="button"
                                            @click="decrementQty(item)"
                                            :disabled="!item.selected || item.qty <= 1"
                                            class="p-1.5 text-slate-600 hover:bg-slate-100 disabled:opacity-40 rounded-l-lg transition"
                                        >
                                            <x-lucide-minus class="w-3.5 h-3.5" />
                                        </button>
                                        <span class="px-3 text-sm font-bold text-slate-800" x-text="item.qty"></span>
                                        <button
                                            type="button"
                                            @click="incrementQty(item)"
                                            :disabled="!item.selected || item.qty >= item.remaining_qty"
                                            class="p-1.5 text-slate-600 hover:bg-slate-100 disabled:opacity-40 rounded-r-lg transition"
                                        >
                                            <x-lucide-plus class="w-3.5 h-3.5" />
                                        </button>
                                    </div>

                                    <div class="w-28 text-right">
                                        <span
                                            class="text-sm font-bold"
                                            :class="item.selected ? 'text-[#1b254b]' : 'text-slate-400'"
                                            x-text="formatRupiah(item.selected ? item.unit_price * item.qty : 0)"
                                        ></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Hidden Inputs hanya untuk item yang dicentang agar index array berurutan -->
                    <template x-for="(sel, idx) in selectedItems" :key="'hidden-' + sel.detail_id">
                        <div>
                            <input type="hidden" :name="`items[${idx}][detail_id]`" :value="sel.detail_id">
                            <input type="hidden" :name="`items[${idx}][qty]`" :value="sel.qty">
                        </div>
                    </template>
                </div>

                <!-- Nama Pembayar Split Bill -->
                <div>
                    <label for="split_payer_name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Nama Pembayar (Split Bill) <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input
                        id="split_payer_name"
                        type="text"
                        name="payer_name"
                        x-model="splitPayerName"
                        maxlength="100"
                        placeholder="Contoh: Budi / Pembayar 1"
                        class="w-full rounded-xl border border-surface-border px-3.5 py-2.5 text-sm focus:border-[#2b2d6e] focus:ring-2 focus:ring-[#2b2d6e]/20 focus:outline-none"
                    >
                </div>

                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            :class="splitMethod === 'cash' ? 'border-[#2b2d6e] bg-[#2b2d6e]/5 text-[#1b254b]' : 'border-surface-border bg-white text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition font-semibold text-sm"
                        >
                            <input type="radio" name="payment_method" value="cash" x-model="splitMethod" class="sr-only">
                            <x-lucide-wallet class="w-5 h-5 text-[#2b2d6e]" />
                            <span>Tunai (Cash)</span>
                        </label>

                        <label
                            :class="splitMethod === 'qris' ? 'border-[#2b2d6e] bg-[#2b2d6e]/5 text-[#1b254b]' : 'border-surface-border bg-white text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition font-semibold text-sm"
                        >
                            <input type="radio" name="payment_method" value="qris" x-model="splitMethod" class="sr-only">
                            <x-lucide-qr-code class="w-5 h-5 text-[#2b2d6e]" />
                            <span>QRIS</span>
                        </label>
                    </div>
                </div>

                <!-- Input Uang Tunai & Kembalian (Khusus Split Cash) -->
                <div x-show="splitMethod === 'cash'" class="space-y-3 rounded-xl bg-slate-50 border border-surface-border p-4">
                    <div>
                        <label for="split_cash_received" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Uang Tunai Diterima (Rp)
                        </label>
                        <input
                            id="split_cash_received"
                            type="number"
                            name="cash_received"
                            x-model.number="splitCashReceived"
                            :required="splitMethod === 'cash'"
                            :disabled="splitMethod !== 'cash'"
                            :min="splitTotal"
                            step="1"
                            placeholder="Masukkan nominal uang diterima..."
                            class="w-full rounded-xl border border-surface-border bg-white px-3.5 py-2.5 text-base font-bold text-[#1b254b] focus:border-[#2b2d6e] focus:ring-2 focus:ring-[#2b2d6e]/20 focus:outline-none"
                        >
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="splitCashReceived = splitTotal"
                            :disabled="splitTotal <= 0"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-surface-border text-slate-700 hover:bg-slate-100 disabled:opacity-40 transition"
                        >
                            Uang Pas (<span x-text="formatRupiah(splitTotal)"></span>)
                        </button>
                        <button
                            type="button"
                            @click="splitCashReceived = 50000"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-surface-border text-slate-700 hover:bg-slate-100 transition"
                        >
                            Rp 50.000
                        </button>
                        <button
                            type="button"
                            @click="splitCashReceived = 100000"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-surface-border text-slate-700 hover:bg-slate-100 transition"
                        >
                            Rp 100.000
                        </button>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                        <span class="text-sm font-medium text-slate-600">Pratinjau Kembalian</span>
                        <span
                            class="text-lg font-bold"
                            :class="splitChange >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                            x-text="splitChange >= 0 ? formatRupiah(splitChange) : 'Kurang ' + formatRupiah(Math.abs(splitChange))"
                        ></span>
                    </div>
                </div>

                <!-- Ringkasan & Tombol Submit Split Bill -->
                <div class="pt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-slate-100">
                    <div>
                        <p class="text-xs text-slate-500">
                            Subtotal item terpilih (<span x-text="selectedItems.length"></span> jenis menu)
                        </p>
                        <p class="text-xl font-bold text-[#1b254b]" x-text="formatRupiah(splitTotal)"></p>
                    </div>

                    <button
                        type="submit"
                        :disabled="selectedItems.length === 0 || (splitMethod === 'cash' && (!splitCashReceived || splitCashReceived < splitTotal))"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-[#2b2d6e] hover:bg-[#1b254b] disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm shadow-xs transition"
                    >
                        <x-lucide-check-circle-2 class="w-4 h-4" />
                        <span>Bayar Item Terpilih</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Kolom Riwayat Pembayaran Sebelumnya pada Nota Ini -->
        <div class="bg-white rounded-2xl border border-surface-border p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-[#1b254b]">Riwayat Pembayaran Nota</h2>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                    {{ $payments->count() }}x bayar
                </span>
            </div>

            @if ($payments->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-200 p-6 text-center">
                    <p class="text-sm font-medium text-slate-500">Belum ada pembayaran tercatat</p>
                    <p class="text-xs text-slate-400 mt-1">Pilih tab Bayar Semua atau Split Bill untuk memproses.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($payments as $payment)
                        <div class="rounded-xl border border-surface-border p-3.5 space-y-2 bg-slate-50/50">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase px-2 py-0.5 rounded-md bg-[#2b2d6e]/10 text-[#2b2d6e]">
                                    {{ strtoupper($payment->payment_method) }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    {{ $payment->created_at?->format('d/m H:i') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-700">
                                    {{ $payment->payer_name ?: $transaction->customer_name }}
                                </span>
                                <span class="text-sm font-bold text-emerald-600">
                                    {{ rupiah($payment->amount_paid) }}
                                </span>
                            </div>

                            <div class="pt-1 flex justify-end">
                                <a
                                    href="{{ route('payments.receipt', $payment) }}"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2b2d6e] hover:underline"
                                >
                                    <x-lucide-printer class="w-3.5 h-3.5" />
                                    <span>Lihat Struk</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function checkoutHandler(config) {
        return {
            tab: config.hasSplitError ? 'split' : 'full',
            remainingTotal: Number(config.remainingTotal || 0),
            items: config.items || [],

            // State Tab Bayar Semua
            payerName: config.defaultPayer || '',
            paymentMethod: config.oldMethod || 'cash',
            cashReceived: config.oldCashReceived !== '' ? Number(config.oldCashReceived) : '',

            // State Tab Split Bill
            splitPayerName: '',
            splitMethod: 'cash',
            splitCashReceived: '',

            get fullChange() {
                if (this.cashReceived === '' || this.cashReceived === null) return -this.remainingTotal;
                return Number(this.cashReceived) - this.remainingTotal;
            },

            get selectedItems() {
                return this.items.filter(item => item.selected);
            },

            get splitTotal() {
                return this.selectedItems.reduce((sum, item) => sum + (item.unit_price * item.qty), 0);
            },

            get splitChange() {
                if (this.splitCashReceived === '' || this.splitCashReceived === null) return -this.splitTotal;
                return Number(this.splitCashReceived) - this.splitTotal;
            },

            incrementQty(item) {
                if (item.qty < item.remaining_qty) {
                    item.qty++;
                }
            },

            decrementQty(item) {
                if (item.qty > 1) {
                    item.qty--;
                }
            },

            formatRupiah(amount) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(0, Number(amount || 0)));
            }
        };
    }
</script>
@endpush