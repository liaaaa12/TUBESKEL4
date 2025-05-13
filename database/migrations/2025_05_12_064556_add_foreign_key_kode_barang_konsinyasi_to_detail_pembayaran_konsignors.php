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
            $table->unsignedBigInteger('kode_barang_konsinyasi')->nullable()->change();
            $table->foreign('kode_barang_konsinyasi')
                ->references('id')
                ->on('barang_konsinyasi')
                ->onDelete('set null');
            $table->integer('jumlah_barang')->after('kode_barang_konsinyasi');
            $table->decimal('harga_beli', 15, 2)->after('jumlah_barang');
            $table->decimal('subtotal', 15, 2)->after('harga_beli');
            $table->dropColumn('jumlah_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kolom sudah tidak ada, jadi tidak perlu drop apa-apa
    }
};
