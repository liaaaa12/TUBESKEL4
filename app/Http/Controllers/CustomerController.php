<?php

namespace App\Http\Controllers;

use App\Models\BarangKonsinyasi;
use App\Models\Barang;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        // Ambil data barang konsinyasi
        $barangKonsinyasi = BarangKonsinyasi::where('stok', '>', 0)->get();
        
        // Ambil data barang biasa
        $barangBiasa = Barang::where('stok', '>', 0)->get();
        
        // Gabungkan data
        $barangs = $barangKonsinyasi->concat($barangBiasa);
        
        // Debug untuk melihat data
        /*foreach($barangs as $barang) {
            echo "ID: " . $barang->id . "<br>";
            echo "Nama: " . $barang->nama_barang . "<br>";
            echo "Foto: " . ($barang->foto ?? $barang->gambar) . "<br>";
            echo "Harga: " . ($barang->harga ?? $barang->harga_barang) . "<br>";
            echo "Stok: " . $barang->stok . "<br>";
            echo "Tipe: " . (get_class($barang) === 'App\Models\BarangKonsinyasi' ? 'Konsinyasi' : 'Biasa') . "<br>";
            echo "<hr>";
        }
        die();*/
        
        return view('customer', compact('barangs'));
    }
} 