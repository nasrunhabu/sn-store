<?php

use App\DemoStore;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('publishes active products created in the admin catalog', function () {
    Storage::fake('local');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), ['name' => 'Kamera'])
        ->assertRedirect(route('admin.categories.index'));

    $category = collect(app(DemoStore::class)->all()['categories'])->firstWhere('name', 'Kamera');

    $this->actingAs($admin)
        ->post(route('admin.products.store'), [
            'name' => 'Kamera Demo',
            'category_id' => $category['id'],
            'price' => 2500000,
            'original_price' => '',
            'badge' => 'Produk uji',
            'image' => 'https://example.com/kamera.jpg',
            'image_alt' => 'Kamera contoh',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.products.index'));

    $this->get(route('home', ['category' => 'Kamera']))
        ->assertOk()
        ->assertSee('Kamera Demo')
        ->assertSee('Rp 2.500.000');

    $product = collect(app(DemoStore::class)->all()['products'])->firstWhere('name', 'Kamera Demo');

    $this->actingAs($admin)
        ->put(route('admin.products.update', $product['id']), [
            'name' => 'Kamera Demo',
            'category_id' => $category['id'],
            'price' => 2500000,
            'original_price' => '',
            'badge' => 'Produk uji',
            'image' => 'https://example.com/kamera.jpg',
            'image_alt' => 'Kamera contoh',
        ])
        ->assertRedirect(route('admin.products.index'));

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Kamera Demo');
});

it('rejects invalid product prices without changing the demo catalog', function () {
    Storage::fake('local');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->from(route('admin.products.create'))
        ->post(route('admin.products.store'), [
            'name' => 'Harga Tidak Valid',
            'category_id' => 'cat-audio',
            'price' => 0,
            'badge' => 'Uji',
            'image' => 'https://example.com/item.jpg',
            'image_alt' => 'Produk uji',
        ])
        ->assertRedirect(route('admin.products.create'))
        ->assertSessionHasErrors('price');

    expect(collect(app(DemoStore::class)->all()['products'])->pluck('name'))
        ->not->toContain('Harga Tidak Valid');
});

it('rejects non-web image URLs for demo products', function () {
    Storage::fake('local');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->from(route('admin.products.create'))
        ->post(route('admin.products.store'), [
            'name' => 'Gambar Tidak Valid',
            'category_id' => 'cat-audio',
            'price' => 500000,
            'badge' => 'Uji',
            'image' => 'javascript:alert(1)',
            'image_alt' => 'Produk uji',
        ])
        ->assertRedirect(route('admin.products.create'))
        ->assertSessionHasErrors('image');

    expect(collect(app(DemoStore::class)->all()['products'])->pluck('name'))
        ->not->toContain('Gambar Tidak Valid');
});

it('prevents deleting a category that still has products', function () {
    Storage::fake('local');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->delete(route('admin.categories.destroy', 'cat-laptop'))
        ->assertSessionHasErrors('category');

    expect(collect(app(DemoStore::class)->all()['categories'])->pluck('id'))
        ->toContain('cat-laptop');
});

it('updates demo transaction statuses and shows only sample users', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['name' => 'Admin Asli', 'email' => 'admin@sn-store.test']);
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->put(route('admin.transactions.update', 'SN-2026-002'), ['status' => 'Status palsu'])
        ->assertSessionHasErrors('status');

    $this->actingAs($admin)
        ->put(route('admin.transactions.update', 'SN-2026-002'), ['status' => 'Dikirim'])
        ->assertRedirect(route('admin.transactions.index'));

    $this->get(route('admin.transactions.index'))
        ->assertOk()
        ->assertSee('value="Dikirim" selected', false);

    $this->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('nadia@example.com')
        ->assertDontSee('admin@sn-store.test')
        ->assertSee('data pengguna contoh');
});

it('renders every demo management page for administrators', function () {
    Storage::fake('local');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee('Daftar kategori');

    $this->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('Katalog demo');

    $this->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('Tambah produk');
});

it('forbids non-admin users from opening the demo catalog', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.products.index'))
        ->assertForbidden();
});
