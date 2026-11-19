<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pembelian', 50)->unique();
            $table->foreignId('distributor_id')->constrained('distributor');
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('tanggal');
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->enum('metode_pembayaran', ['tunai', 'transfer'])->default('tunai');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};