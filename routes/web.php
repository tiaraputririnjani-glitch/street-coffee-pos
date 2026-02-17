<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransaksiController;

Route::get('/', [TransaksiController::class, 'index'])->name('dashboard');


Route::post('/checkout', [TransaksiController::class, 'store'])->name('checkout');