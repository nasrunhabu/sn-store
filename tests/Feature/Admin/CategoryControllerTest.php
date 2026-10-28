<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

it('creates a database category from the admin form', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin)
        ->get(route('admin.categories.create'))
        ->assertOk()
        ->assertSee('Tambah kategori')
        ->assertDontSee('name="slug"', false)
        ->assertSee('Slug akan dibuat otomatis dari nama kategori.');

    $this->post(route('admin.categories.store'), [
        'name' => 'Kategori Baru',
        'slug' => 'slug-manual-yang-diabaikan',
        'description' => 'Deskripsi kategori baru',
        'is_active' => '1',
    ])
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('category_alert', [
            'title' => 'Kategori berhasil ditambahkan',
            'text' => 'Kategori "Kategori Baru" berhasil ditambahkan.',
            'icon' => 'success',
        ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'Kategori Baru',
        'slug' => 'kategori-baru',
        'description' => 'Deskripsi kategori baru',
        'is_active' => true,
    ]);
});

it('adds a numeric suffix when a generated category slug already exists', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    Category::create([
        'name' => 'Kategori Pertama',
        'slug' => 'slug-terpakai',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Slug Terpakai',
            'description' => '',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', [
        'name' => 'Slug Terpakai',
        'slug' => 'slug-terpakai-2',
    ]);
});

it('rejects invalid category status input', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin)
        ->from(route('admin.categories.create'))
        ->post(route('admin.categories.store'), [
            'name' => 'Kategori Tidak Valid',
            'description' => '',
            'is_active' => 'invalid',
        ])
        ->assertRedirect(route('admin.categories.create'))
        ->assertSessionHasErrors('is_active');

    $this->assertDatabaseCount('categories', 0);
});

it('updates a database category and regenerates the slug from its name', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $category = Category::create([
        'name' => 'Nama Lama',
        'slug' => 'slug-tetap',
        'description' => null,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.categories.edit', $category))
        ->assertOk()
        ->assertSee('Edit kategori')
        ->assertDontSee('name="slug"', false);

    $this->put(route('admin.categories.update', $category), [
        'name' => 'Nama Baru',
        'slug' => 'slug-manual-yang-diabaikan',
        'description' => 'Deskripsi diperbarui',
        'is_active' => '0',
    ])
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('category_alert', [
            'title' => 'Kategori berhasil diperbarui',
            'text' => 'Kategori "Nama Baru" berhasil diperbarui.',
            'icon' => 'success',
        ]);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Nama Baru',
        'slug' => 'nama-baru',
        'description' => 'Deskripsi diperbarui',
        'is_active' => false,
    ]);
});

it('describes a single category name change in the success alert', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $category = Category::create([
        'name' => 'Nama Sebelumnya',
        'slug' => 'nama-sebelumnya',
        'description' => 'Deskripsi tetap',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.categories.update', $category), [
            'name' => 'Nama Baru',
            'description' => 'Deskripsi tetap',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('category_alert', [
            'title' => 'Kategori berhasil diperbarui',
            'text' => 'Nama kategori berhasil diubah dari "Nama Sebelumnya" menjadi "Nama Baru".',
            'icon' => 'success',
        ]);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Nama Baru',
        'slug' => 'nama-baru',
    ]);
});

it('adds a numeric suffix when an updated category slug is already in use', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $category = Category::create([
        'name' => 'Kategori Pertama',
        'slug' => 'kategori-pertama',
        'is_active' => true,
    ]);
    Category::create([
        'name' => 'Slug Terpakai',
        'slug' => 'slug-terpakai',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.categories.update', $category), [
            'name' => 'Slug Terpakai',
            'description' => '',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Slug Terpakai',
        'slug' => 'slug-terpakai-2',
    ]);
});

it('deletes a database category', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $category = Category::create([
        'name' => 'Kategori Dihapus',
        'slug' => 'kategori-dihapus',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('category_alert', [
            'title' => 'Kategori berhasil dihapus',
            'text' => 'Kategori "Kategori Dihapus" berhasil dihapus.',
            'icon' => 'success',
        ]);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

it('prevents deleting a category that is assigned to a product', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $category = Category::create([
        'name' => 'Kategori Terpakai',
        'slug' => 'kategori-terpakai',
        'is_active' => true,
    ]);
    Product::create([
        'category_id' => $category->id,
        'name' => 'Produk Terhubung',
        'price' => 100000,
        'badge' => 'Baru',
        'image_path' => 'products/sample.jpg',
        'image_alt' => 'Produk contoh',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('category_alert', [
            'title' => 'Kategori tidak dapat dihapus',
            'text' => 'Kategori "Kategori Terpakai" masih digunakan oleh produk.',
            'icon' => 'error',
        ]);

    $this->assertModelExists($category);
});
