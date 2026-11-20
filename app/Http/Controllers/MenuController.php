<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuRequest;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        abort(501, 'Belum diimplementasi');
    }

    public function create()
    {
        abort(501, 'Belum diimplementasi');
    }

    public function store(MenuRequest $request)
    {
        abort(501, 'Belum diimplementasi');
    }

    public function edit(Menu $menu)
    {
        abort(501, 'Belum diimplementasi');
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
