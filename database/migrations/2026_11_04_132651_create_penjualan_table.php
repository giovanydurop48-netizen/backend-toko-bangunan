<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi', 50)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('pembeli_id')->nullable()->constrained('pembeli')->onDelete('set null');
            $table->dateTime('tanggal');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon_total', 15, 2)->default(0);
            $table->decimal('total_akhir', 15, 2)->default(0);
            $table->enum('metode_pembayaran', ['tunai', 'transfer', 'qris']);
            $table->string('referensi_pembayaran', 100)->nullable();
            $table->decimal('bayar', 15, 2)->default(0);
            $table->decimal('kembalian', 15, 2)->default(0);
            $table->enum('status', ['selesai', 'dibatalkan'])->default('selesai');
            $table->text('alasan_pembatalan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};