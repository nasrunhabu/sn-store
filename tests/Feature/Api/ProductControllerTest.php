<?php

use App\Models\Category;
use App\Models\Product;

it('returns active products publicly with all product fields and the category name', function () {
    $category = Category::factory()->create([
        'name' => 'Aksesoris',
        'is_active' => true,
    ]);
    $product = Product::factory()->for($category)->create([
        'name' => 'Headphone',
        'price' => 450000,
        'stock' => 8,
        'original_price' => 500000,
        'rating' => 4.8,
        'reviews' => 21,
        'badge' => 'Promo',
        'image_path' => 'products/headphone.jpg',
        'image_alt' => 'Headphone hitam',
        'is_active' => true,
    ]);
    Product::factory()->for($category)->create([
        'is_active' => false,
    ]);
    $inactiveCategory = Category::factory()->create(['is_active' => false]);
    Product::factory()->for($inactiveCategory)->create(['is_active' => true]);

    $this->getJson(route('api.products.index'))
        ->assertOk()
        ->assertExactJson([
            'code' => 200,
            'status' => 'success',
            'message' => 'Data produk berhasil dimuat.',
            'data' => [
                [
                    'id' => $product->id,
                    'name' => 'Headphone',
                    'price' => 450000,
                    'stock' => 8,
                    'original_price' => 500000,
                    'rating' => '4.8',
                    'reviews' => 21,
                    'badge' => 'Promo',
                    'image_path' => 'products/headphone.jpg',
                    'image_alt' => 'Headphone hitam',
                    'is_active' => true,
                    'created_at' => $product->created_at?->toISOString(),
                    'updated_at' => $product->updated_at?->toISOString(),
                    'category' => 'Aksesoris',
                ],
            ],
        ]);
});

it('returns a public product detail by id', function () {
    $category = Category::factory()->create([
        'name' => 'Audio',
        'is_active' => true,
    ]);
    $product = Product::factory()->for($category)->create([
        'name' => 'Speaker Bluetooth',
        'price' => 650000,
        'stock' => 5,
        'is_active' => true,
    ]);

    $this->getJson(route('api.products.show', $product))
        ->assertOk()
        ->assertExactJson([
            'code' => 200,
            'status' => 'success',
            'message' => 'Detail produk berhasil dimuat.',
            'data' => [
                'id' => $product->id,
                'name' => 'Speaker Bluetooth',
                'price' => 650000,
                'stock' => 5,
                'original_price' => null,
                'rating' => '5.0',
                'reviews' => 0,
                'badge' => 'Pilihan',
                'image_path' => 'products/sample.jpg',
                'image_alt' => $product->image_alt,
                'is_active' => true,
                'created_at' => $product->created_at?->toISOString(),
                'updated_at' => $product->updated_at?->toISOString(),
                'category' => 'Audio',
            ],
        ]);
});

it('returns a formatted 404 when a product is not publicly available', function () {
    $category = Category::factory()->create(['is_active' => false]);
    $product = Product::factory()->for($category)->create(['is_active' => true]);

    $this->getJson(route('api.products.show', $product))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'status' => 'error',
            'message' => 'Produk tidak ditemukan.',
            'data' => null,
        ]);
});
