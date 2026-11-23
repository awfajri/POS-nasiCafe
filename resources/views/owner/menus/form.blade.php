@extends('layouts.app')

@section('title', $menu ? 'Edit Menu' : 'Tambah Menu')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header Halaman & Navigasi Kembali --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                {{ $menu ? 'Edit Menu' : 'Tambah Menu' }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $menu ? 'Perbarui informasi produk menu, harga, stok, dan foto.' : 'Tambahkan menu makanan atau minuman baru ke dalam sistem POS.' }}
            </p>
        </div>
        <a
            href="{{ route('owner.menus.index') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold rounded-xl border border-surface-border bg-white text-slate-700 hover:bg-slate-50 transition shadow-xs"
        >
            <x-lucide-arrow-left class="w-4 h-4 shrink-0" />
            <span>Kembali</span>
        </a>
    </div>

    {{-- Kartu Formulir --}}
    <div class="bg-white rounded-2xl border border-surface-border shadow-xs p-6 sm:p-8">
        <form
            method="POST"
            action="{{ $menu ? route('owner.menus.update', $menu) : route('owner.menus.store') }}"
            enctype="multipart/form-data"
            class="space-y-6"
        >
            @csrf
            @if ($menu)
                @method('PUT')
            @endif

            {{-- Baris 1: Kategori & Nama Menu --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Kategori --}}
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Kategori <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="category_id"
                        name="category_id"
                        required
                        class="block w-full rounded-xl border px-3.5 py-2.5 text-sm bg-surface-light text-slate-900 focus:bg-white focus:outline-none transition {{ $errors->has('category_id') ? 'border-rose-500 focus:border-rose-500 ring-1 ring-rose-500' : 'border-surface-border focus:border-brand-500' }}"
                    >
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                {{ (string) old('category_id', $menu?->category_id) === (string) $category->id ? 'selected' : '' }}
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nama Menu --}}
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Nama Menu <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $menu?->name) }}"
                        required
                        autofocus
                        placeholder="Contoh: Nasi Goreng Spesial, Es Teh Manis"
                        class="block w-full rounded-xl border px-3.5 py-2.5 text-sm bg-surface-light text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-none transition {{ $errors->has('name') ? 'border-rose-500 focus:border-rose-500 ring-1 ring-rose-500' : 'border-surface-border focus:border-brand-500' }}"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Deskripsi (Opsional) --}}
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-800 mb-1.5">
                    Deskripsi <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Tuliskan keterangan komposisi atau deskripsi menu..."
                    class="block w-full rounded-xl border px-3.5 py-2.5 text-sm bg-surface-light text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-none transition resize-y {{ $errors->has('description') ? 'border-rose-500 focus:border-rose-500 ring-1 ring-rose-500' : 'border-surface-border focus:border-brand-500' }}"
                >{{ old('description', $menu?->description) }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Baris 2: Harga, Stok, dan Batas Minimum Stok --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                {{-- Harga --}}
                <div>
                    <label for="price" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Harga (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-xs font-bold text-slate-400">
                            Rp
                        </span>
                        <input
                            type="number"
                            id="price"
                            name="price"
                            min="0"
                            max="10000000"
                            value="{{ old('price', $menu?->price) }}"
                            required
                            placeholder="0"
                            class="block w-full rounded-xl border pl-10 pr-3.5 py-2.5 text-sm bg-surface-light text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-none transition {{ $errors->has('price') ? 'border-rose-500 focus:border-rose-500 ring-1 ring-rose-500' : 'border-surface-border focus:border-brand-500' }}"
                        >
                    </div>
                    @error('price')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Stok Saat Ini --}}
                <div>
                    <label for="stock" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Stok Saat Ini <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        min="0"
                        max="100000"
                        value="{{ old('stock', $menu?->stock ?? 0) }}"
                        required
                        placeholder="0"
                        class="block w-full rounded-xl border px-3.5 py-2.5 text-sm bg-surface-light text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-none transition {{ $errors->has('stock') ? 'border-rose-500 focus:border-rose-500 ring-1 ring-rose-500' : 'border-surface-border focus:border-brand-500' }}"
                    >
                    @error('stock')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Batas Minimum Stok --}}
                <div>
                    <label for="min_stock" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Min. Stok <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="number"
                        id="min_stock"
                        name="min_stock"
                        min="0"
                        max="100000"
                        value="{{ old('min_stock', $menu?->min_stock ?? 5) }}"
                        required
                        placeholder="5"
                        class="block w-full rounded-xl border px-3.5 py-2.5 text-sm bg-surface-light text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-none transition {{ $errors->has('min_stock') ? 'border-rose-500 focus:border-rose-500 ring-1 ring-rose-500' : 'border-surface-border focus:border-brand-500' }}"
                    >
                    @error('min_stock')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Baris 3: Status Ketersediaan Menu --}}
            <div class="rounded-xl border border-surface-border bg-surface-light/60 p-4">
                <label class="inline-flex items-start gap-3 cursor-pointer select-none">
                    @php
                        $isAvailable = filter_var(old('is_available', $menu?->is_available ?? true), FILTER_VALIDATE_BOOLEAN);
                    @endphp
                    <input
                        type="checkbox"
                        id="is_available"
                        name="is_available"
                        value="1"
                        {{ $isAvailable ? 'checked' : '' }}
                        class="mt-1 w-4 h-4 rounded border-slate-300 text-[#2b2d6e] focus:ring-[#2b2d6e]/20"
                    >
                    <div>
                        <span class="block text-sm font-semibold text-slate-900">Menu Tersedia untuk Dijual</span>
                        <span class="block text-xs text-slate-500 mt-0.5">
                            Jika dinonaktifkan, menu ini tidak akan muncul pada halaman POS kasir maupun menu pelanggan.
                        </span>
                    </div>
                </label>
                @error('is_available')
                    <p class="mt-2 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Baris 4: Upload Foto Menu --}}
            <div>
                <label for="image" class="block text-sm font-semibold text-slate-800 mb-1.5">
                    Foto Menu <span class="text-xs font-normal text-slate-400">(Opsional, maks. 2MB)</span>
                </label>

                {{-- Pratinjau Foto Saat Ini (Jika Mode Edit & Memiliki Foto) --}}
                @if ($menu?->image_path)
                    <div class="mb-3 flex items-center gap-4 p-3.5 bg-surface-light border border-surface-border rounded-xl">
                        <img
                            src="{{ Storage::url($menu->image_path) }}"
                            alt="{{ $menu->name }}"
                            class="w-16 h-16 rounded-xl object-cover border border-surface-border shrink-0 shadow-2xs"
                        >
                        <div class="text-xs text-slate-600 min-w-0">
                            <p class="font-semibold text-slate-800">Foto Saat Ini</p>
                            <p class="text-slate-400 mt-0.5">Pilih file baru di bawah ini jika ingin mengganti foto menu saat ini.</p>
                        </div>
                    </div>
                @endif

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#2b2d6e]/10 file:text-[#2b2d6e] hover:file:bg-[#2b2d6e]/20 cursor-pointer rounded-xl border px-3.5 py-2 bg-surface-light focus:outline-none transition {{ $errors->has('image') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-surface-border' }}"
                >
                <p class="mt-1.5 text-xs text-slate-400">Format yang didukung: JPG, JPEG, PNG, atau WEBP (Maksimal 2048 KB / 2MB).</p>
                @error('image')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

           {{-- Tombol Aksi --}}
            <div class="pt-5 border-t border-slate-100 flex flex-wrap items-center justify-end gap-3">
                <a
                    href="{{ route('owner.menus.index') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-surface-border bg-white text-slate-700 hover:bg-slate-50 font-semibold text-sm whitespace-nowrap transition shadow-xs"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#2b2d6e] hover:bg-[#202253] text-white font-semibold text-sm whitespace-nowrap transition shadow-xs cursor-pointer"
                >
                    <x-lucide-save class="w-4 h-4 shrink-0" style="width: 16px; height: 16px;" />
                    <span>{{ $menu ? 'Simpan Perubahan' : 'Simpan Menu' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
