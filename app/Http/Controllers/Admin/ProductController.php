<?php

namespace App\Http\Controllers\Admin;

use App\DemoStore;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(DemoStore $store): View
    {
        $data = $store->all();
        $categories = collect($data['categories'])->keyBy('id');
        $products = collect($data['products'])->sortBy('name')->values();

        return view('pages.admin.products.index', compact('categories', 'products'));
    }

    public function create(DemoStore $store): View
    {
        $categories = collect($store->all()['categories'])->sortBy('name')->values();

        return view('pages.admin.products.form', [
            'categories' => $categories,
            'product' => null,
            'pageTitle' => 'Tambah produk',
        ]);
    }

    public function store(Request $request, DemoStore $store): RedirectResponse
    {
        $validated = $request->validate($this->rules($store->all()['categories']));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['original_price'] = $validated['original_price'] ?? null;

        $store->update(function (array $data) use ($validated): array {
            $data['products'][] = array_merge([
                'id' => (string) Str::uuid(),
                'rating' => '5.0',
                'reviews' => 0,
            ], $validated);

            return $data;
        });

        return to_route('admin.products.index')->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(string $product, DemoStore $store): View
    {
        $data = $store->all();
        $productData = collect($data['products'])->firstWhere('id', $product);

        abort_if($productData === null, 404);

        return view('pages.admin.products.form', [
            'categories' => collect($data['categories'])->sortBy('name')->values(),
            'product' => $productData,
            'pageTitle' => 'Edit produk',
        ]);
    }

    public function update(Request $request, string $product, DemoStore $store): RedirectResponse
    {
        $validated = $request->validate($this->rules($store->all()['categories']));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['original_price'] = $validated['original_price'] ?? null;

        $store->update(function (array $data) use ($product, $validated): array {
            $index = collect($data['products'])->search(fn (array $item): bool => $item['id'] === $product);

            abort_if($index === false, 404);
            $data['products'][$index] = array_replace($data['products'][$index], $validated);

            return $data;
        });

        return to_route('admin.products.index')->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(string $product, DemoStore $store): RedirectResponse
    {
        $store->update(function (array $data) use ($product): array {
            $index = collect($data['products'])->search(fn (array $item): bool => $item['id'] === $product);

            abort_if($index === false, 404);
            array_splice($data['products'], $index, 1);

            return $data;
        });

        return to_route('admin.products.index')->with('status', 'Produk berhasil dihapus.');
    }

    private function rules(array $categories): array
    {
        $categoryIds = collect($categories)->pluck('id')->all();

        return [
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'string', Rule::in($categoryIds)],
            'price' => ['required', 'integer', 'min:1'],
            'original_price' => ['nullable', 'integer', 'min:1'],
            'badge' => ['required', 'string', 'max:40'],
            'image' => ['required', 'url:http,https', 'max:2048'],
            'image_alt' => ['required', 'string', 'max:160'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
