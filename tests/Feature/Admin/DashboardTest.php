<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Storage;

it('redirects guests to login when they open the admin dashboard', function () {
    Storage::fake('local');
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

it('forbids non-admin users from opening the admin dashboard', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertForbidden();
});

it('renders the admin dashboard with database category and product totals', function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create(['name' => 'Nadia Store']);
    $user->assignRole('Admin');
    User::factory()->count(2)->create();
    $category = Category::create([
        'name' => 'Kategori Satu',
        'slug' => 'kategori-satu',
        'is_active' => true,
    ]);
    Category::create([
        'name' => 'Kategori Dua',
        'slug' => 'kategori-dua',
        'is_active' => true,
    ]);
    Product::create([
        'category_id' => $category->id,
        'name' => 'Produk Database',
        'price' => 100000,
        'badge' => 'Baru',
        'image_path' => 'products/sample.jpg',
        'image_alt' => 'Produk database',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200)
        ->assertSee('Selamat datang, Nadia Store.')
        ->assertSee('Kategori</p>', false)
        ->assertSee('>1<', false)
        ->assertSee('Transaksi')
        ->assertSee('Pengguna')
        ->assertSee('>3<', false)
        ->assertSee('Transaksi masih menggunakan data demo.');
});

it('shows the dashboard link to admins on the public page', function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Admin');

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertSee('Dashboard')
        ->assertSee(route('dashboard'))
        ->assertDontSee('href="'.route('register').'"')
        ->assertSee('Akun saya')
        ->assertSee(route('profile.edit'))
        ->assertSee(route('logout'))
        ->assertSee('Pengaturan profil')
        ->assertSee('Keluar');
});

it('does not show the dashboard link to regular users on the public page', function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Customer');

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertDontSee('Dashboard')
        ->assertDontSee('href="'.route('dashboard').'"')
        ->assertDontSee('href="'.route('register').'"')
        ->assertSee('Akun saya')
        ->assertSee(route('profile.edit'))
        ->assertSee(route('logout'))
        ->assertSee('Pengaturan profil')
        ->assertSee('Keluar');
});

it('shows a login link to guests on the public page', function () {
    Storage::fake('local');
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Masuk')
        ->assertSee(route('login'))
        ->assertSee('Daftar')
        ->assertSee(route('register'));
});
