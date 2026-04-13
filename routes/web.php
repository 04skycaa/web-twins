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

Route::get('/products', [ProductController::class, 'index'])
    ->middleware(['auth', 'role:owner,kepala_toko']);

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