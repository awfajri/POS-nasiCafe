@extends('layouts.app')

@section('title', 'Kasir POS - NasiCafe')

@section('content')
<div x-data="posApp()" class="space-y-6">
    <!-- Header Ringkas Layar POS -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#1b254b]">Kasir POS</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola pesanan langsung dari meja kasir dengan cepat dan akurat.</p>
        </div>
        <div class="flex items-center gap-2 text-xs font-medium text-slate-500 bg-white border border-surface-border px-3.5 py-2 rounded-xl shadow-xs shrink-0">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Kasir: <strong class="text-slate-800">{{ auth()->user()->name }}</strong></span>
        </div>
    </div>

    <!-- Layout Split-Screen POS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- ================= PANEL KIRI: KATALOG MENU (lg:col-span-7 xl:col-span-8) ================= -->
        <div class="lg:col-span-7 xl:col-span-8 space-y-4">
            <!-- Bar Pencarian & Filter Kategori -->
            <div class="bg-white rounded-2xl border border-surface-border p-4 shadow-xs space-y-3">
                <!-- Search Input -->
                <div class="relative">
                    <x-lucide-search class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Cari nama menu (contoh: Nasi Goreng, Es Teh)..."
                        class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 focus:border-[#2b2d6e] focus:ring-2 focus:ring-[#2b2d6e]/20 text-sm placeholder:text-slate-400 transition"
                    >
                    <button
                        x-show="search.length > 0"
                        @click="search = ''"
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1"
                        style="display: none;"
                    >
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </div>

                <!-- Category Pills (Horizontal Scrollable) -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar" aria-label="Filter Kategori Menu">
                    <button
                        type="button"
                        @click="selectedCategory = 'all'"
                        :class="selectedCategory === 'all'
                            ? 'bg-[#2b2d6e] text-white shadow-xs'
                            : 'bg-slate-100 hover:bg-slate-200 text-slate-600'"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition shrink-0 whitespace-nowrap"
                    >
                        Semua Menu (<span x-text="menus.length"></span>)
                    </button>

                    @foreach ($categories as $category)
                        <button
                            type="button"
                            @click="selectedCategory = @json((string) $category->id)"
                            :class="selectedCategory === @json((string) $category->id)
                                ? 'bg-[#2b2d6e] text-white shadow-xs'
                                : 'bg-slate-100 hover:bg-slate-200 text-slate-600'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition shrink-0 whitespace-nowrap"
                        >
                            {{ $category->name }}
                            <span class="text-xs opacity-80">({{ $category->menus_count }})</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Grid Menu Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="menu in filteredMenus" :key="menu.id">
                    <div
                        @click="menu.stock > 0 ? addToCart(menu) : null"
                        :class="{
                            'cursor-pointer hover:border-[#2b2d6e] hover:shadow-md': menu.stock > 0,
                            'opacity-60 grayscale-[35%] cursor-not-allowed bg-slate-50': menu.stock <= 0,
                            'ring-2 ring-[#2b2d6e] border-[#2b2d6e]': getItemQty(menu.id) > 0 && menu.stock > 0
                        }"
                        class="group bg-white rounded-2xl border border-surface-border p-3 flex flex-col justify-between transition relative overflow-hidden select-none"
                    >
                        <!-- Gambar Menu & Badge Status Stok -->
                        <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-slate-100 mb-2.5">
                            <img
                                :src="menu.image_url"
                                :alt="menu.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                loading="lazy"
                                onerror="this.onerror=null;this.src='{{ asset('images/menu-placeholder.png') }}';"
                            >

                            <!-- Badge Kategori di Pojok Kiri Atas -->
                            <span
                                class="absolute top-2 left-2 px-2 py-0.5 rounded-lg bg-black/60 backdrop-blur-xs text-[10px] font-medium text-white max-w-[100px] truncate"
                                x-text="menu.category_name"
                            ></span>

                            <!-- Badge Status Stok di Pojok Kanan Atas -->
                            <template x-if="menu.stock <= 0">
                                <span class="absolute top-2 right-2 px-2 py-0.5 rounded-lg bg-[#c5221f] text-white text-[11px] font-bold shadow-xs">
                                    Habis
                                </span>
                            </template>
                            <template x-if="menu.stock > 0 && menu.stock <= menu.min_stock">
                                <span class="absolute top-2 right-2 px-2 py-0.5 rounded-lg bg-amber-500 text-white text-[11px] font-bold shadow-xs">
                                    Sisa <span x-text="menu.stock"></span>
                                </span>
                            </template>
                            <template x-if="menu.stock > menu.min_stock">
                                <span class="absolute top-2 right-2 px-2 py-0.5 rounded-lg bg-emerald-600/90 text-white text-[11px] font-semibold backdrop-blur-xs shadow-xs">
                                    Stok <span x-text="menu.stock"></span>
                                </span>
                            </template>

                            <!-- Badge Jumlah di Keranjang (Bila Ada) -->
                            <template x-if="getItemQty(menu.id) > 0">
                                <div class="absolute inset-0 bg-[#2b2d6e]/20 flex items-center justify-center">
                                    <span class="bg-[#1b254b] text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-md flex items-center gap-1.5">
                                        <x-lucide-check class="w-3.5 h-3.5" />
                                        <span x-text="getItemQty(menu.id) + ' di keranjang'"></span>
                                    </span>
                                </div>
                            </template>
                        </div>

                        <!-- Info Teks Menu -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-sm text-slate-800 line-clamp-2 leading-snug group-hover:text-[#2b2d6e] transition" x-text="menu.name"></h3>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <span class="font-extrabold text-sm text-slate-900 tabular-nums" x-text="formatRupiah(menu.price)"></span>

                                <template x-if="menu.stock > 0">
                                    <button
                                        type="button"
                                        @click.stop="addToCart(menu)"
                                        class="w-7 h-7 rounded-lg bg-[#2b2d6e] hover:bg-[#1b254b] text-white flex items-center justify-center transition shadow-xs focus:outline-none"
                                        title="Tambah ke keranjang"
                                    >
                                        <x-lucide-plus class="w-4 h-4" />
                                    </button>
                                </template>
                                <template x-if="menu.stock <= 0">
                                    <span class="text-[11px] font-semibold text-rose-600">Kosong</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State Bila Menu Tidak Ditemukan -->
            <div
                x-show="filteredMenus.length === 0"
                class="bg-white rounded-2xl border border-surface-border p-12 text-center"
                style="display: none;"
            >
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <x-lucide-search class="w-7 h-7" />
                </div>
                <h3 class="font-bold text-slate-800 text-base">Menu tidak ditemukan</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                    Tidak ada menu yang sesuai dengan kata kunci pencarian atau filter kategori saat ini.
                </p>
                <button
                    type="button"
                    @click="search = ''; selectedCategory = 'all'"
                    class="mt-4 px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                >
                    Reset Filter
                </button>
            </div>
        </div>

        <!-- ================= PANEL KANAN: KERANJANG & CHECKOUT (lg:col-span-5 xl:col-span-4) ================= -->
        <div class="lg:col-span-5 xl:col-span-4 lg:sticky lg:top-4 space-y-4">
            <div class="bg-white rounded-2xl border border-surface-border shadow-xs p-5 flex flex-col">
                <!-- Header Keranjang -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-[#1b254b] text-white flex items-center justify-center shadow-xs">
                            <x-lucide-shopping-bag class="w-5 h-5" />
                        </span>
                        <div>
                            <h2 class="font-bold text-base text-slate-800 leading-tight">Keranjang Pesanan</h2>
                            <p class="text-xs text-slate-400 leading-tight">
                                <span x-text="cartTotalCount"></span> item dipilih
                            </p>
                        </div>
                    </div>

                    <button
                        x-show="cart.length > 0"
                        @click="clearCart()"
                        type="button"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline p-1"
                        style="display: none;"
                    >
                        Kosongkan
                    </button>
                </div>

                <!-- Form POS Kasir -->
                <form
                    x-ref="posForm"
                    method="POST"
                    action="{{ Route::has('pos.store') ? route('pos.store') : url('/pos/orders') }}"
                    class="mt-4 space-y-4 flex flex-col"
                >
                    @csrf

                    <!-- Input Nama Pelanggan (Wajib) & Nomor Meja (Opsional) -->
                    <div class="space-y-3 bg-slate-50/70 p-3.5 rounded-xl border border-slate-200/80">
                        <div>
                            <label for="customer_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Nama Pelanggan <span class="text-rose-500">*</span>
                            </label>
                            <input
                                id="customer_name"
                                name="customer_name"
                                type="text"
                                x-model="customerName"
                                required
                                maxlength="100"
                                placeholder="contoh: Budi Santoso / Meja 05"
                                class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 focus:border-[#2b2d6e] focus:ring-2 focus:ring-[#2b2d6e]/20 text-sm font-medium placeholder:text-slate-400 transition"
                            >
                            @error('customer_name')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="table_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Nomor Meja <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input
                                id="table_number"
                                name="table_number"
                                type="text"
                                x-model="tableNumber"
                                maxlength="20"
                                placeholder="contoh: 05 / Take Away"
                                class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 focus:border-[#2b2d6e] focus:ring-2 focus:ring-[#2b2d6e]/20 text-sm font-medium placeholder:text-slate-400 transition"
                            >
                            @error('table_number')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Hidden Inputs: Items Keranjang (Indeks Berurutan 0, 1, 2...) -->
                    <!-- DILARANG mengirim price/subtotal/total ke server -->
                    <template x-for="(item, index) in cart" :key="item.id">
                        <div>
                            <input type="hidden" :name="'items[' + index + '][menu_id]'" :value="item.id">
                            <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                        </div>
                    </template>

                    <!-- Hidden Input: redirect_to_payment -->
                    <input type="hidden" name="redirect_to_payment" x-model="redirectToPayment">

                    @error('items')
                        <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-medium">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Daftar Item Keranjang -->
                    <div class="border-t border-b border-slate-100 py-2 max-h-[320px] overflow-y-auto space-y-2.5 pr-1 divide-y divide-slate-100">
                        <!-- Tampilan Kosong -->
                        <div x-show="cart.length === 0" class="py-8 text-center">
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                                <x-lucide-shopping-cart class="w-6 h-6" />
                            </div>
                            <p class="text-sm font-bold text-slate-700">Keranjang masih kosong</p>
                            <p class="text-xs text-slate-400 mt-0.5">Pilih kartu menu di sebelah kiri untuk menambah pesanan.</p>
                        </div>

                        <!-- Item-item Keranjang -->
                        <template x-for="(item, index) in cart" :key="item.id">
                            <div class="pt-2.5 first:pt-0 flex items-center justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-slate-800 truncate" x-text="item.name"></h4>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-slate-500 tabular-nums" x-text="formatRupiah(item.price)"></span>
                                        <span class="text-xs font-extrabold text-[#1b254b] tabular-nums" x-text="'= ' + formatRupiah(item.price * item.quantity)"></span>
                                    </div>
                                    <template x-if="item.quantity >= item.stock">
                                        <p class="text-[10px] text-amber-600 font-semibold mt-0.5">Mencapai batas stok (<span x-text="item.stock"></span>)</p>
                                    </template>
                                </div>

                                <!-- Stepper Kuantitas & Tombol Hapus -->
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 p-0.5">
                                        <button
                                            type="button"
                                            @click="decrementQty(item)"
                                            class="w-7 h-7 rounded-lg bg-white hover:bg-slate-200 text-slate-700 flex items-center justify-center transition focus:outline-none shadow-xs"
                                            title="Kurangi kuantitas"
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
                                            class="w-7 h-7 rounded-lg flex items-center justify-center transition focus:outline-none"
                                            title="Tambah kuantitas"
                                        >
                                            <x-lucide-plus class="w-3.5 h-3.5" />
                                        </button>
                                    </div>

                                    <button
                                        type="button"
                                        @click="removeFromCart(item)"
                                        class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition"
                                        title="Hapus menu dari keranjang"
                                    >
                                        <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Ringkasan Total Biaya -->
                    <div class="space-y-1.5 pt-1">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>Total Item</span>
                            <span class="font-bold text-slate-700 tabular-nums" x-text="cartTotalCount + ' porsi'"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-bold text-slate-800">Estimasi Total</span>
                            <span class="text-xl font-extrabold text-[#1b254b] tabular-nums" x-text="formatRupiah(cartSubtotal)"></span>
                        </div>
                    </div>

                    <!-- Tombol Aksi Submit POS -->
                    <div class="space-y-2 pt-2">
                        <!-- Tombol 1: Simpan & Bayar (Langsung ke pembayaran) -->
                        <button
                            type="button"
                            @click="submitOrder(true)"
                            :disabled="cart.length === 0 || !customerName.trim() || isSubmitting"
                            :class="cart.length === 0 || !customerName.trim() || isSubmitting
                                ? 'opacity-50 cursor-not-allowed bg-slate-300'
                                : 'bg-[#2b2d6e] hover:bg-[#1b254b] shadow-xs active:scale-[0.99]'"
                            class="w-full py-3 px-4 rounded-xl text-white font-bold text-sm flex items-center justify-center gap-2 transition"
                        >
                            <span x-show="!isSubmitting"><x-lucide-credit-card class="w-4 h-4 shrink-0 inline" /></span>
                            <span x-show="!isSubmitting">Simpan &amp; Lanjut Bayar</span>
                            <span x-show="isSubmitting" style="display: none;" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Memproses...</span>
                            </span>
                        </button>

                        <!-- Tombol 2: Simpan Pesanan (Status pending/unpaid) -->
                        <button
                            type="button"
                            @click="submitOrder(false)"
                            :disabled="cart.length === 0 || !customerName.trim() || isSubmitting"
                            :class="cart.length === 0 || !customerName.trim() || isSubmitting
                                ? 'opacity-50 cursor-not-allowed bg-slate-100 text-slate-400'
                                : 'bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-[0.99]'"
                            class="w-full py-2.5 px-4 rounded-xl font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition"
                        >
                            <x-lucide-file-text class="w-4 h-4 shrink-0" />
                            <span>Simpan Saja (Bayar Nanti)</span>
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
    function posApp() {
        return {
            menus: @json($menusPayload),
            search: '',
            selectedCategory: 'all',
            cart: [],
            customerName: @json(old('customer_name', '')),
            tableNumber: @json(old('table_number', '')),
            redirectToPayment: '0',
            isSubmitting: false,
            oldItems: @json(old('items', [])),
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

            decrementQty(item) {
                if (item.quantity > 1) {
                    item.quantity--;
                } else {
                    this.removeFromCart(item);
                }
            },

            removeFromCart(item) {
                this.cart = this.cart.filter(i => i.id !== item.id);
            },

            clearCart() {
                if (confirm('Kosongkan semua pesanan di keranjang?')) {
                    this.cart = [];
                }
            },

            submitOrder(redirect) {
                if (this.cart.length === 0) {
                    alert('Keranjang masih kosong. Pilih menu terlebih dahulu.');
                    return;
                }
                if (!this.customerName.trim()) {
                    alert('Nama pelanggan wajib diisi.');
                    return;
                }
                this.redirectToPayment = redirect ? '1' : '0';
                this.isSubmitting = true;
                this.$nextTick(() => {
                    this.$refs.posForm.submit();
                });
            }
        };
    }
</script>
@endpush