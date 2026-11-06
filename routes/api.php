<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:api-register')
    ->name('api.auth.register');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:api-login')
    ->name('api.auth.login');

Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])
    ->whereNumber('product')
    ->name('api.products.show');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/cart', [CartController::class, 'index'])->name('api.cart.index');
    Route::post('/cart/products/{product}', [CartController::class, 'store'])
        ->whereNumber('product')
        ->name('api.cart.items.store');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
