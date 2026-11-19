<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';

    protected $fillable = [
        'nomor_pembelian',
        'distributor_id',
        'user_id',
        'tanggal',
        'total_harga',
        'metode_pembayaran',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'total_harga' => 'float',
    ];

    public function distributor()
    {
        return $this->belongsTo(Distributor::class, 'distributor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelian::class, 'pembelian_id');
    }
}