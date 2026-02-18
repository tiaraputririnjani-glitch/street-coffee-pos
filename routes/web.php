<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

// Halaman Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// PINTU LOGOUT (WAJIB ADA INI AGAR EROR HILANG)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard (Hanya bisa dibuka kalau sudah LOGIN)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [TransaksiController::class, 'index'])->name('dashboard');
    Route::post('/checkout', [TransaksiController::class, 'checkout'])->name('checkout');
});