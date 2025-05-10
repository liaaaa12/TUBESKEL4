<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianBarang extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_barang_id',
        'barang_id',
        'stok',
        'harga',
        'total',
    ];

    public function vendorBarang()
    {
        return $this->belongsTo(\App\Models\VendorBarang::class, 'vendor_barang_id');
    }

    public function barang()
    {
        return $this->belongsTo(\App\Models\Barang::class, 'barang_id');
    }
}
