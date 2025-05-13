<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('barang_konsinyasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang_konsinyasi', 20);
            $table->string('nama_barang');
            $table->string('foto')->nullable();
            $table->integer('stok');
            $table->decimal('harga', 10, 2);
            $table->string('pemilik')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_konsinyasi');
    }
};
