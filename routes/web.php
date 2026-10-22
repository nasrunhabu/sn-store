<?php

use App\DemoStore;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('home');

Route::get('/dashboard', function (DemoStore $store) {
    $data = $store->all();

    return view('pages.admin.dashboard.index', [
        'categoryCount' => count($data['categories']),
        'productCount' => count($data['products']),
        'transactionCount' => count($data['transactions']),
        'userCount' => count($data['users']),
    ]);
})->middleware(['auth', 'verified', 'can:access-admin'])->name('dashboard');

Route::middleware(['auth', 'verified', 'can:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
        Route::get('/produk/tambah', [ProductController::class, 'create'])->name('products.create');
        Route::post('/produk', [ProductController::class, 'store'])->name('products.store');
        Route::get('/produk/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/produk/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/produk/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::get('/transaksi', [TransactionController::class, 'index'])->name('transactions.index');
        Route::put('/transaksi/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');

        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
