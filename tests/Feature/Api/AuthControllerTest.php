<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

it('registers a customer and returns a bearer token', function () {
    $this->seed(RolePermissionSeeder::class);

    $response = $this->postJson(route('api.auth.register'), [
        'name' => 'API Customer',
        'email' => 'api-customer@example.com',
        'password' => 'Password-API-123!',
        'password_confirmation' => 'Password-API-123!',
    ]);

    $response->assertCreated()
        ->assertJsonPath('code', 201)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('message', 'Registrasi berhasil.')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.name', 'API Customer')
        ->assertJsonPath('data.user.email', 'api-customer@example.com');

    $user = User::query()->where('email', 'api-customer@example.com')->firstOrFail();
    expect(Hash::check('Password-API-123!', $user->password))->toBeTrue()
        ->and($user->hasRole('Customer'))->toBeTrue();
    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
    ]);
    expect($response->json('data.access_token'))->toBeString()->not->toBeEmpty();
});

it('returns a bearer token on login that can access protected API routes', function () {
    $user = User::factory()->create([
        'email' => 'api-login@example.com',
        'password' => 'Password-API-123!',
    ]);

    $response = $this->postJson(route('api.auth.login'), [
        'email' => $user->email,
        'password' => 'Password-API-123!',
    ]);

    $response->assertOk()
        ->assertJsonPath('code', 200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('message', 'Login berhasil.')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', $user->email);

    $token = $response->json('data.access_token');
    expect($token)->toBeString()->not->toBeEmpty();

    $this->withToken($token)
        ->getJson(route('api.cart.index'))
        ->assertOk()
        ->assertJsonPath('data.total_quantity', 0);
});

it('rejects invalid login credentials without creating a token', function () {
    $user = User::factory()->create([
        'email' => 'api-invalid@example.com',
        'password' => 'Password-API-123!',
    ]);

    $this->postJson(route('api.auth.login'), [
        'email' => $user->email,
        'password' => 'incorrect-password',
    ])
        ->assertUnauthorized()
        ->assertExactJson([
            'code' => 401,
            'status' => 'error',
            'message' => 'Email atau kata sandi tidak valid.',
            'data' => null,
        ]);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
    ]);
});

it('returns validation errors in the standard API response when registration data is invalid', function () {
    $this->postJson(route('api.auth.register'), [
        'name' => '',
        'email' => 'invalid',
        'password' => 'short',
        'password_confirmation' => 'different',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('code', 422)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('data.errors.name.0', 'The name field is required.')
        ->assertJsonPath('data.errors.email.0', 'The email field must be a valid email address.')
        ->assertJsonPath('data.errors.password.0', 'The password field confirmation does not match.');

    $this->assertDatabaseCount('users', 0);
});
