<?php

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

it('redirects guests to login before they can use the cart or checkout', function () {
    $product = Product::factory()->create(['stock' => 3]);

    $this->post(route('cart.items.store', $product), ['quantity' => 1])
        ->assertRedirect(route('login'));
    $this->get(route('cart.index'))
        ->assertRedirect(route('login'));
    $this->get(route('checkout.create'))
        ->assertRedirect(route('login'));
});

it('adds available products to the signed-in user cart and increments the cart quantity', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);

    $this->actingAs($user)
        ->post(route('cart.items.store', $product), ['quantity' => 2])
        ->assertRedirect(route('cart.index'));
    $this->post(route('cart.items.store', $product), ['quantity' => 1])
        ->assertRedirect(route('cart.index'));

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);
});

it('does not add products when stock is empty or the cart quantity exceeds stock', function () {
    $user = User::factory()->create();
    $emptyProduct = Product::factory()->create(['stock' => 0]);
    $limitedProduct = Product::factory()->create(['stock' => 2]);

    $this->actingAs($user)
        ->from(route('products.index'))
        ->post(route('cart.items.store', $emptyProduct), ['quantity' => 1])
        ->assertRedirect(route('products.index'))
        ->assertSessionHasErrors('product');
    $this->post(route('cart.items.store', $limitedProduct), ['quantity' => 2])
        ->assertRedirect(route('cart.index'));
    $this->from(route('products.index'))
        ->post(route('cart.items.store', $limitedProduct), ['quantity' => 1])
        ->assertRedirect(route('products.index'))
        ->assertSessionHasErrors('product');

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'product_id' => $limitedProduct->id,
        'quantity' => 2,
    ]);
    $this->assertDatabaseMissing('cart_items', ['product_id' => $emptyProduct->id]);
});

it('prevents a user from changing or removing another user cart item', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $cartItem = CartItem::factory()->for($owner)->create();

    $this->actingAs($otherUser)
        ->patch(route('cart.items.update', $cartItem), ['quantity' => 1])
        ->assertNotFound();
    $this->delete(route('cart.items.destroy', $cartItem))
        ->assertNotFound();

    $this->assertModelExists($cartItem);
});
