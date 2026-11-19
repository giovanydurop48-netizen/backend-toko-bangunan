<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembelian extends Model
{
    use HasFactory;

    protected $table = 'detail_pembelian';

    protected $fillable = [
        'pembelian_id',
        'produk_id',
        'harga_beli',
        'jumlah',
        'subtotal',
    ];

    protected $casts = [
        'harga_beli' => 'float',
        'jumlah' => 'float',
        'subtotal' => 'float',
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}