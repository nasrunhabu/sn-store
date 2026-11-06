<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request, CartService $cartService): JsonResponse
    {
        return ApiResponse::success(
            $cartService->getApiCart($request->user()),
            'Data keranjang berhasil dimuat.',
        );
    }

    public function store(Request $request, Product $product, CartService $cartService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                $validator->errors()->first(),
                422,
                ['errors' => $validator->errors()],
            );
        }

        try {
            $cartService->add(
                $request->user(),
                $product,
                (int) $validator->validated()['quantity'],
            );
        } catch (ValidationException $exception) {
            return ApiResponse::error(
                $exception->validator->errors()->first(),
                422,
                ['errors' => $exception->errors()],
            );
        }

        return ApiResponse::success(
            $cartService->getApiCart($request->user()),
            'Produk berhasil ditambahkan ke keranjang.',
            201,
        );
    }
}
