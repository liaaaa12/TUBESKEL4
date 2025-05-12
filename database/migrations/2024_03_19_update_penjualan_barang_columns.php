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
        Schema::table('penjualan_barang', function (Blueprint $table) {
            $table->string('Kode_barang', 20)->nullable()->change();
            $table->string('kode_barang_konsinyasi', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan_barang', function (Blueprint $table) {
            $table->unsignedBigInteger('Kode_barang')->nullable()->change();
            $table->unsignedBigInteger('kode_barang_konsinyasi')->nullable()->change();
        });
    }
}; 