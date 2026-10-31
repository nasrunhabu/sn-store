<?php

use App\Enums\OrderStatus;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.midtrans.server_key', 'test-midtrans-server-key');
    config()->set('services.midtrans.client_key', 'test-midtrans-client-key');
    Http::preventStrayRequests();
});

it('creates a pending order, reserves stock, and renders checkout without public navigation', function () {
    Http::fake([
        'app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['token' => 'mock-snap-token-123'], 200),
    ]);

    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Laptop Checkout',
        'price' => 5000000,
        'stock' => 4,
    ]);
    CartItem::factory()->for($user)->for($product)->create(['quantity' => 2]);

    $this->actingAs($user)
        ->get(route('checkout.create'))
        ->assertOk()
        ->assertSee('Checkout aman')
        ->assertSee('Nama penerima')
        ->assertSee('Alamat lengkap')
        ->assertDontSee('Gratis ongkir');

    $response = $this->post(route('checkout.store'), [
        'recipient_name' => 'Pembeli SN',
        'recipient_phone' => '081234567890',
        'shipping_address' => 'Jl. Merdeka No. 1, Jakarta',
    ]);

    $order = Order::query()->with('items')->firstOrFail();
    $response->assertRedirect(route('orders.show', $order));

    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->total)->toBe(10000000);
    expect($order->items)->toHaveCount(1);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 2]);
    $this->assertDatabaseMissing('cart_items', ['user_id' => $user->id]);
    $this->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('menunggu pembayaran')
        ->assertSee('Bayar Sekarang via Midtrans')
        ->assertSee('mock-snap-token-123');
});

it('keeps the cart and stock unchanged when stock falls short at checkout', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 2]);
    $cartItem = CartItem::factory()->for($user)->for($product)->create(['quantity' => 2]);
    $product->update(['stock' => 1]);

    $this->actingAs($user)
        ->from(route('checkout.create'))
        ->post(route('checkout.store'), [
            'recipient_name' => 'Pembeli SN',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Merdeka No. 1',
        ])
        ->assertRedirect(route('checkout.create'))
        ->assertSessionHasErrors('cart');

    $this->assertDatabaseCount('orders', 0);
    $this->assertModelExists($cartItem);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
});

it('validates shipping details before creating an order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 2]);
    $cartItem = CartItem::factory()->for($user)->for($product)->create();

    $this->actingAs($user)
        ->from(route('checkout.create'))
        ->post(route('checkout.store'), [])
        ->assertRedirect(route('checkout.create'))
        ->assertSessionHasErrors(['recipient_name', 'recipient_phone', 'shipping_address']);

    $this->assertDatabaseCount('orders', 0);
    $this->assertModelExists($cartItem);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 2]);
});

it('hides another users order details', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $order = Order::factory()->for($owner)->create();

    $this->actingAs($otherUser)
        ->get(route('orders.show', $order))
        ->assertNotFound();
});

it('explains when Midtrans credentials are unavailable on the payment page', function () {
    config()->set('services.midtrans.server_key', '');
    config()->set('services.midtrans.client_key', '');

    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Pembayaran belum dikonfigurasi.')
        ->assertDontSee('Token pembayaran sedang disiapkan.');

    Http::assertNothingSent();
});

it('returns reserved stock only after Midtrans confirms a pending order cancellation', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 3]);
    $order = Order::factory()->for($user)->create([
        'order_number' => 'ORDER-CANCEL-CONFIRMED',
        'total' => $product->price * 2,
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'unit_price' => $product->price,
        'subtotal' => $product->price * 2,
    ]);

    Http::fake([
        'api.sandbox.midtrans.com/v2/ORDER-CANCEL-CONFIRMED/cancel' => Http::response([
            'transaction_status' => 'cancel',
        ], 200),
        'api.sandbox.midtrans.com/v2/ORDER-CANCEL-CONFIRMED/status' => Http::response([
            'order_id' => 'ORDER-CANCEL-CONFIRMED',
            'gross_amount' => number_format($order->total, 2, '.', ''),
            'transaction_status' => 'cancel',
        ], 200),
    ]);

    $this->actingAs($user)
        ->post(route('orders.cancel', $order))
        ->assertRedirect(route('orders.show', $order))
        ->assertSessionHas('status', 'Pesanan dibatalkan dan stok telah dikembalikan.');
    $this->post(route('orders.cancel', $order))
        ->assertRedirect(route('orders.show', $order));

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => OrderStatus::Failed->value,
    ]);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
});

it('keeps the order and stock unchanged when Midtrans has not confirmed cancellation', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 3]);
    $order = Order::factory()->for($user)->create([
        'order_number' => 'ORDER-CANCEL-PENDING',
        'total' => $product->price * 2,
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'unit_price' => $product->price,
        'subtotal' => $product->price * 2,
    ]);

    Http::fake([
        'api.sandbox.midtrans.com/v2/ORDER-CANCEL-PENDING/cancel' => Http::response([], 503),
        'api.sandbox.midtrans.com/v2/ORDER-CANCEL-PENDING/status' => Http::response([
            'order_id' => 'ORDER-CANCEL-PENDING',
            'gross_amount' => number_format($order->total, 2, '.', ''),
            'transaction_status' => 'pending',
        ], 200),
    ]);

    $this->actingAs($user)
        ->post(route('orders.cancel', $order))
        ->assertRedirect(route('orders.show', $order))
        ->assertSessionHas('status', 'Pembatalan belum dikonfirmasi Midtrans. Stok tetap ditahan sementara; silakan cek kembali.');

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
});

it('keeps a successful payment when Midtrans rejects a cancellation after payment', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 3]);
    $order = Order::factory()->for($user)->create([
        'order_number' => 'ORDER-CANCEL-PAID',
        'total' => $product->price * 2,
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'unit_price' => $product->price,
        'subtotal' => $product->price * 2,
    ]);

    Http::fake([
        'api.sandbox.midtrans.com/v2/ORDER-CANCEL-PAID/cancel' => Http::response([], 409),
        'api.sandbox.midtrans.com/v2/ORDER-CANCEL-PAID/status' => Http::response([
            'order_id' => 'ORDER-CANCEL-PAID',
            'gross_amount' => number_format($order->total, 2, '.', ''),
            'transaction_status' => 'settlement',
            'transaction_id' => 'settled-before-cancel',
            'payment_type' => 'qris',
        ], 200),
    ]);

    $this->actingAs($user)
        ->post(route('orders.cancel', $order))
        ->assertRedirect(route('orders.show', $order))
        ->assertSessionHas('status', 'Pembayaran sudah berhasil dikonfirmasi. Pesanan tidak dibatalkan.');

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
});

it('displays the user order history on transactions index', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $userOrder = Order::factory()->for($user)->create([
        'order_number' => 'ORD-USER-001',
        'status' => OrderStatus::Pending,
        'total' => 250000,
    ]);
    $userOrder->items()->create([
        'product_name' => 'Mouse Gaming SN',
        'quantity' => 1,
        'unit_price' => 250000,
        'subtotal' => 250000,
    ]);

    $otherOrder = Order::factory()->for($otherUser)->create([
        'order_number' => 'ORD-OTHER-999',
    ]);

    $this->actingAs($user)
        ->get(route('orders.index'))
        ->assertOk()
        ->assertSee('Pesanan Saya')
        ->assertSee('ORD-USER-001')
        ->assertSee('Mouse Gaming SN')
        ->assertSee('Rp 250.000')
        ->assertDontSee('ORD-OTHER-999');
});
