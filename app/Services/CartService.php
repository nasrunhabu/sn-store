<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function add(User $user, Product $product, int $quantity): void
    {
        DB::transaction(function () use ($user, $product, $quantity): void {
            $lockedProduct = Product::query()
                ->with('category')
                ->lockForUpdate()
                ->findOrFail($product->getKey());

            if (! $lockedProduct->is_active || ! $lockedProduct->category->is_active) {
                throw ValidationException::withMessages([
                    'product' => 'Produk sudah tidak tersedia.',
                ]);
            }

            $cartItem = CartItem::query()
                ->where('user_id', $user->getKey())
                ->where('product_id', $lockedProduct->getKey())
                ->lockForUpdate()
                ->first();
            $newQuantity = ($cartItem?->quantity ?? 0) + $quantity;

            if ($lockedProduct->stock < 1 || $newQuantity > $lockedProduct->stock) {
                throw ValidationException::withMessages([
                    'product' => 'Stok produk tidak mencukupi.',
                ]);
            }

            if ($cartItem === null) {
                CartItem::create([
                    'user_id' => $user->getKey(),
                    'product_id' => $lockedProduct->getKey(),
                    'quantity' => $quantity,
                ]);

                return;
            }

            $cartItem->update(['quantity' => $newQuantity]);
        });
    }

    public function updateQuantity(User $user, CartItem $cartItem, int $quantity): void
    {
        DB::transaction(function () use ($user, $cartItem, $quantity): void {
            $lockedCartItem = CartItem::query()
                ->where('user_id', $user->getKey())
                ->lockForUpdate()
                ->findOrFail($cartItem->getKey());
            $product = Product::query()
                ->with('category')
                ->lockForUpdate()
                ->findOrFail($lockedCartItem->product_id);

            if (
                ! $product->is_active
                || ! $product->category->is_active
                || $quantity > $product->stock
            ) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah melebihi stok produk yang tersedia.',
                ]);
            }

            $lockedCartItem->update(['quantity' => $quantity]);
        });
    }

    public function remove(User $user, CartItem $cartItem): void
    {
        CartItem::query()
            ->where('user_id', $user->getKey())
            ->findOrFail($cartItem->getKey())
            ->delete();
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getItems(User $user): Collection
    {
        return $user->cartItems()
            ->with(['product.category'])
            ->orderBy('id')
            ->get();
    }

    public function getQuantity(User $user): int
    {
        return (int) $user->cartItems()->sum('quantity');
    }
}
