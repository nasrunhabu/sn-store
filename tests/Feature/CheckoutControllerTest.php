<?php

use App\Enums\OrderStatus;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\Village;

beforeEach(function () {
    config()->set('services.midtrans.server_key', 'test-midtrans-server-key');
    config()->set('services.midtrans.client_key', 'test-midtrans-client-key');
    config()->set('services.rajaongkir.api_key', 'test-rajaongkir-key');
    Http::preventStrayRequests();
    Http::fake([
        'rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response(['data' => [[
            'id' => 555,
            'subdistrict_name' => 'DESA A1',
            'district_name' => 'KECAMATAN A1',
            'city_name' => 'KOTA A1',
        ]]], 200),
        'rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response(['data' => [
            ['name' => 'Jalur Nugraha Ekakurir (JNE)', 'code' => 'jne', 'service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => 20000, 'etd' => '2 day'],
            ['name' => 'SiCepat', 'code' => 'sicepat', 'service' => 'BEST', 'description' => 'Besok Sampai Tujuan', 'cost' => 30000, 'etd' => '1 day'],
        ]], 200),
    ]);
});

/**
 * @return array<string, string>
 */
function regionFixture(): array
{
    Province::query()->updateOrCreate(['code' => '11'], ['name' => 'Provinsi A']);
    Province::query()->updateOrCreate(['code' => '12'], ['name' => 'Provinsi B']);
    City::query()->updateOrCreate(['code' => '1101'], ['province_code' => '11', 'name' => 'Kota A1']);
    City::query()->updateOrCreate(['code' => '1201'], ['province_code' => '12', 'name' => 'Kota B1']);
    District::query()->updateOrCreate(['code' => '1101010'], ['city_code' => '1101', 'name' => 'Kecamatan A1']);
    Village::query()->updateOrCreate(['code' => '1101010001'], ['district_code' => '1101010', 'name' => 'Desa A1', 'meta' => ['pos' => '90111']]);

    return [
        'province_code' => '11',
        'city_code' => '1101',
        'district_code' => '1101010',
        'village_code' => '1101010001',
        'postal_code' => '90111',
    ];
}

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
        ...regionFixture(),
        'shipping_option' => 'jne|REG',
    ]);

    $order = Order::query()->with('items')->firstOrFail();
    $response->assertRedirect(route('orders.show', $order));

    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->total)->toBe(10020000)
        ->and($order->shipping_cost)->toBe(20000)
        ->and($order->shipping_label)->toBe('JNE REG')
        ->and($order->shipping_weight)->toBe(2000);
    expect($order->items)->toHaveCount(1);
    expect($order->shipping_province)->toBe('Provinsi A')
        ->and($order->shipping_village)->toBe('Desa A1')
        ->and($order->shipping_postal_code)->toBe('90111')
        ->and($order->full_shipping_address)->toBe('Jl. Merdeka No. 1, Jakarta, Desa A1, Kecamatan A1, Kota A1, Provinsi A, 90111');
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
            ...regionFixture(),
            'shipping_option' => 'jne|REG',
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
        ->assertSessionHasErrors(['recipient_name', 'recipient_phone', 'province_code', 'city_code', 'district_code', 'village_code', 'postal_code', 'shipping_address', 'shipping_option']);

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

it('lists regions level by level for the signed-in user only', function () {
    regionFixture();
    $user = User::factory()->create();

    $this->getJson(route('regions.cities', '11'))->assertUnauthorized();

    $this->actingAs($user)->getJson(route('regions.cities', '11'))
        ->assertOk()
        ->assertExactJson([['code' => '1101', 'name' => 'Kota A1']]);
    $this->getJson(route('regions.districts', '1101'))
        ->assertExactJson([['code' => '1101010', 'name' => 'Kecamatan A1']]);
    $this->getJson(route('regions.villages', '1101010'))
        ->assertExactJson([['code' => '1101010001', 'name' => 'Desa A1', 'postal_code' => '90111']]);
    $this->getJson(route('regions.cities', '12'))->assertExactJson([['code' => '1201', 'name' => 'Kota B1']]);
});

it('rejects a region that does not belong to its parent', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 2]);
    CartItem::factory()->for($user)->for($product)->create();

    $this->actingAs($user)
        ->from(route('checkout.create'))
        ->post(route('checkout.store'), [
            'recipient_name' => 'Pembeli SN',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Merdeka No. 1',
            ...regionFixture(),
            'city_code' => '1201',
            'shipping_option' => 'jne|REG',
        ])
        ->assertSessionHasErrors('city_code');

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 2]);
});

it('returns shipping options for a village for the signed-in user only', function () {
    $fixture = regionFixture();
    $user = User::factory()->create();
    CartItem::factory()->for($user)->create(['quantity' => 2]);

    $this->getJson(route('checkout.shipping-cost', ['village_code' => $fixture['village_code']]))->assertUnauthorized();

    $this->actingAs($user)
        ->getJson(route('checkout.shipping-cost', ['village_code' => $fixture['village_code']]))
        ->assertOk()
        ->assertJsonPath('weight', 2000)
        ->assertJsonPath('options.0.key', 'jne|REG')
        ->assertJsonPath('options.0.cost', 20000);
});

it('reports a readable error when the shipping service fails', function () {
    $fixture = regionFixture();
    $user = User::factory()->create();
    CartItem::factory()->for($user)->create();
    Cache::flush();
    Http::swap(new HttpFactory);
    Http::preventStrayRequests();
    Http::fake([
        'rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response(['data' => [[
            'id' => 555,
            'subdistrict_name' => 'DESA A1',
            'district_name' => 'KECAMATAN A1',
            'city_name' => 'KOTA A1',
        ]]], 200),
        'rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([], 500),
    ]);

    $this->actingAs($user)
        ->getJson(route('checkout.shipping-cost', ['village_code' => $fixture['village_code']]))
        ->assertStatus(422)
        ->assertJsonStructure(['message']);
});

it('rejects a shipping option that was not quoted and creates no order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 3]);
    CartItem::factory()->for($user)->for($product)->create();

    $this->actingAs($user)
        ->from(route('checkout.create'))
        ->post(route('checkout.store'), [
            'recipient_name' => 'Pembeli SN',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Merdeka No. 1',
            ...regionFixture(),
            'shipping_option' => 'jne|FAKE',
        ])
        ->assertSessionHasErrors('shipping_option');

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
});

it('sends shipping as a Midtrans item so the totals match', function () {
    Http::fake(['app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['token' => 'tok'], 200)]);
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100000, 'stock' => 3]);
    CartItem::factory()->for($user)->for($product)->create(['quantity' => 1]);

    $this->actingAs($user)->post(route('checkout.store'), [
        'recipient_name' => 'Pembeli SN',
        'recipient_phone' => '081234567890',
        'shipping_address' => 'Jl. Merdeka No. 1',
        ...regionFixture(),
        'shipping_option' => 'sicepat|BEST',
    ])->assertRedirect();

    $this->get(route('orders.show', Order::query()->firstOrFail()))->assertOk();

    Http::assertSent(function ($request): bool {
        if (! str_contains($request->url(), 'snap/v1/transactions')) {
            return false;
        }
        $data = $request->data();

        return $data['transaction_details']['gross_amount'] === 130000
            && collect($data['item_details'])->contains(fn ($item) => $item['id'] === 'SHIPPING' && $item['price'] === 30000);
    });
});
