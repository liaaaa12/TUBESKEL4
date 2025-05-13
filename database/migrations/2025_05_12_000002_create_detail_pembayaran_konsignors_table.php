<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detail_pembayaran_konsignors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_konsignor_id')->constrained('pembayaran_konsignors')->onDelete('cascade');
            $table->foreignId('penjualan_barang_id')->constrained('penjualan_barang')->onDelete('cascade');
            $table->string('kode_barang_konsinyasi');
            $table->decimal('jumlah_pembayaran', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('detail_pembayaran_konsignors');
    }
}; 