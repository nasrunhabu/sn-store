<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(ProductService $productService): View
    {
        return view('pages.admin.products.index', [
            'products' => $productService->getProducts(),
        ]);
    }

    public function create(ProductService $productService): View
    {
        return view('pages.admin.products.form', [
            'categories' => $productService->getCategories(),
            'product' => null,
            'pageTitle' => 'Tambah produk',
        ]);
    }

    public function store(StoreProductRequest $request, ProductService $productService): RedirectResponse
    {
        $alert = $productService->create($request->validated(), $request->file('image'));

        return to_route('admin.products.index')->with('product_alert', $alert);
    }

    public function edit(Product $product, ProductService $productService): View
    {
        return view('pages.admin.products.form', [
            'categories' => $productService->getCategories(),
            'product' => $product,
            'pageTitle' => 'Edit produk',
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        ProductService $productService,
    ): RedirectResponse {
        $alert = $productService->update($product, $request->validated(), $request->file('image'));

        return to_route('admin.products.index')->with('product_alert', $alert);
    }

    public function destroy(Product $product, ProductService $productService): RedirectResponse
    {
        $alert = $productService->delete($product);

        return to_route('admin.products.index')->with('product_alert', $alert);
    }
}
