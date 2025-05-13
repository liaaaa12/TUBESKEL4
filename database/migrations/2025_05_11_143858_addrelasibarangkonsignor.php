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
        Schema::table('barang_konsinyasi', function (Blueprint $table) {
            // Add id_konsignor column and foreign key relationship
            $table->unsignedBigInteger('id_konsignor')->nullable();
            
            $table->foreign('id_konsignor')
                  ->references('id')
                  ->on('konsignors')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_konsinyasi', function (Blueprint $table) {
            // Drop foreign key constraint and column
            $table->dropForeign(['id_konsignor']);
            $table->dropColumn('id_konsignor');
        });
    }
};