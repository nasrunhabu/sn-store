<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService
{
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
     * @param  array{name: string, description?: ?string, is_active: bool}  $attributes
     * @return array{title: string, text: string, icon: string}
     */
    public function create(array $attributes): array
    {
        $attributes['slug'] = $this->generateSlug($attributes['name']);
        $attributes['is_active'] = (bool) $attributes['is_active'];
        $category = Category::create($attributes);

        return [
            'title' => 'Kategori berhasil ditambahkan',
            'text' => "Kategori \"{$category->name}\" berhasil ditambahkan.",
            'icon' => 'success',
        ];
    }

    /**
     * @param  array{name: string, description?: ?string, is_active: bool}  $attributes
     * @return array{title: string, text: string, icon: string}
     */
    public function update(Category $category, array $attributes): array
    {
        $previousName = $category->name;
        $nameChanged = $previousName !== $attributes['name'];
        $descriptionChanged = ($category->description ?? '') !== ($attributes['description'] ?? '');
        $attributes['is_active'] = (bool) $attributes['is_active'];
        $statusChanged = $category->is_active !== $attributes['is_active'];

        $attributes['slug'] = $this->generateSlug($attributes['name'], $category);
        $category->update($attributes);

        $changedFields = array_filter([
            'name' => $nameChanged,
            'description' => $descriptionChanged,
            'status' => $statusChanged,
        ]);

        $message = match (array_keys($changedFields)) {
            ['name'] => "Nama kategori berhasil diubah dari \"{$previousName}\" menjadi \"{$category->name}\".",
            ['description'] => "Deskripsi kategori \"{$category->name}\" berhasil diperbarui.",
            ['status'] => "Kategori \"{$category->name}\" berhasil ".($category->is_active ? 'diaktifkan.' : 'dinonaktifkan.'),
            [] => "Data kategori \"{$category->name}\" tidak mengalami perubahan.",
            default => "Kategori \"{$category->name}\" berhasil diperbarui.",
        };

        return [
            'title' => 'Kategori berhasil diperbarui',
            'text' => $message,
            'icon' => 'success',
        ];
    }

    /**
     * @return array{title: string, text: string, icon: string}
     */
    public function delete(Category $category): array
    {
        if ($category->products()->exists()) {
            return [
                'title' => 'Kategori tidak dapat dihapus',
                'text' => "Kategori \"{$category->name}\" masih digunakan oleh produk.",
                'icon' => 'error',
            ];
        }

        $categoryName = $category->name;
        $category->delete();

        return [
            'title' => 'Kategori berhasil dihapus',
            'text' => "Kategori \"{$categoryName}\" berhasil dihapus.",
            'icon' => 'success',
        ];
    }

    private function generateSlug(string $name, ?Category $category = null): string
    {
        $baseSlug = Str::slug($name) ?: 'kategori';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Category::query()
                ->where('slug', $slug)
                ->when($category !== null, fn ($query) => $query->where('id', '!=', $category->getKey()))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
