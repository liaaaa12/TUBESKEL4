<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBarang extends Model
{
    use HasFactory;

    protected $table = 'vendor_barang';

    protected $fillable = [
        'nama_vndr_brg',
        'alamat_vndr_brg',
        'no_telp_vndr_brg'
    ];
}
