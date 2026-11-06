<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MidtransNotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicProductController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ShippingCostController;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('home');
Route::get('/produk', PublicProductController::class)->name('products.index');

Route::middleware('auth')->group(function (): void {
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang/produk/{product}', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/keranjang/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/keranjang/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');

    Route::get('/wilayah/provinsi/{province}/kabupaten', [RegionController::class, 'cities'])->name('regions.cities');
    Route::get('/wilayah/kabupaten/{city}/kecamatan', [RegionController::class, 'districts'])->name('regions.districts');
    Route::get('/wilayah/kecamatan/{district}/desa', [RegionController::class, 'villages'])->name('regions.villages');

    Route::get('/checkout/ongkir', ShippingCostController::class)->middleware('throttle:30,1')->name('checkout.shipping-cost');
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/transaksi', [CheckoutController::class, 'index'])->name('orders.index');
    Route::get('/transaksi/{order}', [CheckoutController::class, 'show'])->name('orders.show');
    Route::post('/transaksi/{order}/batal', [CheckoutController::class, 'cancel'])->name('orders.cancel');
    Route::post('/transaksi/{order}/cek-status', [CheckoutController::class, 'checkStatus'])->name('orders.check-status');
});

Route::post('/midtrans/notification', MidtransNotificationController::class)->name('midtrans.notification');

Route::get('/dashboard', function () {
    return view('pages.admin.dashboard.index', [
        'categoryCount' => Category::query()->count(),
        'productCount' => Product::query()->count(),
        'transactionCount' => Order::query()->count(),
        'userCount' => User::query()->count(),
    ]);
})->middleware(['auth', 'verified', 'role:Super Admin|Admin'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:Super Admin|Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/kategori/tambah', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/kategori/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
        Route::get('/produk/tambah', [ProductController::class, 'create'])->name('products.create');
        Route::post('/produk', [ProductController::class, 'store'])->name('products.store');
        Route::get('/produk/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/produk/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/produk/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::get('/transaksi', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transaksi/{order}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('/transaksi/{order}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transaksi/{order}', [TransactionController::class, 'update'])->name('transactions.update');

        Route::get('/hero', [HeroSlideController::class, 'index'])->name('hero-slides.index');
        Route::get('/hero/tambah', [HeroSlideController::class, 'create'])->name('hero-slides.create');
        Route::post('/hero', [HeroSlideController::class, 'store'])->name('hero-slides.store');
        Route::get('/hero/{heroSlide}/edit', [HeroSlideController::class, 'edit'])->name('hero-slides.edit');
        Route::put('/hero/{heroSlide}', [HeroSlideController::class, 'update'])->name('hero-slides.update');
        Route::patch('/hero/{heroSlide}/status', [HeroSlideController::class, 'toggle'])->name('hero-slides.toggle');
        Route::delete('/hero/{heroSlide}', [HeroSlideController::class, 'destroy'])->name('hero-slides.destroy');

        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');

        Route::middleware(['role:Super Admin', 'permission:manage roles'])->group(function (): void {
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
            Route::put('/roles/{user}', [RoleController::class, 'update'])->name('roles.update');
        });
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
