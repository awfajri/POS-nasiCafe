<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    // Menampilkan daftar kategori
    public function index()
    {
        // withCount('menus') menambah kolom menus_count (jumlah menu per kategori)
        // tanpa query terpisah untuk tiap baris
        $categories = Category::withCount('menus')
            ->orderBy('name')   // urut abjad
            ->paginate(10);     // 10 data per halaman

        return view('owner.categories.index', compact('categories'));
    }

    // Menampilkan form tambah kategori
    public function create()
    {
        // category = null memberi tahu view bahwa ini mode tambah, bukan edit
        return view('owner.categories.form', ['category' => null]);
    }

    // Menyimpan kategori baru
    public function store(CategoryRequest $request)
    {
        // Validasi sudah dijalankan CategoryRequest sebelum method ini dipanggil.
        // validated() hanya berisi field yang lolos aturan (bukan $request->all())
        $validated = $request->validated();

        // Slug dibuat otomatis dari nama, contoh: "Nasi Spesial" jadi "nasi-spesial"
        $validated['slug'] = Str::slug($validated['name']);

        Category::create($validated);

        return redirect()
            ->route('owner.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    // Menampilkan form edit kategori
    public function edit(Category $category)
    {
        // $category otomatis diambil dari ID di URL (route model binding),
        // kalau ID tidak ada hasilnya 404. Form yang sama dipakai ulang dengan data terisi
        return view('owner.categories.form', compact('category'));
    }

    // Menyimpan perubahan kategori
    public function update(CategoryRequest $request, Category $category)
    {
        $validated = $request->validated();

        // Slug ikut diperbarui mengikuti nama baru
        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);

        return redirect()
            ->route('owner.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    // Menghapus kategori
    public function destroy(Category $category)
    {
        // Kategori yang masih punya menu tidak boleh dihapus.
        // ValidationException membuat Laravel kembali ke halaman sebelumnya
        // membawa pesan error di key 'category'
        if ($category->menus()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Kategori tidak dapat dihapus karena masih memiliki menu.',
            ]);
        }

        $category->delete();

        return redirect()
            ->route('owner.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}