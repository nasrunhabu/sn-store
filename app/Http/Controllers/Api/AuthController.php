<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Services\ApiAuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, ApiAuthService $apiAuthService): JsonResponse
    {
        return ApiResponse::success(
            $apiAuthService->register($request->validated()),
            'Registrasi berhasil.',
            201,
        );
    }

    public function login(LoginRequest $request, ApiAuthService $apiAuthService): JsonResponse
    {
        $authentication = $apiAuthService->login(
            $request->validated('email'),
            $request->validated('password'),
        );

        if ($authentication === null) {
            return ApiResponse::error('Email atau kata sandi tidak valid.', 401);
        }

        return ApiResponse::success($authentication, 'Login berhasil.');
    }
}
