<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicHomeController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('home');

Route::get('/dashboard', function () {
    return view('pages.admin.dashboard.index', [
        'userCount' => User::query()->count(),
    ]);
})->middleware(['auth', 'verified', 'can:access-admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
