<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuRequest;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

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

    // Menyimpan menu baru
    public function store(MenuRequest $request)
    {
        // validated() hanya berisi field yang lolos aturan MenuRequest
        $data = $request->validated();

        // 'image' (objek file) bukan kolom database, jadi dibuang dari data
        unset($data['image']);

        // Simpan foto ke disk public dengan nama acak, kalau ada file yang diunggah
        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('menus', 'public');
            $data['image_path'] = $path;
        }

        try {
            Menu::create($data);
        } catch (Throwable $e) {
            // Kalau simpan ke database gagal, hapus foto yang sudah terlanjur tersimpan
            // supaya tidak ada file yatim, lalu lempar ulang errornya
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }

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
        $data = $request->validated();

        // Jika ada foto baru diunggah, hapus foto lama lalu simpan foto baru
        if ($request->hasFile('image')) {
            if ($menu->image_path) {
                Storage::disk('public')->delete($menu->image_path);
            }

            $data['image_path'] = $request->file('image')->store('menus', 'public');
        }

        // Hapus key 'image' karena kolom di database adalah image_path
        unset($data['image']);

        // Perbarui data menu (termasuk stock langsung dari form/stock opname)
        $menu->update($data);

        return redirect()
            ->route('owner.menus.index')
            ->with('success', 'Menu berhasil diperbarui.');
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