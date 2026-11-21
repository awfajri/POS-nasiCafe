<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuRequest;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        $menus = $query->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('owner.menus.index', compact('menus', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('owner.menus.form', [
            'menu' => null,
            'categories' => $categories,
        ]);
    }

    public function store(MenuRequest $request)
    {
        abort(501, 'Belum diimplementasi');
    }

    public function edit(Menu $menu)
    {
        $categories = Category::orderBy('name')->get();

        return view('owner.menus.form', compact('menu', 'categories'));
    }

    public function update(MenuRequest $request, Menu $menu)
    {
        abort(501, 'Belum diimplementasi');
    }

    public function destroy(Menu $menu)
    {
        abort(501, 'Belum diimplementasi');
    }

    public function updateStock(Request $request, Menu $menu)
    {
        abort(501, 'Belum diimplementasi');
    }
}
