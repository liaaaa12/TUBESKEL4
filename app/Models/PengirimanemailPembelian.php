<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengirimanemail extends Model
{
    use HasFactory;

    protected $table = 'pengirimanemail'; // Nama tabel sesuai database

    protected $guarded = []; // Semua kolom boleh diisi

    // Relasi ke tabel barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    // Relasi ke tabel vendor_barang
    public function vendor()
    {
        return $this->belongsTo(VendorBarang::class, 'vendor_barang_id');
    }
}
