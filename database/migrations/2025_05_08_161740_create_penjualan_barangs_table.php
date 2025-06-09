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
        Schema::create('penjualan_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->onDelete('cascade');
            
            // Kolom untuk menyimpan ID barang (bisa dari barang reguler atau konsinyasi)
            $table->unsignedBigInteger('Kode_barang')->nullable();
            $table->unsignedBigInteger('kode_barang_konsinyasi')->nullable();
            
            // Constraint untuk barang reguler
            $table->foreign('Kode_barang')
                  ->references('id')
                  ->on('barang')
                  ->onDelete('cascade');
            
            // Constraint untuk barang konsinyasi
            $table->foreign('kode_barang_konsinyasi')
                  ->references('id')
                  ->on('barang_konsinyasi')
                  ->onDelete('cascade');
            
            // Setidaknya salah satu dari ID barang harus diisi
            $table->index(['Kode_barang', 'kode_barang_konsinyasi']);
            
            $table->integer('harga_beli');
            $table->integer('harga_jual');
            $table->integer('jml'); // jumlah barang yang dibeli
            $table->integer('subtotal');
            $table->date('tgl');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_barang');
    }
};