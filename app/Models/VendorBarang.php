<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBarang extends Model
{
    use HasFactory;

    protected $table = 'vendor_barang';

    protected $fillable = [
        'kode_vendor_barang',
        'nama_vndr_brg',
        'alamat_vndr_brg',
        'no_telp_vndr_brg'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $latestVendor = static::latest()->first();
            
            if (!$latestVendor) {
                $model->kode_vendor_barang = 'VB001';
            } else {
                $currentNumber = intval(substr($latestVendor->kode_vendor_barang, 2));
                $nextNumber = $currentNumber + 1;
                $model->kode_vendor_barang = 'VB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
