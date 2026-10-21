<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));
});

test('the configured initial administrator is registered with admin access', function () {
    $response = $this->post('/register', [
        'name' => 'SN Store Admin',
        'email' => config('admin.initial_admin_email'),
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', config('admin.initial_admin_email'))->firstOrFail();

    expect($user->isAdmin())->toBeTrue();
    $response->assertRedirect(route('home', absolute: false));
});
