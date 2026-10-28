<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('creates products with uploaded images and shows active products in the storefront', function () {
    Storage::fake('public');
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $category = Category::create([
        'name' => 'Audio',
        'slug' => 'audio',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.products.store'), [
            'name' => 'Headphone Baru',
            'category_id' => $category->id,
            'price' => 2500000,
            'original_price' => 3000000,
            'badge' => 'Produk uji',
            'image' => UploadedFile::fake()->image('headphone.jpg'),
            'image_alt' => 'Headphone hitam',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('product_alert', [
            'title' => 'Produk berhasil ditambahkan',
            'text' => 'Produk "Headphone Baru" berhasil ditambahkan.',
            'icon' => 'success',
        ]);

    $product = Product::query()->where('name', 'Headphone Baru')->firstOrFail();

    $this->assertModelExists($product);
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'category_id' => $category->id,
        'image_path' => $product->image_path,
        'price' => 2500000,
        'original_price' => 3000000,
        'is_active' => true,
    ]);
    Storage::disk('public')->assertExists($product->image_path);

    $this->get(route('home', ['category' => 'Audio']))
        ->assertOk()
        ->assertSee('Headphone Baru')
        ->assertSee('Rp 2.500.000')
        ->assertSee('Rp 3.000.000');
});

it('rejects invalid product category, price, and image input', function () {
    Storage::fake('public');
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin)
        ->from(route('admin.products.create'))
        ->post(route('admin.products.store'), [
            'name' => 'Produk Tidak Valid',
            'category_id' => 999,
            'price' => 0,
            'badge' => 'Uji',
            'image' => UploadedFile::fake()->create('manual.pdf', 100, 'application/pdf'),
            'image_alt' => 'File yang bukan gambar',
        ])
        ->assertRedirect(route('admin.products.create'))
        ->assertSessionHasErrors(['category_id', 'price', 'image']);

    $this->assertDatabaseCount('products', 0);
    expect(Storage::disk('public')->allFiles('products'))->toBeEmpty();
});

it('keeps the existing image when editing without an upload and replaces it when a new image is uploaded', function () {
    Storage::fake('public');
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $category = Category::create([
        'name' => 'Laptop',
        'slug' => 'laptop',
        'is_active' => true,
    ]);
    $existingImagePath = UploadedFile::fake()->image('old.png')->store('products', 'public');
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Laptop Lama',
        'price' => 5000000,
        'badge' => 'Lama',
        'image_path' => $existingImagePath,
        'image_alt' => 'Laptop lama',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.products.update', $product), [
            'name' => 'Laptop Diperbarui',
            'category_id' => $category->id,
            'price' => 5500000,
            'badge' => 'Terbaru',
            'image_alt' => 'Laptop terbaru',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('product_alert', [
            'title' => 'Produk berhasil diperbarui',
            'text' => 'Produk "Laptop Diperbarui" berhasil diperbarui.',
            'icon' => 'success',
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Laptop Diperbarui',
        'image_path' => $existingImagePath,
    ]);
    Storage::disk('public')->assertExists($existingImagePath);

    $this->put(route('admin.products.update', $product), [
        'name' => 'Laptop Diperbarui',
        'category_id' => $category->id,
        'price' => 5500000,
        'badge' => 'Terbaru',
        'image' => UploadedFile::fake()->image('new.png'),
        'image_alt' => 'Laptop terbaru',
        'is_active' => '1',
    ])->assertRedirect(route('admin.products.index'));

    $product->refresh();
    expect($product->image_path)->not->toBe($existingImagePath);
    Storage::disk('public')->assertExists($product->image_path);
    Storage::disk('public')->assertMissing($existingImagePath);
});

it('deletes a product and its locally stored image', function () {
    Storage::fake('public');
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $category = Category::create([
        'name' => 'Smartphone',
        'slug' => 'smartphone',
        'is_active' => true,
    ]);
    $imagePath = UploadedFile::fake()->image('phone.png')->store('products', 'public');
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Ponsel',
        'price' => 4000000,
        'badge' => 'Baru',
        'image_path' => $imagePath,
        'image_alt' => 'Ponsel hitam',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.products.destroy', $product))
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('product_alert', [
            'title' => 'Produk berhasil dihapus',
            'text' => 'Produk "Ponsel" berhasil dihapus.',
            'icon' => 'success',
        ]);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    Storage::disk('public')->assertMissing($imagePath);
});

it('renders SweetAlert success and delete confirmation on the product table', function () {
    Storage::fake('public');
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $category = Category::create([
        'name' => 'Audio',
        'slug' => 'audio',
        'is_active' => true,
    ]);
    Product::create([
        'category_id' => $category->id,
        'name' => 'Headphone Uji',
        'price' => 100000,
        'badge' => 'Pilihan',
        'image_path' => 'products/sample.jpg',
        'image_alt' => 'Headphone uji',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->withSession(['product_alert' => [
            'title' => 'Produk berhasil diperbarui',
            'text' => 'Produk "Headphone Uji" berhasil diperbarui.',
            'icon' => 'success',
        ]])
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('sweetalert2@11')
        ->assertSee('data-product-delete-form', false)
        ->assertSee('data-product-name="Headphone Uji"', false)
        ->assertSee('window.Swal.fire', false)
        ->assertSee('Produk berhasil diperbarui');
});
