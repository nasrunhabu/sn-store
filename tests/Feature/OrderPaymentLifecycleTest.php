<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderService;

it('marks a pending order paid without reducing reserved stock a second time', function () {
    $product = Product::factory()->create(['stock' => 3]);
    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 2,
        'unit_price' => $product->price,
        'subtotal' => $product->price * 2,
    ]);
    $orderService = app(OrderService::class);

    expect($orderService->markPaid($order, 'gateway-ref-001'))->toBeTrue();
    expect($orderService->markPaid($order, 'gateway-ref-001'))->toBeFalse();
    expect($orderService->markFailed($order))->toBeFalse();

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => OrderStatus::Paid->value,
        'payment_reference' => 'gateway-ref-001',
    ]);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
});

it('restores reserved stock only once when an order payment fails', function () {
    $product = Product::factory()->create(['stock' => 3]);
    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 2]);
    $orderService = app(OrderService::class);

    expect($orderService->markFailed($order))->toBeTrue();
    expect($orderService->markFailed($order))->toBeFalse();
    expect($orderService->markPaid($order, 'late-gateway-ref'))->toBeFalse();

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => OrderStatus::Failed->value,
    ]);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
});

it('does not reopen terminal orders or adjust stock a second time through admin status updates', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $order = Order::factory()->create(['status' => OrderStatus::Failed]);
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 2]);

    expect(app(OrderService::class)->updateStatus($order, OrderStatus::Paid))->toBeFalse();
    expect(app(OrderService::class)->updateStatus($order, OrderStatus::Pending))->toBeFalse();

    expect($order->fresh()->status)->toBe(OrderStatus::Failed);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
});
