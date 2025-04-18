<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BarangKonsinyasi extends Model
{
    use HasFactory;

    protected $table = 'barang_konsinyasi';
    protected $fillable = [
        'kode_barang_konsinyasi',
        'nama_barang',
        'foto',
        'stok',
        'harga',
        'pemilik'
    ];

    public static function getKodeBarangKonsinyasi()
    {
        $sql = "SELECT IFNULL(MAX(kode_barang_konsinyasi), 'BKS-000') as kode_barang_konsinyasi 
                FROM barang_konsinyasi ";
        $kodebrgknsy = DB::select($sql);

        foreach ($kodebrgknsy as $kdbrg) {
            $kd = $kdbrg->kode_barang_konsinyasi;
        }
        $noawal = substr($kd, -3);
        $noakhir = $noawal + 1;
        $noakhir = 'BKS-' . str_pad($noakhir, 3, "0", STR_PAD_LEFT);
        return $noakhir;
    }
}
