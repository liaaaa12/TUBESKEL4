<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanBarang extends Model
{
    use HasFactory;

    protected $table = 'penjualan_barang';
    protected $fillable = ['penjualan_id', 'Kode_barang', 'harga_beli', 'harga_jual', 'jml', 'tgl'];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'Kode_barang');
    }

    public function barang_konsinyasi()
    {
        return $this->belongsTo(Barang::class, 'kode_barang_konsinyasi');
    }
}