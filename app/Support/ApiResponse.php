<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        mixed $data,
        string $message = 'Data berhasil dimuat.',
        int $code = 200,
    ): JsonResponse {
        return response()->json([
            'code' => $code,
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error(
        string $message,
        int $code,
        mixed $data = null,
    ): JsonResponse {
        return response()->json([
            'code' => $code,
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
