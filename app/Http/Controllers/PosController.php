<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PosController extends Controller
{
    /**
     * Tampilkan layar utama Kasir POS.
     */
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount(['menus' => function ($query) {
                $query->where('is_available', true);
            }])
            ->orderBy('name')
            ->get();

        $menus = Menu::query()
            ->with('category')
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('categories', 'menus'));
    }
}