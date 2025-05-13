<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembayaranKonsignor extends Model
{
    use HasFactory;

    protected $table = 'detail_pembayaran_konsignors';

    protected $fillable = [
        'pembayaran_konsignor_id',
        'penjualan_barang_id',
        'kode_barang_konsinyasi',
        'jumlah_barang',
        'harga_beli',
        'subtotal',
        'jumlah_pembayaran'
    ];

    protected $casts = [
        'jumlah_pembayaran' => 'decimal:2'
    ];

    public function pembayaranKonsignor()
    {
        return $this->belongsTo(PembayaranKonsignor::class);
    }

    public function penjualanBarang()
    {
        return $this->belongsTo(PenjualanBarang::class);
    }

    public function barangKonsinyasi()
    {
        return $this->belongsTo(BarangKonsinyasi::class, 'kode_barang_konsinyasi', 'id');
    }
} 