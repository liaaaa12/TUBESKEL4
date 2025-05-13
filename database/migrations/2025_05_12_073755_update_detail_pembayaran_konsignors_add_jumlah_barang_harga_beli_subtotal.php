<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detail_pembayaran_konsignors', function (Blueprint $table) {
            // Tambah kolom baru
            if (!Schema::hasColumn('detail_pembayaran_konsignors', 'jumlah_barang')) {
                $table->integer('jumlah_barang')->after('kode_barang_konsinyasi')->nullable();
            }
            if (!Schema::hasColumn('detail_pembayaran_konsignors', 'harga_beli')) {
                $table->decimal('harga_beli', 15, 2)->after('jumlah_barang')->nullable();
            }
            if (!Schema::hasColumn('detail_pembayaran_konsignors', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->after('harga_beli')->nullable();
            }
            // Hapus kolom jumlah_pembayaran jika ada
            if (Schema::hasColumn('detail_pembayaran_konsignors', 'jumlah_pembayaran')) {
                $table->dropColumn('jumlah_pembayaran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_pembayaran_konsignors', function (Blueprint $table) {
            // Kembalikan kolom jumlah_pembayaran
            $table->decimal('jumlah_pembayaran', 15, 2)->nullable();
            // Hapus kolom yang baru ditambah
            $table->dropColumn(['jumlah_barang', 'harga_beli', 'subtotal']);
        });
    }
};
