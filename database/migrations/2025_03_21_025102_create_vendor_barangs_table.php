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
        Schema::create('vendor_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_vndr_brg'); // Nama
            $table->string('alamat_vndr_brg',100);//Alamat
            $table->string('no_telp_vndr_brg');//No.Telp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_barangs');
    }
};
