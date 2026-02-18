<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

// Halaman Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); // INI YANG BIKIN EROR TADI

// Dashboard (Hanya bisa dibuka kalau sudah LOGIN)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [TransaksiController::class, 'index'])->name('dashboard');
    Route::post('/checkout', [TransaksiController::class, 'checkout'])->name('checkout');
});