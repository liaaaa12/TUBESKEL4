<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangKonsinyasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\KeranjangController;

// Admin Routes (Protected)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('barang_konsinyasi', BarangKonsinyasiController::class);
    Route::get('/export-pembelian-barang', [PDFController::class, 'exportPembelianBarang']);
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Midtrans Callback (must be public)
Route::post('/payment/callback', [KeranjangController::class, 'handleCallback'])
    ->name('payment.callback');

// Payment Status Check
Route::get('/payment/status/{orderId}', [KeranjangController::class, 'checkStatus'])
    ->name('payment.status');

// Active Payment Status Check
Route::get('/payment/check-status-pg', [KeranjangController::class, 'cek_status_pembayaran_pg'])
    ->name('payment.check.status');

// Payment Routes
Route::post('/payment/create-transaction', [KeranjangController::class, 'createTransaction'])
    ->name('payment.create');

// Auto Refresh Route
Route::get('/payment/auto-refresh', function() {
    // Trigger payment status check
    app(KeranjangController::class)->cek_status_pembayaran_pg();
    return view('autorefresh_penjualan');
})->name('payment.autorefresh_penjualan');

// Test Routes (only for development)
Route::get('/test-callback', [KeranjangController::class, 'testCallback'])
    ->name('test.callback');

Route::get('/test-callback/{status}', [KeranjangController::class, 'testCallbackStatus'])
    ->name('test.callback.status');

// Test Routes (development only)
Route::get('/test-payment-callback', [KeranjangController::class, 'testSimpleCallback'])
    ->name('test.callback.simple');

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Customer Routes (Protected)
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
    
    // Password Change Routes
    Route::get('/ubahpassword', [AuthController::class, 'ubahpassword'])->name('password.change');
    Route::post('/ubahpassword', [AuthController::class, 'prosesubahpassword'])->name('password.change');

    Route::get('/keranjang', function () {
        return view('keranjang');
    })->name('keranjang');
});
