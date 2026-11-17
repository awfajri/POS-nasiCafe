@if ($shouldDisplay && $lowStockMenus->isNotEmpty())
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="mb-6 rounded-2xl border border-amber-300 bg-amber-50/95 p-4 shadow-sm"
        role="alert"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="rounded-xl bg-amber-500/15 p-2 text-amber-700 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-amber-900">
                            Peringatan Stok Menipis
                        </h3>
                        <span class="rounded-full bg-amber-200/80 px-2 py-0.5 text-xs font-bold text-amber-900">
                            {{ $count }} menu
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs text-amber-800">
                        Menu berikut telah mencapai atau berada di bawah batas minimum stok:
                    </p>
                    <div class="mt-2.5 flex flex-wrap gap-2">
                        @foreach ($lowStockMenus->take(6) as $menu)
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/90 px-2.5 py-1 text-xs font-medium text-amber-950 border border-amber-200 shadow-2xs">
                                <span>{{ $menu->name }}</span>
                                <span class="font-bold text-amber-700">({{ $menu->stock }} tersisa)</span>
                            </span>
                        @endforeach
                        @if ($count > 6)
                            <span class="inline-flex items-center rounded-lg bg-amber-200/60 px-2 py-1 text-xs font-medium text-amber-900">
                                +{{ $count - 6 }} lainnya
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <button
                @click="show = false"
                type="button"
                class="rounded-xl p-1.5 text-amber-700 hover:bg-amber-200/50 hover:text-amber-900 transition focus:outline-none"
                aria-label="Tutup peringatan stok"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
@endif
