<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_transaksis', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel transaksis
            $table->foreignId('transaksi_id')
                  ->constrained('transaksis')
                  ->onDelete('cascade');

            // Relasi ke tabel produk (sesuai nama tabel produk di database)
            $table->foreignId('produk_id')
                  ->constrained('produk') // Jika nama tabel Anda 'produks', ubah menjadi 'produks'
                  ->onDelete('cascade');

            $table->integer('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksis');
    }
};