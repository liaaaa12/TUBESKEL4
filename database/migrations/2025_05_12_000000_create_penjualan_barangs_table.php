<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('penjualan_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang_konsinyasi')->onDelete('cascade');
            $table->foreignId('penjualan_id')->constrained('penjualan')->onDelete('cascade');
            $table->integer('jumlah_terjual');
            $table->decimal('harga_jual', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('penjualan_barangs');
    }
}; 