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
        Schema::create('pembelian_barangs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_barang_id');
            $table->unsignedBigInteger('barang_id');
            $table->integer('stok');
            $table->decimal('harga', 15, 2);
            $table->decimal('total', 15, 2)->nullable();
            $table->timestamps();
            $table->foreign('vendor_barang_id')->references('id')->on('vendor_barang')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('barang')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian_barangs');
    }
};
