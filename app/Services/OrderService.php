<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * @param  array{recipient_name: string, recipient_phone: string, shipping_address: string}  $shipping
     */
    public function checkout(User $user, array $shipping): Order
    {
        return DB::transaction(function () use ($user, $shipping): Order {
            $cartItems = CartItem::query()
                ->where('user_id', $user->getKey())
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang masih kosong.',
                ]);
            }

            $products = Product::query()
                ->whereIn('id', $cartItems->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $categories = Category::query()
                ->whereIn('id', $products->pluck('category_id'))
                ->get()
                ->keyBy('id');
            $lineItems = [];
            $total = 0;

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product_id);
                $category = $product === null ? null : $categories->get($product->category_id);

                if ($product === null || ! $product->is_active || ! $category?->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Salah satu produk di keranjang sudah tidak tersedia.',
                    ]);
                }

                if ($cartItem->quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$product->name} tidak mencukupi. Silakan perbarui keranjang.",
                    ]);
                }

                $subtotal = $product->price * $cartItem->quantity;
                $lineItems[] = [
                    'product' => $product,
                    'product_name' => $product->name,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
                $total += $subtotal;
            }

            $order = Order::create([
                'user_id' => $user->getKey(),
                'order_number' => (string) Str::ulid(),
                'status' => OrderStatus::Pending,
                ...$shipping,
                'total' => $total,
            ]);

            foreach ($lineItems as $lineItem) {
                $lineItem['product']->decrement('stock', $lineItem['quantity']);
                $order->items()->create([
                    'product_id' => $lineItem['product']->getKey(),
                    'product_name' => $lineItem['product_name'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $lineItem['unit_price'],
                    'subtotal' => $lineItem['subtotal'],
                ]);
            }

            $cartItems->each->delete();

            return $order;
        });
    }

    public function markPaid(Order $order, string $paymentReference): bool
    {
        return DB::transaction(function () use ($order, $paymentReference): bool {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if ($lockedOrder->status !== OrderStatus::Pending) {
                return false;
            }

            $lockedOrder->update([
                'status' => OrderStatus::Paid,
                'payment_reference' => $paymentReference,
                'paid_at' => now(),
            ]);

            return true;
        });
    }

    public function markFailed(Order $order): bool
    {
        return DB::transaction(function () use ($order): bool {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if ($lockedOrder->status !== OrderStatus::Pending) {
                return false;
            }

            $items = $lockedOrder->items()
                ->whereNotNull('product_id')
                ->orderBy('product_id')
                ->get();
            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $products->get($item->product_id)?->increment('stock', $item->quantity);
            }

            $lockedOrder->update([
                'status' => OrderStatus::Failed,
                'failed_at' => now(),
            ]);

            return true;
        });
    }

    public function getOrders(int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->with(['user', 'items.product'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function getUserOrders(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $user->getKey())
            ->with(['items.product'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function updateStatus(Order $order, OrderStatus $newStatus): bool
    {
        if ($order->status === $newStatus) {
            return true;
        }

        if ($newStatus === OrderStatus::Paid) {
            return $this->markPaid($order, 'admin-manual:'.(auth()->id() ?? 'system'));
        }

        if ($newStatus === OrderStatus::Failed) {
            return $this->markFailed($order);
        }

        return false;
    }
}
