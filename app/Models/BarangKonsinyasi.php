<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BarangKonsinyasi extends Model
{
    use HasFactory;

    protected $table = 'barang_konsinyasi';
    protected $fillable = ['kode_barang_konsinyasi', 'nama_barang', 'stok', 'harga', 'pemilik'];

    public static function getKodeBarangKonsinyasi()
    {
        // query kode perusahaan
        $sql = "SELECT IFNULL(MAX(kode_barang_konsinyasi), 'BKS-000') as kode_barang_konsinyasi 
                FROM barang_konsinyasi ";
        $kodebrgknsy = DB::select($sql);

        // cacah hasilnya
        foreach ($kodebrgknsy as $kdbrg) {
            $kd = $kdbrg->kode_barang_konsinyasi;
        }
        // Mengambil substring tiga digit akhir dari string PR-000
        $noawal = substr($kd,-3);
        $noakhir = $noawal+1; //menambahkan 1, hasilnya adalah integer cth 1
        $noakhir = 'BKS-'.str_pad($noakhir,3,"0",STR_PAD_LEFT); //menyambung dengan string PR-001
        return $noakhir;
    }
}
