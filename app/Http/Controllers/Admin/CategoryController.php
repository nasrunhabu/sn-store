<?php

namespace App\Http\Controllers\Admin;

use App\DemoStore;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(DemoStore $store): View
    {
        $categories = collect($store->all()['categories'])->sortBy('name')->values();

        return view('pages.admin.categories.index', compact('categories'));
    }

    public function store(Request $request, DemoStore $store): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $store->update(function (array $data) use ($validated): array {
            $this->ensureNameIsAvailable($data['categories'], $validated['name']);
            $data['categories'][] = [
                'id' => (string) Str::uuid(),
                'name' => $validated['name'],
                'is_active' => true,
            ];

            return $data;
        });

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, string $category, DemoStore $store): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        $store->update(function (array $data) use ($category, $validated): array {
            $index = collect($data['categories'])->search(fn (array $item): bool => $item['id'] === $category);

            abort_if($index === false, 404);
            $this->ensureNameIsAvailable($data['categories'], $validated['name'], $category);
            $data['categories'][$index] = array_replace($data['categories'][$index], $validated);

            return $data;
        });

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(string $category, DemoStore $store): RedirectResponse
    {
        $store->update(function (array $data) use ($category): array {
            $index = collect($data['categories'])->search(fn (array $item): bool => $item['id'] === $category);

            abort_if($index === false, 404);

            if (collect($data['products'])->contains('category_id', $category)) {
                throw ValidationException::withMessages([
                    'category' => 'Kategori masih digunakan produk. Pindahkan atau hapus produk tersebut terlebih dahulu.',
                ]);
            }

            array_splice($data['categories'], $index, 1);

            return $data;
        });

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil dihapus.');
    }

    private function ensureNameIsAvailable(array $categories, string $name, ?string $ignoreId = null): void
    {
        $nameExists = collect($categories)->contains(
            fn (array $category): bool => $category['id'] !== $ignoreId
                && Str::lower($category['name']) === Str::lower($name)
        );

        if ($nameExists) {
            throw ValidationException::withMessages([
                'name' => 'Nama kategori sudah digunakan.',
            ]);
        }
    }
}
