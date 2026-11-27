@php
    $user = auth()->user();
    $isCashier = $user && $user->isCashier();
    $payments = $transaction->payments ?? collect();
    $canConfirmQris = $isCashier
        && $transaction->source === \App\Models\Transaction::SOURCE_SELF_ORDER
        && $transaction->status === \App\Models\Transaction::STATUS_PENDING
        && $transaction->payment_status === \App\Models\Transaction::PAYMENT_UNPAID;
@endphp

<div class="bg-white rounded-2xl border border-surface-border p-5 sm:p-6 shadow-xs space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-[#1b254b]">Pembayaran & Struk</h2>
            <p class="text-xs text-slate-500">
                Status pembayaran saat ini:
                <span class="font-semibold text-slate-700 uppercase">{{ $transaction->payment_status }}</span>
                ({{ rupiah($transaction->paid_amount) }} / {{ rupiah($transaction->total_amount) }})
            </p>
        </div>

        <!-- Tombol Aksi Khusus Kasir (Owner Read-Only) -->
        @if ($isCashier)
            <div class="flex flex-wrap items-center gap-2.5">
                @if ($canConfirmQris)
                    <form
                        method="POST"
                        action="{{ route('payments.confirm-qris', $transaction) }}"
                        onsubmit="return confirm('Konfirmasi bahwa pembayaran QRIS dari pelanggan sudah diterima?')"
                    >
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-xs transition"
                        >
                            <x-lucide-qr-code class="w-4 h-4" />
                            <span>Konfirmasi Lunas QRIS (WA)</span>
                        </button>
                    </form>
                @endif

                @if ($transaction->canBePaid())
                    <a
                        href="{{ route('payments.checkout', $transaction) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#2b2d6e] hover:bg-[#1b254b] text-white text-sm font-bold shadow-xs transition"
                    >
                        <x-lucide-credit-card class="w-4 h-4" />
                        <span>Proses Pembayaran / Split Bill</span>
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Tabel Daftar Sub-Pembayaran (Tampil untuk Owner & Kasir) -->
    @if ($payments->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 p-5 text-center text-sm text-slate-500">
            Belum ada riwayat pembayaran untuk transaksi ini.
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-surface-border">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Pembayar</th>
                        <th class="px-4 py-3">Metode</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3">Kasir</th>
                        <th class="px-4 py-3 text-right">Struk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($payments as $payment)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                {{ $payment->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                {{ $payment->payer_name ?: $transaction->customer_name }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold uppercase bg-[#2b2d6e]/10 text-[#2b2d6e]">
                                    {{ strtoupper($payment->payment_method) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-600 whitespace-nowrap">
                                {{ rupiah($payment->amount_paid) }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $payment->cashier?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a
                                    href="{{ route('payments.receipt', $payment) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-surface-border text-xs font-semibold text-[#1b254b] hover:bg-slate-100 transition"
                                >
                                    <x-lucide-printer class="w-3.5 h-3.5" />
                                    <span>Struk</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>