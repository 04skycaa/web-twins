<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController; // Tambahkan ini
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:owner,kepala_toko'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Opname Routes
    Route::post('/products/opname', [ProductController::class, 'storeOpname'])->name('products.opname.store');
    Route::put('/products/opname/{id}', [ProductController::class, 'updateOpname'])->name('products.opname.update');
    Route::delete('/products/opname/{id}', [ProductController::class, 'destroyOpname'])->name('products.opname.destroy');

    // Request Routes
    Route::post('/products/request/{id}/approve', [ProductController::class, 'approveRequest'])->name('products.request.approve');
    Route::post('/products/request/{id}/reject', [ProductController::class, 'rejectRequest'])->name('products.request.reject');
});
use App\Http\Controllers\UserController;
use App\Http\Controllers\OutletController;

Route::prefix('users')->middleware(['auth', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::prefix('outlet')->middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/', [OutletController::class, 'index'])->name('outlet.index');
    Route::post('/', [OutletController::class, 'store'])->name('outlet.store');
    Route::put('/{id}', [OutletController::class, 'update'])->name('outlet.update');
    Route::delete('/{id}', [OutletController::class, 'destroy'])->name('outlet.destroy');
});

Route::prefix('transaksi')->middleware(['auth', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/', [TransaksiController::class, 'riwayat'])->name('transaksi.index');
    Route::get('/riwayat', [TransaksiController::class, 'riwayat'])->name('transaksi.riwayat');
    Route::get('/diskon', [TransaksiController::class, 'diskon'])->name('transaksi.diskon');
    Route::post('/diskon', [TransaksiController::class, 'storeDiskon'])->name('transaksi.diskon.store');
    Route::put('/diskon/{id}', [TransaksiController::class, 'updateDiskon'])->name('transaksi.diskon.update');
    Route::delete('/diskon/{id}', [TransaksiController::class, 'destroyDiskon'])->name('transaksi.diskon.destroy');
});

use App\Http\Controllers\KeuanganController;
Route::get('/keuangan', [KeuanganController::class, 'index'])
    ->middleware(['auth', 'role:owner,kepala_toko'])
    ->name('keuangan.index');

Route::get('/user-produk', function () {
    return view('user');
})->name('user.index');

require __DIR__ . '/auth.php';