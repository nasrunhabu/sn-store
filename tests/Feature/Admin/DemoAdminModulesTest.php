<?php

use App\DemoStore;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Storage;

it('publishes active products created in the admin catalog', function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $category = collect(app(DemoStore::class)->all()['categories'])->firstWhere('id', 'cat-audio');

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

    $this->get(route('home', ['category' => $category['name']]))
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
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

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
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

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

it('updates demo transaction statuses and shows only sample users', function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create(['name' => 'Admin Asli', 'email' => 'admin@sn-store.test']);
    $admin->assignRole('Admin');

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

it('renders admin category and product pages', function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee('Daftar kategori')
        ->assertSee('Belum ada kategori di database.');

    $this->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('Katalog demo');

    $this->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('Tambah produk');
});

it('lists categories from the database without demo category controls', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $category = Category::create([
        'name' => 'Kategori Database',
        'slug' => 'kategori-database',
        'description' => 'Deskripsi kategori dari database',
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee('1 kategori tersimpan di database')
        ->assertSee('Kategori Database')
        ->assertSee('kategori-database')
        ->assertSee('Deskripsi kategori dari database')
        ->assertSee('Nonaktif')
        ->assertSee('Tambah kategori')
        ->assertSee(route('admin.categories.edit', $category))
        ->assertSee('data-category-delete-form', false)
        ->assertSee('window.Swal.fire', false);

    $this->assertModelExists($category);
});

it('forbids non-admin users from opening the demo catalog', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.products.index'))
        ->assertForbidden();
});
