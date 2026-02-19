<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

// 1. Halaman Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// 2. PINTU LOGOUT
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Dashboard & Fitur Kasir (Hanya bisa dibuka kalau sudah LOGIN)
Route::middleware(['auth'])->group(function () {
    // Halaman Utama Kasir
    Route::get('/', [TransaksiController::class, 'index'])->name('dashboard');
    
    // Proses Simpan Transaksi
    Route::post('/checkout', [TransaksiController::class, 'checkout'])->name('checkout');

    // JALUR BARU: Ambil Data Laporan (Biar Tombol "Lihat Laporan" Jalan)
    Route::get('/get-report', [TransaksiController::class, 'getReport'])->name('get-report');
});