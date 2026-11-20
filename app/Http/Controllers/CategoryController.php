<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        abort(501, 'Belum diimplementasi');
    }

    public function create()
    {
        abort(501, 'Belum diimplementasi');
    }

    public function store(CategoryRequest $request)
    {
        abort(501, 'Belum diimplementasi');
    }

    public function edit(Category $category)
    {
        abort(501, 'Belum diimplementasi');
    }

    public function update(CategoryRequest $request, Category $category)
    {
        abort(501, 'Belum diimplementasi');
    }

    public function destroy(Category $category)
    {
        abort(501, 'Belum diimplementasi');
    }
}
