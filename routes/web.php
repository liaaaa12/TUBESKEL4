<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangKonsinyasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PengirimanEmailPembelianController;

Route::resource('barang_konsinyasi', BarangKonsinyasiController::class);

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Customer Routes (Protected)
Route::middleware(['auth'])->group(function () {
    Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
    
    // Password Change Routes
    Route::get('/ubahpassword', [AuthController::class, 'ubahpassword'])->name('password.change');
    Route::post('/ubahpassword', [AuthController::class, 'prosesubahpassword'])->name('password.change');
});

// Route untuk export PDF pembelian barang
Route::get('/export-pembelian-barang', [PDFController::class, 'exportPembelianBarang']);

// proses pengiriman email pembelian barang
Route::get('/proses_kirim_email_pembelian', [PengirimanEmailPembelianController::class, 'proses_kirim_email_pembelian']);
