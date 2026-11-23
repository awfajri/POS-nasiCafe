@extends('layouts.app')

@section('title', 'Pesan Menu - NasiCafe')

@section('content')
<div x-data="customerOrderApp()" class="space-y-6 pb-36 sm:pb-40">
    <!-- Header Sapaan & Penjelasan Singkat -->
    <div class="bg-gradient-to-r from-[#1b254b] to-[#2b2d6e] rounded-3xl p-5 sm:p-7 text-white shadow-md relative overflow-hidden">
        <div class="relative z-10 max-w-xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur-xs text-xs font-semibold text-white mb-2.5">
                <x-lucide-utensils class="w-3.5 h-3.5" />
                <span>Self-Order Digital NasiCafe</span>
            </span>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Halo, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-xs sm:text-sm text-slate-200 mt-1 leading-relaxed">
                Pilih menu favoritmu langsung dari meja, lakukan pembayaran via QRIS, dan pesananmu segera disiapkan kasir.
            </p>
        </div>

        <!-- Dekorasi Visual di Background -->
        <div class="absolute -right-6 -bottom-8 w-36 h-36 rounded-full bg-white/5 pointer-events-none"></div>
    </div>

    <!-- Filter & Pencarian Menu Cepat -->
    <div class="space-y-3">
        <!-- Search Bar -->
        <div class="relative">
            <x-lucide-search class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
            <input
                type="text"
                x-model="search"
                placeholder="Cari menu favoritmu (contoh: Nasi Ayam, Es Jeruk)..."
                class="w-full pl-10 pr-10 py-3 rounded-2xl bg-white border border-surface-border focus:border-[#2b2d6e] focus:ring-2 focus:ring-[#2b2d6e]/20 text-sm placeholder:text-slate-400 shadow-xs transition"
            >
            <button
                x-show="search.length > 0"
                @click="search = ''"
                type="button"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1"
                style="display: none;"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <!-- Horizontal Scrollable Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar" aria-label="Kategori Menu">
            <button
                type="button"
                @click="selectedCategory = 'all'"
                :class="selectedCategory === 'all'
                    ? 'bg-[#2b2d6e] text-white shadow-xs'
                    : 'bg-white border border-surface-border text-slate-600 hover:bg-slate-50'"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition shrink-0 whitespace-nowrap"
            >
                Semua (<span x-text="menus.length"></span>)
            </button>

            @foreach ($categories as $category)
                <button
                    type="button"
                    @click="selectedCategory = @json((string) $category->id)"
                    :class="selectedCategory === @json((string) $category->id)
                        ? 'bg-[#2b2d6e] text-white shadow-xs'
                        : 'bg-white border border-surface-border text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition shrink-0 whitespace-nowrap"
                >
                    {{ $category->name }}
                    <span class="text-xs opacity-75">({{ $category->menus_count }})</span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Katalog Menu (Mobile First Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        <template x-for="menu in filteredMenus" :key="menu.id">
            <div
                :class="{
                    'opacity-60 grayscale-[35%] bg-slate-50': menu.stock <= 0,
                    'ring-2 ring-[#2b2d6e] border-[#2b2d6e]': getItemQty(menu.id) > 0 && menu.stock > 0
                }"
                class="bg-white rounded-2xl border border-surface-border p-4 flex flex-col justify-between shadow-xs transition hover:shadow-sm"
            >
                <!-- Foto & Status Stok Menu -->
                <div class="relative w-full aspect-[4/3] rounded-xl overflow-hidden bg-slate-100 mb-3">
                    <img
                        :src="menu.image_url"
                        :alt="menu.name"
                        class="w-full h-full object-cover"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='{{ asset('images/menu-placeholder.png') }}';"
                    >

                    <!-- Kategori Menu Badge -->
                    <span
                        class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-lg bg-black/60 backdrop-blur-xs text-[11px] font-medium text-white max-w-[120px] truncate"
                        x-text="menu.category_name"
                    ></span>

                    <!-- Badge Status Stok -->
                    <template x-if="menu.stock <= 0">
                        <span class="absolute top-2.5 right-2.5 px-2.5 py-1 rounded-lg bg-[#c5221f] text-white text-xs font-bold shadow-xs">
                            Habis
                        </span>
                    </template>
                    <template x-if="menu.stock > 0 && menu.stock <= menu.min_stock">
                        <span class="absolute top-2.5 right-2.5 px-2.5 py-1 rounded-lg bg-amber-500 text-white text-xs font-bold shadow-xs">
                            Sisa <span x-text="menu.stock"></span> porsi
                        </span>
                    </template>
                    <template x-if="menu.stock > menu.min_stock">
                        <span class="absolute top-2.5 right-2.5 px-2.5 py-1 rounded-lg bg-emerald-600/90 text-white text-[11px] font-semibold backdrop-blur-xs shadow-xs">
                            Tersedia
                        </span>
                    </template>
                </div>

                <!-- Info Menu & Tombol Tambah / Stepper -->
                <div class="flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-base text-slate-800 leading-snug line-clamp-2" x-text="menu.name"></h3>
                    </div>

                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-xs text-slate-400 block leading-tight">Harga</span>
                            <span class="font-extrabold text-base text-[#1b254b] tabular-nums leading-tight" x-text="formatRupiah(menu.price)"></span>
                        </div>

                        <!-- Interaksi Keranjang -->
                        <div>
                            <!-- Kasus 1: Menu Habis (Disabled) -->
                            <template x-if="menu.stock <= 0">
                                <span class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed">
                                    Habis
                                </span>
                            </template>

                            <!-- Kasus 2: Menu Belum Ada di Keranjang -->
                            <template x-if="menu.stock > 0 && getItemQty(menu.id) === 0">
                                <button
                                    type="button"
                                    @click="addToCart(menu)"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#2b2d6e] hover:bg-[#1b254b] text-white text-xs font-bold shadow-xs transition active:scale-95"
                                >
                                    <x-lucide-plus class="w-4 h-4" />
                                    <span>Tambah</span>
                                </button>
                            </template>

                            <!-- Kasus 3: Menu Sudah Ada di Keranjang (Inline Stepper) -->
                            <template x-if="menu.stock > 0 && getItemQty(menu.id) > 0">
                                <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 p-0.5">
                                    <button
                                        type="button"
                                        @click="decrementQtyByMenuId(menu.id)"
                                        class="w-7 h-7 rounded-lg bg-white hover:bg-slate-200 text-slate-700 flex items-center justify-center transition shadow-xs"
                                        title="Kurangi"
                                    >
                                        <x-lucide-minus class="w-3.5 h-3.5" />
                                    </button>

                                    <span class="w-8 text-center text-xs font-bold text-slate-800 tabular-nums" x-text="getItemQty(menu.id)"></span>

                                    <button
                                        type="button"
                                        @click="incrementQtyByMenuId(menu.id)"
                                        :disabled="getItemQty(menu.id) >= menu.stock"
                                        :class="getItemQty(menu.id) >= menu.stock
                                            ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400'
                                            : 'bg-white hover:bg-slate-200 text-slate-700 shadow-xs'"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center transition"
                                        title="Tambah"
                                    >
                                        <x-lucide-plus class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State Menu Tidak Ditemukan -->
    <div
        x-show="filteredMenus.length === 0"
        class="bg-white rounded-3xl border border-surface-border p-12 text-center"
        style="display: none;"
    >
        <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
            <x-lucide-search class="w-7 h-7" />
        </div>
        <h3 class="font-bold text-slate-800 text-base">Menu Tidak Ditemukan</h3>
        <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
            Tidak ada menu yang sesuai dengan kata kunci atau kategori yang Anda pilih.
        </p>
        <button
            type="button"
            @click="search = ''; selectedCategory = 'all'"
            class="mt-4 px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
        >
            Lihat Semua Menu
        </button>
    </div>

    <!-- ================= FLOATING CART BAR (Di Atas Customer Bottom Nav) ================= -->
    <div
        x-show="cart.length > 0"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-8"
        class="fixed bottom-20 sm:bottom-22 left-4 right-4 max-w-xl mx-auto z-30"
        style="display: none;"
    >
        <div class="bg-[#1b254b] text-white rounded-2xl shadow-xl border border-slate-700/50 px-4 py-3 sm:px-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <span class="relative w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0">
                    <x-lucide-shopping-cart class="w-5 h-5" />
                    <span
                        class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-[#c5221f] text-white text-[11px] font-bold flex items-center justify-center shadow-xs"
                        x-text="cartTotalCount"
                    ></span>
                </span>
                <div class="truncate">
                    <p class="text-xs text-slate-300 leading-tight">
                        <span x-text="cartTotalCount"></span> item dipilih
                    </p>
                    <p class="text-sm sm:text-base font-extrabold text-white leading-tight tabular-nums" x-text="formatRupiah(cartSubtotal)"></p>
                </div>
            </div>

            <button
                type="button"
                @click="cartDrawerOpen = true"
                class="px-4 py-2.5 rounded-xl bg-white text-[#1b254b] font-bold text-xs sm:text-sm hover:bg-slate-100 transition flex items-center gap-1.5 shrink-0 shadow-xs"
            >
                <span>Lihat Pesanan</span>
                <x-lucide-arrow-right class="w-4 h-4" />
            </button>
        </div>
    </div>

    <!-- ================= BOTTOM SHEET / MODAL DRAWER RINGKASAN PESANAN ================= -->
    <div
        x-show="cartDrawerOpen"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop Blur Overlay -->
        <div
            x-show="cartDrawerOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="cartDrawerOpen = false"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        ></div>

        <!-- Modal Drawer Container -->
        <div class="min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4 text-center">
            <div
                x-show="cartDrawerOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-12 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-12 sm:translate-y-0 sm:scale-95"
                class="relative bg-white rounded-t-3xl sm:rounded-3xl text-left shadow-2xl transform transition-all w-full max-w-lg p-5 sm:p-6 flex flex-col max-h-[88vh]"
            >
                <!-- Mobile Handle Bar -->
                <div class="w-12 h-1 bg-slate-300 rounded-full mx-auto mb-3 sm:hidden"></div>

                <!-- Header Drawer -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-[#1b254b] text-white flex items-center justify-center shadow-xs">
                            <x-lucide-shopping-bag class="w-5 h-5" />
                        </span>
                        <div>
                            <h2 class="font-bold text-base text-slate-800 leading-tight">Keranjang Pesanan</h2>
                            <p class="text-xs text-slate-400 leading-tight">Periksa pesanan Anda sebelum checkout</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="cartDrawerOpen = false"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
                    >
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>

                <!-- Form Order Self-Order Pelanggan -->
                <form
                    x-ref="customerOrderForm"
                    method="POST"
                    action="{{ route('customer.order.store') }}"
                    @submit="isSubmitting = true"
                    class="mt-4 flex flex-col flex-1 overflow-hidden space-y-4"
                >
                    @csrf

                    <!-- Input Nomor Meja (Opsional, Max 10 Karakter Sesuai Kontrak) -->
                    <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80">
                        <label for="table_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Nomor Meja <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input
                            id="table_number"
                            name="table_number"
                            type="text"
                            x-model="tableNumber"
                            maxlength="10"
                            placeholder="contoh: 07 / Meja 12"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-[#2b2d6e] focus:ring-2 focus:ring-[#2b2d6e]/20 text-sm font-medium placeholder:text-slate-400 transition"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">
                            Tuliskan nomor meja tempat Anda duduk jika makan di tempat (maksimal 10 karakter).
                        </p>
                        @error('table_number')
                            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Hidden Inputs: Items Keranjang (Indeks Berurutan Tanpa Field Harga) -->
                    <template x-for="(item, index) in cart" :key="item.id">
                        <div>
                            <input type="hidden" :name="'items[' + index + '][menu_id]'" :value="item.id">
                            <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                        </div>
                    </template>

                    @error('items')
                        <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-medium">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Daftar Item yang Dipesan (Scrollable) -->
                    <div class="flex-1 overflow-y-auto space-y-2.5 pr-1 divide-y divide-slate-100 max-h-[260px]">
                        <template x-for="item in cart" :key="item.id">
                            <div class="pt-2.5 first:pt-0 flex items-center justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-slate-800 truncate" x-text="item.name"></h4>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-slate-500 tabular-nums" x-text="formatRupiah(item.price)"></span>
                                        <span class="text-xs font-extrabold text-[#1b254b] tabular-nums" x-text="'= ' + formatRupiah(item.price * item.quantity)"></span>
                                    </div>
                                    <template x-if="item.quantity >= item.stock">
                                        <p class="text-[10px] text-amber-600 font-semibold mt-0.5">Maks. stok tersedia: <span x-text="item.stock"></span></p>
                                    </template>
                                </div>

                                <!-- Stepper Kuantitas & Tombol Hapus -->
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 p-0.5">
                                        <button
                                            type="button"
                                            @click="decrementQty(item)"
                                            class="w-7 h-7 rounded-lg bg-white hover:bg-slate-200 text-slate-700 flex items-center justify-center transition shadow-xs"
                                        >
                                            <x-lucide-minus class="w-3.5 h-3.5" />
                                        </button>

                                        <span class="w-8 text-center text-xs font-bold text-slate-800 tabular-nums" x-text="item.quantity"></span>

                                        <button
                                            type="button"
                                            @click="incrementQty(item)"
                                            :disabled="item.quantity >= item.stock"
                                            :class="item.quantity >= item.stock
                                                ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400'
                                                : 'bg-white hover:bg-slate-200 text-slate-700 shadow-xs'"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center transition"
                                        >
                                            <x-lucide-plus class="w-3.5 h-3.5" />
                                        </button>
                                    </div>

                                    <button
                                        type="button"
                                        @click="removeFromCart(item)"
                                        class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition"
                                        title="Hapus item"
                                    >
                                        <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Informasi Pembayaran & Total -->
                    <div class="border-t border-slate-100 pt-3 space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>Total Item</span>
                            <span class="font-bold text-slate-800 tabular-nums" x-text="cartTotalCount + ' porsi'"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm sm:text-base">
                            <span class="font-bold text-slate-800">Estimasi Total Bayar</span>
                            <span class="text-xl font-extrabold text-[#1b254b] tabular-nums" x-text="formatRupiah(cartSubtotal)"></span>
                        </div>

                        <!-- Petunjuk Pembayaran -->
                        <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-800 flex items-start gap-2">
                            <x-lucide-alert-circle class="w-4 h-4 shrink-0 mt-0.5 text-amber-600" />
                            <span>Setelah klik Pesan, Anda akan diarahkan ke dasbor pesanan untuk memindai QRIS dan konfirmasi via WhatsApp ke kasir.</span>
                        </div>
                    </div>

                    <!-- Tombol Konfirmasi Pesan -->
                    <div class="pt-1">
                        <button
                            type="submit"
                            :disabled="cart.length === 0 || isSubmitting"
                            :class="cart.length === 0 || isSubmitting
                                ? 'opacity-50 cursor-not-allowed bg-slate-300'
                                : 'bg-[#2b2d6e] hover:bg-[#1b254b] shadow-md active:scale-[0.99]'"
                            class="w-full py-3.5 px-4 rounded-xl text-white font-bold text-sm sm:text-base flex items-center justify-center gap-2 transition"
                        >
                            <span x-show="!isSubmitting"><x-lucide-check class="w-5 h-5 inline" /></span>
                            <span x-show="!isSubmitting">Pesan Sekarang</span>
                            <span x-show="isSubmitting" style="display: none;" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Membuat Pesanan...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $menusPayload = $menus->map(fn ($menu) => [
        'id' => $menu->id,
        'name' => $menu->name,
        'price' => (int) $menu->price,
        'stock' => (int) $menu->stock,
        'min_stock' => (int) $menu->min_stock,
        'category_id' => (string) $menu->category_id,
        'category_name' => $menu->category?->name ?? 'Lainnya',
        'image_url' => $menu->image_url,
    ])->values();
@endphp
<script>
    function customerOrderApp() {
        return {
            menus: @json($menusPayload),
            search: '',
            selectedCategory: 'all',
            cart: [],
            tableNumber: @json(old('table_number', '')),
            cartDrawerOpen: false,
            isSubmitting: false,
            oldItems: @json(old('items', [])),
            hasValidationErrors: @json($errors->any()),
            init() {
                if (Array.isArray(this.oldItems) && this.oldItems.length > 0) {
                    this.oldItems.forEach(oldItem => {
                        const menu = this.menus.find(m => m.id === Number(oldItem.menu_id));
                        if (menu && menu.stock > 0) {
                            this.cart.push({
                                id: menu.id,
                                name: menu.name,
                                price: menu.price,
                                stock: menu.stock,
                                quantity: Math.min(Number(oldItem.quantity) || 1, menu.stock),
                                image_url: menu.image_url,
                            });
                        }
                    });
                }

                if (this.hasValidationErrors && this.cart.length > 0) {
                    this.cartDrawerOpen = true;
                }
            },

            get filteredMenus() {
                let list = this.menus;
                if (this.selectedCategory !== 'all') {
                    list = list.filter(m => m.category_id === this.selectedCategory);
                }
                if (this.search.trim() !== '') {
                    const q = this.search.toLowerCase().trim();
                    list = list.filter(m => m.name.toLowerCase().includes(q));
                }
                return list;
            },

            get cartTotalCount() {
                return this.cart.reduce((sum, item) => sum + item.quantity, 0);
            },

            get cartSubtotal() {
                return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },

            formatRupiah(amount) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
            },

            getItemQty(menuId) {
                const item = this.cart.find(i => i.id === menuId);
                return item ? item.quantity : 0;
            },

            addToCart(menu) {
                if (menu.stock <= 0) return;
                const existing = this.cart.find(i => i.id === menu.id);
                if (existing) {
                    if (existing.quantity < menu.stock) {
                        existing.quantity++;
                    }
                } else {
                    this.cart.push({
                        id: menu.id,
                        name: menu.name,
                        price: menu.price,
                        stock: menu.stock,
                        quantity: 1,
                        image_url: menu.image_url,
                    });
                }
            },

            incrementQty(item) {
                if (item.quantity < item.stock) {
                    item.quantity++;
                }
            },

            incrementQtyByMenuId(menuId) {
                const item = this.cart.find(i => i.id === menuId);
                if (item && item.quantity < item.stock) {
                    item.quantity++;
                }
            },

            decrementQty(item) {
                if (item.quantity > 1) {
                    item.quantity--;
                } else {
                    this.removeFromCart(item);
                }
            },

            decrementQtyByMenuId(menuId) {
                const item = this.cart.find(i => i.id === menuId);
                if (item) {
                    this.decrementQty(item);
                }
            },

            removeFromCart(item) {
                this.cart = this.cart.filter(i => i.id !== item.id);
                if (this.cart.length === 0) {
                    this.cartDrawerOpen = false;
                }
            }
        };
    }
</script>
@endpush