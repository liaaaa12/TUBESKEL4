<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKonsinyasi extends Model
{
    use HasFactory;

    protected $table = 'barang_konsinyasi';
    protected $fillable = ['nama_barang', 'stok', 'harga', 'pemilik'];
}
