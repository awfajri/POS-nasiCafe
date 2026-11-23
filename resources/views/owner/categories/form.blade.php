@extends('layouts.app')

@section('title', $category ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Header Halaman & Navigasi Kembali --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                {{ $category ? 'Edit Kategori' : 'Tambah Kategori' }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $category ? 'Perbarui data kategori menu makanan dan minuman.' : 'Tambahkan kategori baru untuk mengelompokkan menu makanan dan minuman.' }}
            </p>
        </div>
        <a
            href="{{ route('owner.categories.index') }}"
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
            action="{{ $category ? route('owner.categories.update', $category) : route('owner.categories.store') }}"
            class="space-y-5"
        >
            @csrf
            @if ($category)
                @method('PUT')
            @endif

            {{-- Input Nama Kategori --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-800 mb-1.5">
                    Nama Kategori <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $category?->name) }}"
                    required
                    autofocus
                    placeholder="Contoh: Makanan Berat, Minuman Dingin"
                    class="block w-full rounded-xl border px-3.5 py-2.5 text-sm bg-surface-light text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-none transition {{ $errors->has('name') ? 'border-rose-500 focus:border-rose-500 ring-1 ring-rose-500' : 'border-surface-border focus:border-brand-500' }}"
                >
                @error('name')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Input Deskripsi Kategori --}}
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-800 mb-1.5">
                    Deskripsi <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Tuliskan keterangan singkat mengenai kategori ini..."
                    class="block w-full rounded-xl border px-3.5 py-2.5 text-sm bg-surface-light text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-none transition resize-y {{ $errors->has('description') ? 'border-rose-500 focus:border-rose-500 ring-1 ring-rose-500' : 'border-surface-border focus:border-brand-500' }}"
                >{{ old('description', $category?->description) }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <a
                    href="{{ route('owner.categories.index') }}"
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-surface-border bg-white text-slate-700 hover:bg-slate-50 font-semibold text-sm transition shadow-xs"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#2b2d6e] hover:bg-[#202253] text-white font-semibold text-sm transition shadow-xs"
                >
                    <x-lucide-save class="w-4 h-4 shrink-0" />
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
