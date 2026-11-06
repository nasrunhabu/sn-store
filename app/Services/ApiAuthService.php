<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApiAuthService
{
    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     * @return array{access_token: string, token_type: string, user: array{id: int, name: string, email: string}}
     */
    public function register(array $attributes): array
    {
        $user = DB::transaction(function () use ($attributes): User {
            $user = User::create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'],
            ]);
            $user->assignRole('Customer');

            return $user;
        });

        event(new Registered($user));

        return $this->createTokenResponse($user);
    }

    /**
     * @return array{access_token: string, token_type: string, user: array{id: int, name: string, email: string}}|null
     */
    public function login(string $email, string $password): ?array
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $this->createTokenResponse($user);
    }

    /**
     * @return array{access_token: string, token_type: string, user: array{id: int, name: string, email: string}}
     */
    private function createTokenResponse(User $user): array
    {
        $token = $user->createToken('api-access');

        return [
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];
    }
}
