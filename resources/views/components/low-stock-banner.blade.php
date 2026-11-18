@if ($shouldDisplay && $lowStockMenus->isNotEmpty())
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="mb-6 rounded-2xl border border-amber-300 bg-amber-50/95 p-4 shadow-xs"
        role="alert"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="rounded-xl bg-amber-500/15 p-2 text-amber-700 shrink-0">
                    <x-lucide-alert-triangle class="w-5 h-5 text-amber-700" />
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm font-semibold text-amber-900">
                            Peringatan Stok Menipis
                        </h3>
                        <span class="rounded-full bg-amber-200/80 px-2 py-0.5 text-xs font-bold text-amber-900">
                            {{ $count }} menu
                        </span>

                        @if (auth()->user()?->isOwner())
                            <a
                                href="{{ \Illuminate\Support\Facades\Route::has('owner.menus.index') ? route('owner.menus.index') : '#' }}"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-600 text-white hover:bg-amber-700 transition"
                            >
                                <span>Cek Stok Menu</span>
                                <x-lucide-arrow-right class="w-3.5 h-3.5" />
                            </a>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-amber-800">
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
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>
    </div>
@endif
