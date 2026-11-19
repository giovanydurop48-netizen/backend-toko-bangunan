<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('jenis_perubahan', ['penjualan', 'pembelian', 'barang_keluar', 'pembatalan_penjualan', 'penyesuaian']);
            $table->decimal('stok_awal', 10, 2);
            $table->decimal('jumlah_perubahan', 10, 2);
            $table->decimal('stok_akhir', 10, 2);
            $table->string('referensi_id', 50)->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_stok');
    }
};