<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_barang_keluar', 50)->unique();
            $table->foreignId('produk_id')->constrained('produk');
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('tanggal');
            $table->decimal('jumlah', 10, 2);
            $table->enum('alasan', ['rusak', 'expired', 'diberikan', 'penyesuaian_lain']);
            $table->decimal('hpp_saat_ini', 15, 2);
            $table->decimal('total_beban', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_keluar');
    }
};