<?php

it('renders the storefront with ten featured products', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200)
        ->assertSee('10 Produk Terbaru')
        ->assertSee('iPhone 15 128GB')
        ->assertSee('Galaxy Tab S9 FE');

    expect(substr_count($response->getContent(), 'data-product-card'))->toBe(10);
});

it('filters featured products by search term', function () {
    $response = $this->get(route('home', ['search' => 'Galaxy Buds']));

    $response->assertStatus(200)
        ->assertSee('Galaxy Buds FE')
        ->assertDontSee('iPhone 15 128GB');
});

it('filters featured products by category', function () {
    $response = $this->get(route('home', ['category' => 'Laptop']));

    $response->assertStatus(200)
        ->assertSee('MacBook Air M3')
        ->assertDontSee('iPhone 15 128GB');
});

it('shows an empty state when no products match the search', function () {
    $response = $this->get(route('home', ['search' => 'not-a-real-product']));

    $response->assertStatus(200)
        ->assertSee('Produk belum ditemukan')
        ->assertDontSee('iPhone 15 128GB');
});
