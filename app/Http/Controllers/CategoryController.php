<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('menus')
            ->orderBy('name')
            ->paginate(10);

        return view('owner.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('owner.categories.form', ['category' => null]);
    }

    public function store(CategoryRequest $request)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        Category::create($validated);

        return redirect()
            ->route('owner.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('owner.categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);

        return redirect()
            ->route('owner.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
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
