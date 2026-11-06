<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(ProductService $productService): JsonResponse
    {
        return ApiResponse::success(
            $productService->getPublicProducts(),
            'Data produk berhasil dimuat.',
        );
    }

    public function show(int $product, ProductService $productService): JsonResponse
    {
        $productData = $productService->getPublicProduct($product);

        if ($productData === null) {
            return ApiResponse::error('Produk tidak ditemukan.', 404);
        }

        return ApiResponse::success($productData, 'Detail produk berhasil dimuat.');
    }
}
