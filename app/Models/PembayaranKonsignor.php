<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranKonsignor extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_konsignors';

    protected $fillable = [
        'konsignor_id',
        'no_pembayaran',
        'tanggal_pembayaran',
        'total_pembayaran',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'date',
        'total_pembayaran' => 'decimal:2'
    ];

    public function konsignor()
    {
        return $this->belongsTo(Konsignor::class);
    }

    public function detailPembayaran()
    {
        return $this->hasMany(DetailPembayaranKonsignor::class);
    }
} 