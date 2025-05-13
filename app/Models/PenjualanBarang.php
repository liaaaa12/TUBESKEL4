<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanBarang extends Model
{
    use HasFactory;

    protected $table = 'penjualan_barang';
    
    // Menyesuaikan dengan struktur tabel yang ada
    protected $fillable = [
        'penjualan_id',
        'tgl',
        'Kode_barang',
        'kode_barang_konsinyasi',
        'harga_beli',
        'harga_jual',
        'subtotal',
        'jml'
    ];

    // Timestamps enabled by default in Laravel
    public $timestamps = true;

    // Relationships
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
        return $this->belongsTo(\App\Models\BarangKonsinyasi::class, 'kode_barang_konsinyasi');
    }

    public function detailPembayaranKonsignor()
    {
        return $this->hasMany(DetailPembayaranKonsignor::class, 'penjualan_barang_id')
            ->whereHas('pembayaranKonsignor', function ($query) {
                $query->whereNotNull('id');
            });
    }
}