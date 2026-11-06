<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(CategoryService $categoryService): JsonResponse
    {
        return ApiResponse::success(
            $categoryService->getPublicCategories(),
            'Data kategori berhasil dimuat.',
        );
    }
}
