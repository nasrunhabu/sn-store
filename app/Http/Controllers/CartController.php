<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(
        Request $request,
        CartService $cartService,
        ProductService $productService,
    ): View {
        $user = $request->user();
        $items = $cartService->getItems($user);

        return view('pages.public.cart', [
            'categories' => $productService->getPublicCategories(),
            'searchTerm' => '',
            'selectedCategory' => '',
            'cartQuantity' => $cartService->getQuantity($user),
            'items' => $items,
            'total' => $items->sum(fn (CartItem $item): int => $item->product->price * $item->quantity),
        ]);
    }

    public function store(Request $request, Product $product, CartService $cartService): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartService->add($request->user(), $product, (int) $validated['quantity']);

        return to_route('cart.index')->with('status', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $cartItem, CartService $cartService): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartService->updateQuantity(
            $request->user(),
            $cartItem,
            (int) $validated['quantity'],
        );

        return to_route('cart.index')->with('status', 'Jumlah produk berhasil diperbarui.');
    }

    public function destroy(Request $request, CartItem $cartItem, CartService $cartService): RedirectResponse
    {
        $cartService->remove($request->user(), $cartItem);

        return to_route('cart.index')->with('status', 'Produk dihapus dari keranjang.');
    }
}
