<?php

use App\Models\User;

it('redirects guests to login when they open the admin dashboard', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

it('forbids non-admin users from opening the admin dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertForbidden();
});

it('renders the admin dashboard with the registered user total', function () {
    $user = User::factory()->create(['name' => 'Nadia Store']);
    $user->forceFill(['is_admin' => true])->save();
    User::factory()->count(2)->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200)
        ->assertSee('Selamat datang, Nadia Store.')
        ->assertSee('Kategori')
        ->assertSee('Produk')
        ->assertSee('Transaksi')
        ->assertSee('Pengguna terdaftar')
        ->assertSee('3');
});

it('shows the dashboard link to admins on the public page', function () {
    $user = User::factory()->create();
    $user->forceFill(['is_admin' => true])->save();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertSee('Dashboard')
        ->assertSee(route('dashboard'))
        ->assertSee('Akun saya')
        ->assertSee(route('profile.edit'))
        ->assertSee(route('logout'))
        ->assertSee('Pengaturan profil')
        ->assertSee('Keluar');
});

it('does not show the dashboard link to regular users on the public page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertDontSee('Dashboard')
        ->assertDontSee('href="'.route('dashboard').'"')
        ->assertSee('Akun saya')
        ->assertSee(route('profile.edit'))
        ->assertSee(route('logout'))
        ->assertSee('Pengaturan profil')
        ->assertSee('Keluar');
});

it('shows a login link to guests on the public page', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Masuk')
        ->assertSee(route('login'));
});
