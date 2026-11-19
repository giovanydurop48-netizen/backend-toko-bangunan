<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor', function (Blueprint $table) {
            $table->id();
            $table->string('kode_distributor', 20)->unique();
            $table->string('nama_distributor', 100);
            $table->string('telepon', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor');
    }
};