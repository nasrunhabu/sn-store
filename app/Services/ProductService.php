<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProductService
{
    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return Product::query()
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return Category::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{categories: array<int, string>, products: Collection<int, array<string, mixed>>}
     */
    public function getPublicCatalog(array $filters): array
    {
        $searchTerm = mb_strtolower(trim($filters['search'] ?? ''));
        $selectedCategory = $filters['category'] ?? '';

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = Product::query()
            ->with('category:id,name')
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when($searchTerm !== '', function ($query) use ($searchTerm): void {
                $query->where(function ($query) use ($searchTerm): void {
                    $query->whereRaw('LOWER(products.name) LIKE ?', ["%{$searchTerm}%"])
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery
                            ->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"]));
                });
            })
            ->when($selectedCategory !== '', fn ($query) => $query->whereHas(
                'category',
                fn ($categoryQuery) => $categoryQuery->where('name', $selectedCategory),
            ))
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'category' => $product->category->name,
                'image' => $product->image_url,
                'image_alt' => $product->image_alt,
                'badge' => $product->badge,
                'rating' => $product->rating,
                'reviews' => $product->reviews,
                'price' => 'Rp '.number_format($product->price, 0, ',', '.'),
                'original_price' => $product->original_price === null
                    ? null
                    : 'Rp '.number_format($product->original_price, 0, ',', '.'),
            ]);

        return [
            'categories' => $categories->pluck('name')->all(),
            'products' => $products,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, UploadedFile $image): array
    {
        $imagePath = $image->store('products', 'public');

        if (! is_string($imagePath)) {
            throw new RuntimeException('Gambar produk gagal disimpan.');
        }

        try {
            $product = DB::transaction(function () use ($attributes, $imagePath): Product {
                $attributes = Arr::except($attributes, ['image']);
                $attributes['is_active'] = (bool) ($attributes['is_active'] ?? false);
                $attributes['image_path'] = $imagePath;

                return Product::create($attributes);
            });
        } catch (Throwable $exception) {
            $this->deleteStoredImage($imagePath);

            throw $exception;
        }

        return [
            'title' => 'Produk berhasil ditambahkan',
            'text' => "Produk \"{$product->name}\" berhasil ditambahkan.",
            'icon' => 'success',
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Product $product, array $attributes, ?UploadedFile $image): array
    {
        $newImagePath = $image?->store('products', 'public');

        if ($image !== null && ! is_string($newImagePath)) {
            throw new RuntimeException('Gambar produk gagal disimpan.');
        }

        $oldImagePath = $product->image_path;

        try {
            DB::transaction(function () use ($product, $attributes, $newImagePath): void {
                $attributes = Arr::except($attributes, ['image']);
                $attributes['is_active'] = (bool) ($attributes['is_active'] ?? false);
                $attributes['image_path'] = $newImagePath ?? $product->image_path;

                $product->update($attributes);
            });
        } catch (Throwable $exception) {
            if (is_string($newImagePath)) {
                $this->deleteStoredImage($newImagePath);
            }

            throw $exception;
        }

        if (is_string($newImagePath)) {
            $this->deleteStoredImage($oldImagePath);
        }

        return [
            'title' => 'Produk berhasil diperbarui',
            'text' => "Produk \"{$product->name}\" berhasil diperbarui.",
            'icon' => 'success',
        ];
    }

    public function delete(Product $product): array
    {
        $productName = $product->name;
        $imagePath = $product->image_path;

        DB::transaction(fn () => $product->delete());
        $this->deleteStoredImage($imagePath);

        return [
            'title' => 'Produk berhasil dihapus',
            'text' => "Produk \"{$productName}\" berhasil dihapus.",
            'icon' => 'success',
        ];
    }

    private function deleteStoredImage(string $imagePath): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists($imagePath) && ! $disk->delete($imagePath)) {
            throw new RuntimeException('Gambar produk gagal dihapus.');
        }
    }
}
