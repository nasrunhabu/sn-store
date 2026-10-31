<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.midtrans.server_key', 'test-midtrans-server-key');
    config()->set('services.midtrans.client_key', 'test-midtrans-client-key');
    Http::preventStrayRequests();
});

it('generates a snap token from midtrans api and saves it to the order', function () {
    Http::fake([
        'app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['token' => 'snap-token-abc-123'], 200),
    ]);

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 150000]);
    $order = Order::factory()->for($user)->create(['total' => 150000, 'snap_token' => null]);
    OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 1,
        'unit_price' => 150000,
        'subtotal' => 150000,
    ]);

    $service = app(MidtransService::class);
    $token = $service->getOrCreateSnapToken($order);

    expect($token)->toBe('snap-token-abc-123');
    expect($order->fresh()->snap_token)->toBe('snap-token-abc-123');

    // Calling again returns cached snap token without hitting HTTP API
    Http::fake([
        'app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['token' => 'different-token'], 200),
    ]);
    expect($service->getOrCreateSnapToken($order->fresh()))->toBe('snap-token-abc-123');
});

it('does not request a snap token when payment credentials are incomplete', function () {
    config()->set('services.midtrans.server_key', '');
    config()->set('services.midtrans.client_key', '');

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 150000]);
    $order = Order::factory()->for($user)->create([
        'total' => 150000,
        'snap_token' => null,
    ]);
    OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 1,
        'unit_price' => 150000,
        'subtotal' => 150000,
    ]);

    expect(app(MidtransService::class)->getOrCreateSnapToken($order))->toBeNull();
    Http::assertNothingSent();
});

it('marks order as paid upon receiving settlement notification from midtrans', function () {
    $order = Order::factory()->create([
        'order_number' => 'ORDER-MIDTRANS-SETTLED',
        'status' => OrderStatus::Pending,
        'total' => 200000,
    ]);

    $serverKey = config('services.midtrans.server_key');
    $statusCode = '200';
    $grossAmount = '200000.00';
    $signatureKey = hash('sha512', $order->order_number.$statusCode.$grossAmount.$serverKey);

    $payload = [
        'order_id' => $order->order_number,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signatureKey,
        'transaction_status' => 'settlement',
        'transaction_id' => 'midtrans-trx-999',
        'payment_type' => 'qris',
    ];

    $response = $this->postJson(route('midtrans.notification'), $payload);

    $response->assertOk()
        ->assertJson(['status' => 'ok']);
    $this->postJson(route('midtrans.notification'), $payload)
        ->assertOk()
        ->assertJson(['status' => 'ok']);

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Paid);
    expect($order->payment_reference)->toContain('midtrans:qris:midtrans-trx-999');
    expect($order->paid_at)->not->toBeNull();
});

it('marks order as paid upon credit card capture with accept fraud status', function () {
    $order = Order::factory()->create([
        'order_number' => 'ORDER-MIDTRANS-CC',
        'status' => OrderStatus::Pending,
        'total' => 500000,
    ]);

    $serverKey = config('services.midtrans.server_key');
    $statusCode = '200';
    $grossAmount = '500000.00';
    $signatureKey = hash('sha512', $order->order_number.$statusCode.$grossAmount.$serverKey);

    $payload = [
        'order_id' => $order->order_number,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signatureKey,
        'transaction_status' => 'capture',
        'fraud_status' => 'accept',
        'transaction_id' => 'midtrans-trx-cc-888',
        'payment_type' => 'credit_card',
    ];

    $response = $this->postJson(route('midtrans.notification'), $payload);

    $response->assertOk()
        ->assertJson(['status' => 'ok']);

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Paid);
});

it('marks order as failed and restores stock upon receiving expire notification', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $order = Order::factory()->create([
        'order_number' => 'ORDER-MIDTRANS-EXPIRE',
        'status' => OrderStatus::Pending,
        'total' => 100000,
    ]);
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 2]);

    $serverKey = config('services.midtrans.server_key');
    $statusCode = '202';
    $grossAmount = '100000.00';
    $signatureKey = hash('sha512', $order->order_number.$statusCode.$grossAmount.$serverKey);

    $payload = [
        'order_id' => $order->order_number,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signatureKey,
        'transaction_status' => 'expire',
        'transaction_id' => 'midtrans-trx-exp',
        'payment_type' => 'bank_transfer',
    ];

    $response = $this->postJson(route('midtrans.notification'), $payload);

    $response->assertOk();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Failed);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 7]);
});

it('rejects midtrans notification with invalid signature', function () {
    $order = Order::factory()->create([
        'order_number' => 'ORDER-INVALID-SIG',
        'status' => OrderStatus::Pending,
    ]);

    $payload = [
        'order_id' => $order->order_number,
        'status_code' => '200',
        'gross_amount' => '100000.00',
        'signature_key' => 'invalid-signature-hash',
        'transaction_status' => 'settlement',
    ];

    $response = $this->postJson(route('midtrans.notification'), $payload);

    $response->assertStatus(400)
        ->assertJson(['status' => 'error']);

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});

it('allows customer to manually check payment status', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create([
        'order_number' => 'ORDER-CHECK-STATUS',
        'status' => OrderStatus::Pending,
    ]);

    Http::fake([
        '*/v2/ORDER-CHECK-STATUS/status' => Http::response([
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-manual-check-1',
            'payment_type' => 'gopay',
            'order_id' => 'ORDER-CHECK-STATUS',
            'gross_amount' => '100000.00',
        ], 200),
    ]);

    $this->actingAs($user)
        ->post(route('orders.check-status', $order))
        ->assertRedirect(route('orders.show', $order))
        ->assertSessionHas('status', 'Pembayaran berhasil dikonfirmasi!');

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
});

it('rejects a validly signed notification when the amount differs from the order total', function () {
    $order = Order::factory()->create([
        'order_number' => 'ORDER-MIDTRANS-WRONG-AMOUNT',
        'status' => OrderStatus::Pending,
        'total' => 200000,
    ]);

    $statusCode = '200';
    $grossAmount = '100000.00';
    $signatureKey = hash('sha512', $order->order_number.$statusCode.$grossAmount.config('services.midtrans.server_key'));

    $this->postJson(route('midtrans.notification'), [
        'order_id' => $order->order_number,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signatureKey,
        'transaction_status' => 'settlement',
    ])->assertStatus(400);

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});

it('does not apply a status response for a different order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create([
        'order_number' => 'ORDER-CHECK-WRONG-ORDER',
        'status' => OrderStatus::Pending,
        'total' => 100000,
    ]);

    Http::fake([
        'api.sandbox.midtrans.com/v2/ORDER-CHECK-WRONG-ORDER/status' => Http::response([
            'order_id' => 'ORDER-OTHER',
            'gross_amount' => '100000.00',
            'transaction_status' => 'settlement',
        ], 200),
    ]);

    $this->actingAs($user)
        ->post(route('orders.check-status', $order))
        ->assertRedirect(route('orders.show', $order))
        ->assertSessionHas('status', 'Status pembayaran belum dapat dikonfirmasi. Silakan coba kembali.');

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});

it('does not apply a status response with a different amount', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create([
        'order_number' => 'ORDER-CHECK-WRONG-AMOUNT',
        'status' => OrderStatus::Pending,
        'total' => 100000,
    ]);

    Http::fake([
        'api.sandbox.midtrans.com/v2/ORDER-CHECK-WRONG-AMOUNT/status' => Http::response([
            'order_id' => 'ORDER-CHECK-WRONG-AMOUNT',
            'gross_amount' => '90000.00',
            'transaction_status' => 'settlement',
        ], 200),
    ]);

    $this->actingAs($user)
        ->post(route('orders.check-status', $order))
        ->assertRedirect(route('orders.show', $order))
        ->assertSessionHas('status', 'Status pembayaran belum dapat dikonfirmasi. Silakan coba kembali.');

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});
