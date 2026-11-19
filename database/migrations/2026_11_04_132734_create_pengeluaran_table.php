<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengeluaran', 50)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('tanggal');
            $table->enum('kategori_pengeluaran', ['operasional', 'lain_lain'])->default('operasional');
            $table->string('nama_pengeluaran', 150);
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};