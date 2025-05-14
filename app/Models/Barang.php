<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang'; // Nama tabel eksplisit
    protected $guarded = [];

    /**
     * Generate kode barang baru secara otomatis.
     */
    public static function getKodeBarang()
    {
        $result = DB::select("SELECT IFNULL(MAX(Kode_barang), 'AB000') as kode_barang FROM barang");
        $kd = $result[0]->kode_barang;

        $noAwal = substr($kd, -3);
        $noAkhir = (int)$noAwal + 1;

        return 'AB' . str_pad($noAkhir, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Mutator: Simpan harga tanpa karakter koma atau titik.
     */
    public function setHargaBarangAttribute($value)
    {
        // Hapus titik dan koma sebelum disimpan ke database
        $this->attributes['harga_barang'] = preg_replace('/[^\d]/', '', $value);
    }

    /**
     * Accessor: Format harga saat ditampilkan ke UI (Rp1.000.000)
     */
    public function getHargaBarangFormattedAttribute()
    {
        return 'Rp' . number_format($this->harga_barang, 0, ',', '.');
    }
}
