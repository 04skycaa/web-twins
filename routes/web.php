<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController; // Tambahkan ini
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:owner,kepala_toko'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/opname', [ProductController::class, 'opname'])->name('products.opname');
    Route::get('/products/request', [ProductController::class, 'request'])->name('products.request');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/mass-destroy', [ProductController::class, 'massDestroy'])->name('products.mass_destroy');

    // Opname Routes
    Route::post('/products/opname', [ProductController::class, 'storeOpname'])->name('products.opname.store');
    Route::put('/products/opname/{id}', [ProductController::class, 'updateOpname'])->name('products.opname.update');
    Route::delete('/products/opname/{id}', [ProductController::class, 'destroyOpname'])->name('products.opname.destroy');

    // Request Routes
    Route::post('/products/request', [ProductController::class, 'storeRequest'])->name('products.request.store');
    Route::put('/products/request/{id}', [ProductController::class, 'updateRequest'])->name('products.request.update');
    Route::delete('/products/request/{id}', [ProductController::class, 'destroyRequest'])->name('products.request.destroy');
    Route::post('/products/request/{id}/approve', [ProductController::class, 'approveRequest'])->name('products.request.approve');
    Route::post('/products/request/{id}/reject', [ProductController::class, 'rejectRequest'])->name('products.request.reject');
    Route::post('/products/request/{id}/ship', [ProductController::class, 'shipRequest'])->name('products.request.ship');
    Route::post('/products/request/{id}/receive', [ProductController::class, 'receiveRequest'])->name('products.request.receive');

    // Export Routes
    Route::get('/products/export/excel', [ProductController::class, 'exportExcel'])->name('products.export.excel');
    Route::get('/products/export/pdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf');
});
use App\Http\Controllers\UserController;
use App\Http\Controllers\OutletController;

Route::prefix('users')->middleware(['auth', 'verified', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::prefix('outlet')->middleware(['auth', 'verified', 'role:owner'])->group(function () {
    Route::get('/', [OutletController::class, 'index'])->name('outlet.index');
    Route::post('/', [OutletController::class, 'store'])->name('outlet.store');
    Route::put('/{id}', [OutletController::class, 'update'])->name('outlet.update');
    Route::delete('/{id}', [OutletController::class, 'destroy'])->name('outlet.destroy');
});

Route::prefix('transaksi')->middleware(['auth', 'verified', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/', [TransaksiController::class, 'riwayat'])->name('transaksi.index');
    Route::get('/riwayat', [TransaksiController::class, 'riwayat'])->name('transaksi.riwayat');
    Route::get('/diskon', [TransaksiController::class, 'diskon'])->name('transaksi.diskon');
    Route::post('/diskon', [TransaksiController::class, 'storeDiskon'])->name('transaksi.diskon.store');
    Route::put('/diskon/{id}', [TransaksiController::class, 'updateDiskon'])->name('transaksi.diskon.update');
    Route::delete('/diskon/{id}', [TransaksiController::class, 'destroyDiskon'])->name('transaksi.diskon.destroy');
});

use App\Http\Controllers\KeuanganController;
Route::get('/keuangan', [KeuanganController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:owner,kepala_toko'])
    ->name('keuangan.index');

Route::get('/outlet/{id}', [LandingController::class, 'showOutlet'])->name('user.index');
Route::post('/outlet/{id}/review', [LandingController::class, 'storeReview'])
    ->middleware(['auth', 'verified'])
    ->name('store.review.store');
Route::post('/submit-general-review', [LandingController::class, 'generalReview'])
    ->middleware(['auth', 'verified'])
    ->name('landing.review.store');

require __DIR__ . '/auth.php';