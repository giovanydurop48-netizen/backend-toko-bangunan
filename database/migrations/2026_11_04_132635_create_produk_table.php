<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori')->onDelete('cascade');
            $table->string('kode_produk', 50)->unique();
            $table->string('nama_produk', 150);
            $table->string('satuan_dasar', 20); // Pcs, Meter, Kg, Sak, Roll
            $table->decimal('harga_beli_terakhir', 15, 2)->default(0);
            $table->decimal('hpp_rata_rata', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->decimal('stok', 10, 2)->default(0);
            $table->decimal('stok_minimum', 10, 2)->default(5);
            $table->date('tanggal_expired')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Constraint Validasi Stok Tidak Boleh Negatif di Level DB
        DB::statement('ALTER TABLE produk ADD CONSTRAINT chk_stok_non_negatif CHECK (stok >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};