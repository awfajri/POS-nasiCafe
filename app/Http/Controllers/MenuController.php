<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuRequest;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // Menampilkan daftar menu dengan pencarian, filter, dan paginasi
    public function index(Request $request)
    {
        // with('category') mengambil kategori sekaligus, tanpa query per baris
        $query = Menu::with('category');

        // Pencarian nama. Hanya diterima kalau berupa teks,
        // supaya URL seperti ?search[]=a tidak membuat error
        $search = $request->query('search');
        if (is_string($search) && trim($search) !== '') {
            // % dan _ di-escape supaya dianggap huruf biasa, bukan wildcard LIKE
            $escaped = addcslashes(trim($search), '%_\\');
            $query->where('name', 'like', '%' . $escaped . '%');
        }

        // Filter kategori. integer() memastikan nilainya angka, bukan teks atau array
        $categoryId = $request->integer('category_id');
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        // Filter stok kritis (stock <= min_stock), scope dari model Menu
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        // withQueryString() menjaga filter tetap aktif saat pindah halaman
        $menus = $query->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Daftar kategori untuk dropdown filter
        $categories = Category::orderBy('name')->get();

        return view('owner.menus.index', compact('menus', 'categories'));
    }

    // Menampilkan form tambah menu
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        // menu = null memberi tahu view bahwa ini mode tambah
        return view('owner.menus.form', [
            'menu' => null,
            'categories' => $categories,
        ]);
    }

    // Prompt 10
    public function store(MenuRequest $request)
    {
        $data = $request->validated();

        // Simpan gambar jika ada file yang diunggah
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('menus', 'public');
        }

        // Hapus key 'image' karena kolom di database bernama image_path
        unset($data['image']);

        Menu::create($data);

        return redirect()
            ->route('owner.menus.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    // Menampilkan form edit menu
    public function edit(Menu $menu)
    {
        $categories = Category::orderBy('name')->get();

        // $menu otomatis diambil dari ID di URL, ID tidak ada hasilnya 404
        return view('owner.menus.form', compact('menu', 'categories'));
    }

    // Prompt 11
    public function update(MenuRequest $request, Menu $menu)
    {
        abort(501, 'Belum diimplementasi');
    }

    // Prompt 12
    public function destroy(Menu $menu)
    {
        abort(501, 'Belum diimplementasi');
    }

    // Prompt 13
    public function updateStock(Request $request, Menu $menu)
    {
        abort(501, 'Belum diimplementasi');
    }
}