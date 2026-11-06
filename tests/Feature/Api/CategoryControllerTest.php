<?php

use App\Models\Category;

it('returns active categories publicly with the requested fields and timestamps', function () {
    $category = Category::factory()->create([
        'name' => 'Aksesoris',
        'slug' => 'aksesoris',
        'description' => 'Kategori aksesoris.',
        'is_active' => true,
    ]);
    Category::factory()->create([
        'name' => 'Arsip',
        'slug' => 'arsip',
        'is_active' => false,
    ]);

    $response = $this->getJson(route('api.categories.index'));

    $response->assertOk()
        ->assertExactJson([
            'code' => 200,
            'status' => 'success',
            'message' => 'Data kategori berhasil dimuat.',
            'data' => [
                [
                    'id' => $category->id,
                    'nama' => 'Aksesoris',
                    'slug' => 'aksesoris',
                    'deskripsi' => 'Kategori aksesoris.',
                    'created_at' => $category->created_at?->toISOString(),
                    'updated_at' => $category->updated_at?->toISOString(),
                ],
            ],
        ]);
});
