<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $this->seed(RolePermissionSeeder::class);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    expect(auth()->user()->hasRole('Customer'))->toBeTrue();
    $response->assertRedirect(route('home', absolute: false));
});

test('registering with an administrative email does not grant an elevated role', function () {
    $this->seed(RolePermissionSeeder::class);

    $response = $this->post('/register', [
        'name' => 'SN Store Admin',
        'email' => 'admin@sn-store.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'admin@sn-store.com')->firstOrFail();

    expect($user->hasRole('Customer'))->toBeTrue()
        ->and($user->canAccessAdminPanel())->toBeFalse();
    $response->assertRedirect(route('home', absolute: false));
});
