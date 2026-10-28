<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

function seedPublicHomeCatalog(): void
{
    $smartphone = Category::create([
        'name' => 'Smartphone',
        'slug' => 'smartphone',
        'is_active' => true,
    ]);
    $laptop = Category::create([
        'name' => 'Laptop',
        'slug' => 'laptop',
        'is_active' => true,
    ]);
    $audio = Category::create([
        'name' => 'Audio',
        'slug' => 'audio',
        'is_active' => true,
    ]);

    foreach ([
        ['iPhone 15 128GB', $smartphone->id],
        ['MacBook Air M3 13"', $laptop->id],
        ['Galaxy Buds FE', $audio->id],
        ['Smartwatch Active 3', $smartphone->id],
        ['Canon EOS R50 Kit', $laptop->id],
        ['JBL Flip 6', $audio->id],
        ['Sony WH-1000XM5', $audio->id],
        ['Logitech MX Master 3S', $laptop->id],
        ['LG UltraGear 27"', $laptop->id],
        ['Galaxy Tab S9 FE', $smartphone->id],
    ] as [$name, $categoryId]) {
        Product::create([
            'category_id' => $categoryId,
            'name' => $name,
            'price' => 1000000,
            'badge' => 'Pilihan',
            'image_path' => 'products/sample.jpg',
            'image_alt' => $name,
            'is_active' => true,
        ]);
    }
}

it('renders the storefront with ten featured products', function () {
    Storage::fake('public');
    seedPublicHomeCatalog();
    $response = $this->get(route('home'));

    $response->assertStatus(200)
        ->assertSee('10 Produk Tersedia')
        ->assertSee('iPhone 15 128GB')
        ->assertSee('Galaxy Tab S9 FE');

    expect(substr_count($response->getContent(), 'data-product-card'))->toBe(10);
});

it('filters featured products by search term', function () {
    Storage::fake('public');
    seedPublicHomeCatalog();
    $response = $this->get(route('home', ['search' => 'Galaxy Buds']));

    $response->assertStatus(200)
        ->assertSee('Galaxy Buds FE')
        ->assertDontSee('iPhone 15 128GB');
});

it('filters featured products by category', function () {
    Storage::fake('public');
    seedPublicHomeCatalog();
    $response = $this->get(route('home', ['category' => 'Laptop']));

    $response->assertStatus(200)
        ->assertSee('MacBook Air M3')
        ->assertDontSee('iPhone 15 128GB');
});

it('shows an empty state when no products match the search', function () {
    Storage::fake('public');
    seedPublicHomeCatalog();
    $response = $this->get(route('home', ['search' => 'not-a-real-product']));

    $response->assertStatus(200)
        ->assertSee('Produk belum ditemukan')
        ->assertDontSee('iPhone 15 128GB');
});

it('does not show inactive products or products in inactive categories', function () {
    Storage::fake('public');
    $activeCategory = Category::create([
        'name' => 'Aktif',
        'slug' => 'aktif',
        'is_active' => true,
    ]);
    $inactiveCategory = Category::create([
        'name' => 'Nonaktif',
        'slug' => 'nonaktif',
        'is_active' => false,
    ]);
    Product::create([
        'category_id' => $activeCategory->id,
        'name' => 'Produk Tampil',
        'price' => 100000,
        'badge' => 'Pilihan',
        'image_path' => 'products/active.jpg',
        'image_alt' => 'Produk aktif',
        'is_active' => true,
    ]);
    Product::create([
        'category_id' => $activeCategory->id,
        'name' => 'Produk Disembunyikan',
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

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Produk Tampil')
        ->assertDontSee('Produk Disembunyikan')
        ->assertDontSee('Produk Kategori Nonaktif');
});
