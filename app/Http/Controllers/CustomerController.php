<?php

namespace App\Http\Controllers;

use App\Models\BarangKonsinyasi;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $barangs = BarangKonsinyasi::where('stok', '>', 0)->get();
        return view('customer', compact('barangs'));
    }
} 