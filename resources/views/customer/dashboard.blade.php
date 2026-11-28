@extends('layouts.app')

@section('title', 'Dashboard Pelanggan')

@section('content')
    @php
        $steps = ['pending' => 'Pending', 'processing' => 'Diproses', 'completed' => 'Selesai'];
    @endphp

    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-ink-primary">Halo, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-ink-muted">Pantau status pesanan Anda di sini.</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-stat-card title="Pesanan Aktif" :value="$active->count()" />
            <x-stat-card title="Total Transaksi" :value="$totalTransactions" />
        </div>

        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-ink-primary">Pesanan Aktif</h2>

            @forelse ($active as $order)
                <article class="space-y-4 rounded-2xl bg-surface-card p-5 ring-1 ring-surface-border">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-xs text-ink-muted">Kode nota</p>
                            <p class="font-semibold text-ink-primary">{{ $order->invoice_code }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-status-badge :status="$order->status" />
                            <x-payment-badge :status="$order->payment_status" />
                        </div>
                    </div>

                    @php $currentIndex = array_search($order->status, array_keys($steps), true); @endphp
                    <ol class="flex items-start gap-2" aria-label="Status pesanan">
                        @foreach ($steps as $label)
                            @php $reached = $loop->index <= $currentIndex; @endphp
                            <li class="flex-1">
                                <div class="h-1.5 rounded-full {{ $reached ? 'bg-brand-600' : 'bg-surface-border' }}"></div>
                                <p class="mt-1.5 text-xs {{ $reached ? 'font-semibold text-brand-700' : 'text-ink-muted' }}">{{ $label }}</p>
                            </li>
                        @endforeach
                    </ol>

                    <ul class="divide-y divide-surface-border text-sm">
                        @foreach ($order->details as $detail)
                            <li class="flex items-center justify-between py-2">
                                <span class="text-ink-primary">{{ $detail->menu->name }} <span class="text-ink-muted">x{{ $detail->quantity }}</span></span>
                                <span class="text-ink-muted">{{ rupiah($detail->subtotal) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="flex items-center justify-between border-t border-surface-border pt-3">
                        <span class="text-sm text-ink-muted">Total</span>
                        <span class="text-lg font-semibold text-ink-primary">{{ rupiah($order->total_amount) }}</span>
                    </div>

                    @if ($order->status === 'pending' && $order->payment_status === 'unpaid')
                        @php
                            $waText = "Halo kasir NasiCafe, saya sudah membayar pesanan {$order->invoice_code} sebesar ".rupiah($order->total_amount).'.';
                            $waUrl = 'https://wa.me/'.$waNumber.'?text='.urlencode($waText);
                        @endphp
                        <div class="space-y-4 rounded-xl bg-surface-light p-4 text-center ring-1 ring-surface-border">
                            <div class="flex items-center justify-center gap-2 text-sm font-semibold text-ink-primary">
                                <x-lucide-qr-code class="size-4" />
                                Bayar dengan QRIS
                            </div>

                            <img src="{{ asset(config('nasicafe.qris_image')) }}" alt="Kode QRIS NasiCafe"
                                class="mx-auto size-48 rounded-lg bg-white object-contain p-2 ring-1 ring-surface-border">
                            
                            <p class="text-sm text-ink-muted">
                                Bayar sebesar <span class="font-semibold text-ink-primary">{{ rupiah($order->total_amount) }}</span>,
                                lalu kabari kasir lewat WhatsApp.
                            </p>

                            @if ($order->expires_at)
                                <div x-data="{
                                        remaining: 0,
                                        init() {
                                            const end = new Date('{{ $order->expires_at->toIso8601String() }}').getTime();
                                            const tick = () => { this.remaining = Math.max(0, Math.floor((end - Date.now()) / 1000)); };
                                            tick();
                                            setInterval(tick, 1000);
                                        },
                                        get label() {
                                            const m = Math.floor(this.remaining / 60), s = this.remaining % 60;
                                            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                                        }
                                    }"
                                    class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm ring-1 ring-surface-border">
                                    <x-lucide-clock class="size-4 text-ink-muted" />
                                    <span x-show="remaining > 0">Batas bayar <span class="font-semibold tabular-nums text-ink-primary" x-text="label"></span></span>
                                    <span x-show="remaining === 0" style="display: none" class="font-semibold text-status-danger">Waktu habis, muat ulang halaman</span>
                                </div>
                            @endif
                            <div>
                                <a href="{{ asset(config('nasicafe.qris_image')) }}"
                                    download="QRIS-NasiCafe.png"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-ink-primary ring-1 ring-surface-border transition hover:bg-surface-card focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2">
                                    <x-lucide-download class="size-4" />
                                    Download QRIS
                                </a>
                            </div>
                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2">
                                <x-lucide-message-circle class="size-4" />
                                Konfirmasi lewat WhatsApp
                            </a>
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl bg-surface-card px-5 py-10 text-center ring-1 ring-surface-border">
                    <p class="text-ink-muted">Belum ada pesanan aktif.</p>
                    @if (\Illuminate\Support\Facades\Route::has('customer.order'))
                        <a href="{{ route('customer.order') }}"
                            class="mt-4 inline-flex rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                            Pesan sekarang
                        </a>
                    @endif
                </div>
            @endforelse
        </section>

        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-ink-primary">Riwayat Pesanan</h2>

            <div class="space-y-3 md:hidden">
                @forelse ($history as $order)
                    <div class="space-y-2.5 rounded-2xl bg-surface-card p-4 ring-1 ring-surface-border">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-semibold text-ink-primary">{{ $order->invoice_code }}</p>
                                <p class="text-xs text-ink-muted">{{ $order->created_at->translatedFormat('d M Y H:i') }}</p>
                            </div>
                            <p class="font-semibold text-ink-primary">{{ rupiah($order->total_amount) }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <x-status-badge :status="$order->status" />
                            <x-payment-badge :status="$order->payment_status" />
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl bg-surface-card px-4 py-10 text-center text-sm text-ink-muted ring-1 ring-surface-border">
                        Belum ada riwayat pesanan.
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-hidden rounded-2xl bg-surface-card ring-1 ring-surface-border md:block">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-surface-border text-sm">
                        <thead class="bg-surface-light text-left text-xs font-semibold uppercase tracking-wide text-ink-muted">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-3">Kode nota</th>
                                <th class="whitespace-nowrap px-4 py-3">Tanggal</th>
                                <th class="whitespace-nowrap px-4 py-3 text-right">Total</th>
                                <th class="whitespace-nowrap px-4 py-3">Status</th>
                                <th class="whitespace-nowrap px-4 py-3">Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-border">
                            @forelse ($history as $order)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-ink-primary">{{ $order->invoice_code }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-ink-muted">{{ $order->created_at->translatedFormat('d M Y H:i') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-ink-primary">{{ rupiah($order->total_amount) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3"><x-status-badge :status="$order->status" /></td>
                                    <td class="whitespace-nowrap px-4 py-3"><x-payment-badge :status="$order->payment_status" /></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-ink-muted">Belum ada riwayat pesanan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $history->links() }}
        </section>
    </div>
@endsection