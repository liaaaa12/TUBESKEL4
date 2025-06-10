<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\BarangKonsinyasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PengirimanEmailController;
use App\Http\Controllers\PengirimanEmailPembelianController;
use App\Mail\TesMail;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes (Protected)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('barang_konsinyasi', BarangKonsinyasiController::class);
    Route::get('/export-pembelian-barang', [PDFController::class, 'exportPembelianBarang']);
});

// Customer Routes (Protected)
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/customer', [CustomerController::class, 'index'])->name('customer');

    // Password Change
    Route::get('/ubahpassword', [AuthController::class, 'ubahpassword'])->name('password.change');
    Route::post('/ubahpassword', [AuthController::class, 'prosesubahpassword'])->name('password.change');

    // Keranjang
    Route::get('/keranjang', function () {
        return view('keranjang');
    })->name('keranjang');

    // Proses Pengiriman Email
    Route::get('/proses_kirim_email_pembayaran', [PengirimanEmailController::class, 'proses_kirim_email_pembayaran']);
    Route::get('/proses_kirim_email_pembelian', [PengirimanEmailPembelianController::class, 'proses_kirim_email_pembelian']);

    // Cek Status Pembayaran
    Route::get('/cek_status_pembayaran_pg', [KeranjangController::class, 'cek_status_pembayaran_pg']);

    // Transaction History Route
    Route::get('/riwayat-transaksi', [KeranjangController::class, 'riwayatTransaksi'])->name('riwayat.transaksi');
});

// Payment & Midtrans Routes
Route::p
