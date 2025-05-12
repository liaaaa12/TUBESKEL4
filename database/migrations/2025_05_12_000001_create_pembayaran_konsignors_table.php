<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pembayaran_konsignors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konsignor_id')->constrained('konsignors')->onDelete('cascade');
            $table->string('no_pembayaran')->unique();
            $table->date('tanggal_pembayaran');
            $table->decimal('total_pembayaran', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembayaran_konsignors');
    }
}; 