@extends('layouts.app')

@section('title', 'Menu & Stok')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Menu & Stok</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola daftar menu makanan, minuman, harga, ketersediaan, dan stok harian.</p>
        </div>
        <div>
            <a
                href="{{ route('owner.menus.create') }}"
                class="inline-flex items-center gap-2 bg-[#2b2d6e] hover:bg-[#202253] text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-xs transition"
            >
                <x-lucide-plus class="w-4 h-4 shrink-0" />
                <span>Tambah Menu</span>
            </a>
        </div>
    </div>

    {{-- Baris Filter Atas --}}
    <div class="bg-white rounded-2xl border border-surface-border p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('owner.menus.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
           {{-- Pencarian Nama Menu --}}
<div class="relative flex-1 min-w-0">
    <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
        <x-lucide-search class="w-4 h-4 shrink-0" style="width: 16px; height: 16px;" />
    </span>
    <input
        type="text"
        name="search"
        value="{{ request('search') }}"
        placeholder="Cari nama menu..."
        style="padding-left: 2.5rem;"
        class="block w-full pl-10 pr-3.5 py-2 text-sm rounded-xl border border-surface-border bg-surface-light text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-brand-500 focus:outline-none transition"
    >
</div>

            {{-- Filter Kategori --}}
            <div class="sm:w-48 shrink-0">
                <select
                    name="category_id"
                    class="block w-full px-3 py-2 text-sm rounded-xl border border-surface-border bg-surface-light text-slate-900 focus:bg-white focus:border-brand-500 focus:outline-none transition"
                >
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Stok Kritis --}}
            <div class="flex items-center shrink-0 px-1">
                <label class="inline-flex items-center gap-2 cursor-pointer select-none text-sm font-medium text-slate-700 whitespace-nowrap">
                    <input
                        type="checkbox"
                        name="low_stock"
                        value="1"
                        {{ request()->boolean('low_stock') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-[#2b2d6e] focus:ring-[#2b2d6e]/20"
                    >
                    <span>Stok Kritis</span>
                </label>
            </div>

            {{-- Tombol Filter & Reset Sejajar --}}
            <div class="flex items-center gap-2 shrink-0">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl bg-[#2b2d6e] hover:bg-[#202253] text-white transition shadow-xs whitespace-nowrap cursor-pointer"
                >
                    <x-lucide-filter class="w-4 h-4 shrink-0" />
                    <span>Filter</span>
                </button>

                @if (request()->hasAny(['search', 'category_id', 'low_stock']))
                    <a
                        href="{{ route('owner.menus.index') }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-sm font-semibold rounded-xl border border-surface-border bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition whitespace-nowrap"
                        title="Bersihkan Filter"
                    >
                        <x-lucide-rotate-ccw class="w-4 h-4 shrink-0" />
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Error validasi (menu, amount, stock_action) ditampilkan oleh <x-alert /> di layout utama agar tidak dobel --}}

    {{-- Kartu Tabel Daftar Menu --}}
    <div class="bg-white rounded-2xl border border-surface-border shadow-xs overflow-hidden">
        @if ($menus->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-surface-border text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th scope="col" class="py-3.5 px-4 sm:px-6">Menu</th>
                            <th scope="col" class="py-3.5 px-4">Kategori</th>
                            <th scope="col" class="py-3.5 px-4">Harga</th>
                            <th scope="col" class="py-3.5 px-4">Stok & Status</th>
                            <th scope="col" class="py-3.5 px-4">Quick Restock</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border text-slate-700">
                        @foreach ($menus as $menu)
                            @php
                                $stock = (int) $menu->stock;
                                $minStock = (int) $menu->min_stock;

                                if ($stock === 0) {
                                    $stockStatusLabel = 'Habis';
                                    $stockBadgeClass = 'bg-status-danger/10 text-red-700 border-red-200';
                                    $stockCountClass = 'text-red-700';
                                } elseif ($stock <= $minStock) {
                                    $stockStatusLabel = 'Kritis';
                                    $stockBadgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                                    $stockCountClass = 'text-amber-700';
                                } else {
                                    $stockStatusLabel = 'Aman';
                                    $stockBadgeClass = 'bg-status-success/10 text-emerald-700 border-emerald-200';
                                    $stockCountClass = 'text-slate-900';
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition align-middle">
                                {{-- Foto dan Nama Menu --}}
                                <td class="py-4 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        @if ($menu->image_path)
                                            <img
                                                src="{{ Storage::url($menu->image_path) }}"
                                                alt="{{ $menu->name }}"
                                                onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                                class="w-12 h-12 rounded-xl object-cover border border-surface-border shrink-0"
                                            >
                                            <div class="hidden w-12 h-12 rounded-xl bg-slate-100 border border-surface-border flex items-center justify-center text-slate-400 shrink-0">
                                                <x-lucide-utensils class="w-5 h-5" />
                                            </div>
                                        @else
                                            <div class="w-12 h-12 rounded-xl bg-slate-100 border border-surface-border flex items-center justify-center text-slate-400 shrink-0">
                                                <x-lucide-utensils class="w-5 h-5" />
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 truncate">{{ $menu->name }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kategori --}}
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $menu->category?->name ?? '-' }}
                                    </span>
                                </td>

                                {{-- Harga --}}
                                <td class="py-4 px-4 whitespace-nowrap font-medium text-slate-900">
                                    Rp {{ number_format($menu->price, 0, ',', '.') }}
                                </td>

                                {{-- Stok & Status --}}
<td class="py-4 px-4 whitespace-nowrap">
    <div class="space-y-1.5">
        <div>
            <div class="font-bold text-sm leading-tight {{ $stockCountClass }}">
                {{ $stock }}
            </div>
            <div class="text-xs text-ink-muted leading-tight mt-0.5">
                Min. stok: {{ $minStock }}
            </div>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap border {{ $stockBadgeClass }}">
                {{ $stockStatusLabel }}
            </span>
            @if ($menu->is_available)
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap bg-status-success/10 text-emerald-700 border border-emerald-200">
                    Aktif
                </span>
            @else
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap bg-slate-100 text-slate-600 border border-slate-200">
                    Nonaktif
                </span>
            @endif
        </div>
    </div>
</td>

                                {{-- Form Quick Restock --}}
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <form
                                        method="POST"
                                        action="{{ route('owner.menus.stock', $menu) }}"
                                        class="flex items-center gap-1.5"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <select
                                            name="stock_action"
                                            class="rounded-lg border border-surface-border text-xs py-1.5 px-2 bg-surface-light text-slate-700 focus:bg-white focus:outline-none focus:border-brand-500"
                                            title="Pilih Operasi Stok"
                                        >
                                            <option value="add">+ Tambah</option>
                                            <option value="set">= Atur</option>
                                        </select>
                                        <input
                                            type="number"
                                            name="amount"
                                            min="0"
                                            max="100000"
                                            placeholder="Jml"
                                            required
                                            class="w-20 rounded-lg border border-surface-border text-xs py-1.5 px-2.5 bg-surface-light text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:border-brand-500"
                                        >
                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center gap-1 px-2.5 py-1.5 rounded-lg bg-ink-primary hover:opacity-90 text-white text-xs font-semibold transition cursor-pointer"
                                            title="Perbarui Stok"
                                        >
                                            <x-lucide-check class="w-3.5 h-3.5 shrink-0" />
                                            <span>Update</span>
                                        </button>
                                    </form>
                                </td>

                                {{-- Tombol Aksi --}}
                                <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('owner.menus.edit', $menu) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 hover:text-slate-900 transition"
                                        >
                                            <x-lucide-pencil class="w-3.5 h-3.5" />
                                            <span>Edit</span>
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('owner.menus.destroy', $menu) }}"
                                            onsubmit="return confirm(@js('Apakah Anda yakin ingin menghapus menu ' . $menu->name . '?'));"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-rose-200 text-rose-700 bg-white hover:bg-rose-50 hover:border-rose-300 transition cursor-pointer"
                                            >
                                                <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginasi --}}
            @if ($menus->hasPages())
                <div class="p-4 sm:px-6 border-t border-surface-border bg-slate-50/50">
                    {{ $menus->links() }}
                </div>
            @endif
        @else
            {{-- Tampilan Kosong (Empty State) --}}
            <div class="py-16 px-4 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                    <x-lucide-utensils class="w-7 h-7" />
                </div>
                <h3 class="text-base font-bold text-slate-800">Tidak ada menu ditemukan</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">
                    @if (request()->hasAny(['search', 'category_id', 'low_stock']))
                        Tidak ada menu yang sesuai dengan kriteria filter saat ini. Coba reset filter pencarian.
                    @else
                        Menu makanan dan minuman belum tersedia. Tambahkan menu pertama untuk mulai mengelola stok.
                    @endif
                </p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    @if (request()->hasAny(['search', 'category_id', 'low_stock']))
                        <a
                            href="{{ route('owner.menus.index') }}"
                            class="inline-flex items-center gap-1.5 border border-surface-border bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm px-4 py-2.5 rounded-xl shadow-xs transition"
                        >
                            <x-lucide-rotate-ccw class="w-4 h-4" />
                            <span>Reset Filter</span>
                        </a>
                    @endif
                    <a
                        href="{{ route('owner.menus.create') }}"
                        class="inline-flex items-center gap-2 bg-[#2b2d6e] hover:bg-[#202253] text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-xs transition"
                    >
                        <x-lucide-plus class="w-4 h-4" />
                        <span>Tambah Menu</span>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection