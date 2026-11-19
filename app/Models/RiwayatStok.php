<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatStok extends Model
{
    use HasFactory;

    protected $table = 'riwayat_stok';
    public $timestamps = false; // Hanya menggunakan created_at bawaan DB

    protected $fillable = [
        'produk_id',
        'user_id',
        'jenis_perubahan',
        'stok_awal',
        'jumlah_perubahan',
        'stok_akhir',
        'referensi_id',
        'keterangan',
    ];

    protected $casts = [
        'stok_awal' => 'float',
        'jumlah_perubahan' => 'float',
        'stok_akhir' => 'float',
        'created_at' => 'datetime',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}