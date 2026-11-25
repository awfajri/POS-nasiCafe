@extends('layouts.app')

@section('title', 'Kategori Menu')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kategori Menu</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola kategori untuk mengelompokkan menu makanan dan minuman.</p>
        </div>
        <div>
            <a
                href="{{ route('owner.categories.create') }}"
                class="inline-flex items-center gap-2 bg-[#2b2d6e] hover:bg-[#202253] text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-xs transition"
            >
                <x-lucide-plus class="w-4 h-4 shrink-0" />
                <span>Tambah Kategori</span>
            </a>
        </div>
    </div>

    {{-- Pesan error hapus (key 'category') sudah ditampilkan komponen alert di layout --}}

    {{-- Kartu Tabel Kategori --}}
    <div class="bg-white rounded-2xl border border-surface-border shadow-xs overflow-hidden">
        @if ($categories->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-surface-border text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th scope="col" class="py-3.5 px-4 sm:px-6">Nama</th>
                            <th scope="col" class="py-3.5 px-4">Slug</th>
                            <th scope="col" class="py-3.5 px-4">Deskripsi</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Jumlah Menu</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border text-slate-700">
                        @foreach ($categories as $category)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-4 px-4 sm:px-6 font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $category->name }}
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono bg-slate-100 text-slate-600">
                                        {{ $category->slug }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 max-w-xs truncate text-slate-600">
                                    {{ $category->description ? $category->description : '-' }}
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $category->menus_count }} menu
                                    </span>
                                </td>
                                <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('owner.categories.edit', $category) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 hover:text-slate-900 transition"
                                        >
                                            <x-lucide-pencil class="w-3.5 h-3.5" />
                                            <span>Edit</span>
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('owner.categories.destroy', $category) }}"
                                            onsubmit="return confirm(@js('Apakah Anda yakin ingin menghapus kategori ' . $category->name . '?'))"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-rose-200 text-rose-700 bg-white hover:bg-rose-50 hover:border-rose-300 transition"
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

            {{-- Paginasi Halaman --}}
            @if ($categories->hasPages())
                <div class="p-4 sm:px-6 border-t border-surface-border bg-slate-50/50">
                    {{ $categories->links() }}
                </div>
            @endif
        @else
            {{-- Tampilan Kosong (Empty State) --}}
            <div class="py-16 px-4 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                    <x-lucide-book-open class="w-7 h-7" />
                </div>
                <h3 class="text-base font-bold text-slate-800">Belum ada kategori</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">
                    Kategori makanan dan minuman belum ditambahkan. Buat kategori pertama untuk mulai mengelompokkan menu.
                </p>
                <div class="mt-6">
                    <a
                        href="{{ route('owner.categories.create') }}"
                        class="inline-flex items-center gap-2 bg-[#2b2d6e] hover:bg-[#202253] text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-xs transition"
                    >
                        <x-lucide-plus class="w-4 h-4" />
                        <span>Tambah Kategori Sekarang</span>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
