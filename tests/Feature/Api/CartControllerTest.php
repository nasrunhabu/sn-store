<?php

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

it('returns 401 when a guest tries to view or add to the cart', function () {
    $product = Product::factory()->create(['stock' => 3]);

    $this->getJson(route('api.cart.index'))
        ->assertUnauthorized()
        ->assertExactJson([
            'code' => 401,
            'status' => 'error',
            'message' => 'Login diperlukan untuk mengakses fitur ini.',
            'data' => null,
        ]);
    $this->postJson(route('api.cart.items.store', $product), ['quantity' => 1])
        ->assertUnauthorized();
});

it('adds a product to the signed-in user cart and returns the updated cart', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Audio']);
    $product = Product::factory()->for($category)->create([
        'name' => 'Speaker',
        'price' => 250000,
        'stock' => 5,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson(route('api.cart.items.store', $product), ['quantity' => 2]);

    $response->assertCreated()
        ->assertJsonPath('code', 201)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.total_quantity', 2)
        ->assertJsonPath('data.total_price', 500000)
        ->assertJsonPath('data.items.0.quantity', 2)
        ->assertJsonPath('data.items.0.subtotal', 500000)
        ->assertJsonPath('data.items.0.product.category', 'Audio');

    $this->postJson(route('api.cart.items.store', $product), ['quantity' => 1])
        ->assertCreated()
        ->assertJsonPath('data.total_quantity', 3)
        ->assertJsonPath('data.total_price', 750000)
        ->assertJsonPath('data.items.0.quantity', 3);

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);
});

it('shows only the signed-in user cart items with totals', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownedProduct = Product::factory()->create(['price' => 100000]);
    $otherProduct = Product::factory()->create(['price' => 80000]);
    $ownedItem = CartItem::factory()->for($user)->for($ownedProduct)->create(['quantity' => 3]);
    CartItem::factory()->for($otherUser)->for($otherProduct)->create(['quantity' => 7]);

    $this->actingAs($user, 'sanctum')
        ->getJson(route('api.cart.index'))
        ->assertOk()
        ->assertExactJson([
            'code' => 200,
            'status' => 'success',
            'message' => 'Data keranjang berhasil dimuat.',
            'data' => [
                'items' => [
                    [
                        'id' => $ownedItem->id,
                        'quantity' => 3,
                        'unit_price' => 100000,
                        'subtotal' => 300000,
                        'product' => [
                            'id' => $ownedProduct->id,
                            'name' => $ownedProduct->name,
                            'price' => 100000,
                            'stock' => 10,
                            'original_price' => null,
                            'rating' => '5.0',
                            'reviews' => 0,
                            'badge' => 'Pilihan',
                            'image_path' => 'products/sample.jpg',
                            'image_alt' => $ownedProduct->image_alt,
                            'is_active' => true,
                            'created_at' => $ownedProduct->created_at?->toISOString(),
                            'updated_at' => $ownedProduct->updated_at?->toISOString(),
                            'category' => $ownedProduct->category->name,
                        ],
                    ],
                ],
                'total_quantity' => 3,
                'total_price' => 300000,
            ],
        ]);
});

it('rejects invalid quantities and products without sufficient stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 1]);

    $this->actingAs($user, 'sanctum')
        ->postJson(route('api.cart.items.store', $product), ['quantity' => 0])
        ->assertUnprocessable()
        ->assertJsonPath('code', 422)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('data.errors.quantity.0', 'The quantity field must be at least 1.');

    $this->postJson(route('api.cart.items.store', $product), ['quantity' => 2])
        ->assertUnprocessable()
        ->assertJsonPath('code', 422)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('data.errors.product.0', 'Stok produk tidak mencukupi.');

    $this->assertDatabaseMissing('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});
