<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';

    protected $fillable = [
        'nomor_transaksi',
        'user_id',
        'pembeli_id',
        'tanggal',
        'subtotal',
        'diskon_total',
        'total_akhir',
        'metode_pembayaran',
        'referensi_pembayaran',
        'bayar',
        'kembalian',
        'status',
        'alasan_pembatalan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'subtotal' => 'float',
        'diskon_total' => 'float',
        'total_akhir' => 'float',
        'bayar' => 'float',
        'kembalian' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pembeli()
    {
        return $this->belongsTo(Pembeli::class, 'pembeli_id');
    }

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'penjualan_id');
    }
}