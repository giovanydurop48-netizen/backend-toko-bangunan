<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    use HasFactory;

    protected $table = 'barang_keluar';

    protected $fillable = [
        'nomor_barang_keluar',
        'produk_id',
        'user_id',
        'tanggal',
        'jumlah',
        'alasan',
        'hpp_saat_ini',
        'total_beban',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'jumlah' => 'float',
        'hpp_saat_ini' => 'float',
        'total_beban' => 'float',
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