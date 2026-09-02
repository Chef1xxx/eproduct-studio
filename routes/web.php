<?php

use App\Http\Controllers\Web\Auth\AuthController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\Seller\SellerController;
use App\Http\Controllers\Web\Seller\SellerProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/products/{product}', [CatalogController::class, 'show'])->name('products.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegister']);

    Route::get('/login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'storeLogin']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/seller', [SellerController::class, 'index'])->name('seller.index');

    Route::get('/seller/products/create', [SellerProductController::class, 'create'])->name('seller.products.create');
    Route::post('/seller/products', [SellerProductController::class, 'store'])->name('seller.products.store');
    Route::get('/seller/products/{product}/edit', [SellerProductController::class, 'edit'])->name('seller.products.edit');
    Route::put('/seller/products/{product}', [SellerProductController::class, 'update'])->name('seller.products.update');
    Route::delete('/seller/products/{product}', [SellerProductController::class, 'destroy'])->name('seller.products.destroy');
});