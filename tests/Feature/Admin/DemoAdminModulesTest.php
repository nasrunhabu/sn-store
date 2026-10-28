<?php

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Storage;

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
    Category::create([
        'name' => 'Audio',
        'slug' => 'audio',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee('Daftar kategori')
        ->assertSee('Audio');

    $this->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('Katalog produk');

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

it('forbids non-admin users from opening the product catalog', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.products.index'))
        ->assertForbidden();
});
