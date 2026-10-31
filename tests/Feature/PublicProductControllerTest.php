<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

/**
 * @return array{Category, Product}
 */
function seedPublicProductPage(): array
{
    $laptop = Category::create([
        'name' => 'Laptop',
        'slug' => 'laptop',
        'is_active' => true,
    ]);
    $smartphone = Category::create([
        'name' => 'Smartphone',
        'slug' => 'smartphone',
        'is_active' => true,
    ]);

    $macBook = Product::create([
        'category_id' => $laptop->id,
        'name' => 'MacBook Air M3',
        'price' => 18000000,
        'badge' => 'Pilihan',
        'image_path' => 'products/macbook.jpg',
        'image_alt' => 'MacBook Air M3',
        'is_active' => true,
    ]);
    Product::create([
        'category_id' => $smartphone->id,
        'name' => 'iPhone 15',
        'price' => 15000000,
        'badge' => 'Baru',
        'image_path' => 'products/iphone.jpg',
        'image_alt' => 'iPhone 15',
        'is_active' => true,
    ]);

    return [$laptop, $macBook];
}

it('marks products with zero stock as unavailable', function () {
    seedPublicProductPage();

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('Semua Produk')
        ->assertSee('Laptop')
        ->assertSee('Smartphone')
        ->assertSee('MacBook Air M3')
        ->assertSee('Stok habis')
        ->assertSee('MacBook Air M3 sedang habis');
});

it('shows login for guests and an add-to-cart form for signed-in shoppers', function () {
    $product = Product::factory()->create(['stock' => 3]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee(route('login'))
        ->assertSee('Masuk untuk menambahkan '.$product->name.' ke keranjang');

    $this->actingAs(User::factory()->create())
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee(route('cart.items.store', $product))
        ->assertSee('Tambah '.$product->name.' ke keranjang');
});

it('filters products by category and search term', function () {
    seedPublicProductPage();

    $this->get(route('products.index', ['category' => 'Laptop', 'search' => 'Air']))
        ->assertOk()
        ->assertSee('MacBook Air M3')
        ->assertDontSee('iPhone 15')
        ->assertSee('name="category" value="Laptop"', false);
});

it('does not render inactive products or products from inactive categories', function () {
    [$activeCategory] = seedPublicProductPage();
    $inactiveCategory = Category::create([
        'name' => 'Aksesori Nonaktif',
        'slug' => 'aksesori-nonaktif',
        'is_active' => false,
    ]);
    Product::create([
        'category_id' => $activeCategory->id,
        'name' => 'Produk Nonaktif',
        'price' => 100000,
        'badge' => 'Pilihan',
        'image_path' => 'products/inactive.jpg',
        'image_alt' => 'Produk nonaktif',
        'is_active' => false,
    ]);
    Product::create([
        'category_id' => $inactiveCategory->id,
        'name' => 'Produk Kategori Nonaktif',
        'price' => 100000,
        'badge' => 'Pilihan',
        'image_path' => 'products/inactive-category.jpg',
        'image_alt' => 'Produk kategori nonaktif',
        'is_active' => true,
    ]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('MacBook Air M3')
        ->assertDontSee('Produk Nonaktif')
        ->assertDontSee('Produk Kategori Nonaktif')
        ->assertDontSee('Aksesori Nonaktif');
});
