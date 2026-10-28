<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(CategoryService $categoryService): View
    {
        return view('pages.admin.categories.index', [
            'categories' => $categoryService->getCategories(),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.categories.form', [
            'category' => null,
            'pageTitle' => 'Tambah kategori',
        ]);
    }

    public function store(CategoryRequest $request, CategoryService $categoryService): RedirectResponse
    {
        return to_route('admin.categories.index')
            ->with('category_alert', $categoryService->create($request->validated()));
    }

    public function edit(Category $category): View
    {
        return view('pages.admin.categories.form', [
            'category' => $category,
            'pageTitle' => 'Edit kategori',
        ]);
    }

    public function update(CategoryRequest $request, Category $category, CategoryService $categoryService): RedirectResponse
    {
        return to_route('admin.categories.index')
            ->with('category_alert', $categoryService->update($category, $request->validated()));
    }

    public function destroy(Category $category, CategoryService $categoryService): RedirectResponse
    {
        return to_route('admin.categories.index')
            ->with('category_alert', $categoryService->delete($category));
    }
}
