<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    
    protected $fillable = [
        'no_faktur',
        'tanggal_faktur',
        'pembeli_id',
        'tagihan',
        
        'status'
    ];

    /**
     * Generate kode faktur otomatis
     */
    public static function getKodeFaktur()
    {
        $prefix = 'FKT-';
        $date = now()->format('Ymd');
        
        $lastKode = self::where('no_faktur', 'like', $prefix . $date . '%')
            ->orderBy('no_faktur', 'desc')
            ->first();
            
        if (!$lastKode) {
            $number = '0001';
        } else {
            $lastNumber = intval(substr($lastKode->no_faktur, -4));
            $number = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }
        
        return $prefix . $date . $number;
    }

    protected static function boot()
{
    parent::boot();

    static::creating(function ($penjualan) {
        if (empty($penjualan->tgl)) {
            $penjualan->tgl = now(); // atau Carbon::today() kalo hanya tanggal
        }
    });
}

    /**
     * Relasi ke pembeli
     */
    public function pembeli()
    {
        return $this->belongsTo(Pembeli::class, 'pembeli_id');
    }

    /**
     * Relasi ke detail penjualan barang
     */
    public function penjualanBarang()
    {
        return $this->hasMany(PenjualanBarang::class, 'penjualan_id');
    }
    
    /**
     * Relasi ke pembayaran
     */
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'penjualan_id');
    }
}