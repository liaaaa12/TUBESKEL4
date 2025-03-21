<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/modul_fauzanabiyyuaziz', function () {
    return view('modul_fauzanabiyyuaziz');
});
