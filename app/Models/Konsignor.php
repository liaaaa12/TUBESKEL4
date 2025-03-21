<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Konsignor extends Model
{
    use HasFactory;

    protected $table = 'konsignors'; // Nama tabel eksplisit

    protected $guarded = [];

    public static function getIdKonsignors()
    {
        // query kode perusahaan
        $sql = "SELECT IFNULL(MAX(id_konsignors), 'K-000') as id_konsignors 
                FROM konsignors ";
        $id_konsignor = DB::select($sql);

        // cacah hasilnya
        foreach ($id_konsignor as $idKonsig) {
            $kd = $idKonsig->id_konsignors;
        }
        // Mengambil substring tiga digit akhir dari string PR-000
        $noawal = substr($kd,-2);
        $noakhir = $noawal+1; //menambahkan 1, hasilnya adalah integer cth 1
        $noakhir = 'K-'.str_pad($noakhir,3,"0",STR_PAD_LEFT); //menyambung dengan string PR-001
        return $noakhir;

    }

    
}
