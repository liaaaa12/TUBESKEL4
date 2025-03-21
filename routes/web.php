<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangKonsinyasiController;

Route::resource('barang_konsinyasi', BarangKonsinyasiController::class);
Route::get('/', function () {
    return view('welcome');
});
