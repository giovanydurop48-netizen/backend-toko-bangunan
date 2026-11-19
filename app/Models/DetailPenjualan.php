<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    use HasFactory;

    protected $table = 'detail_penjualan';

    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'hpp_saat_ini',
        'harga_jual',
        'jumlah',
        'diskon_persen',
        'diskon_nominal',
        'subtotal',
    ];

    protected $casts = [
        'hpp_saat_ini' => 'float',
        'harga_jual' => 'float',
        'jumlah' => 'float',
        'diskon_persen' => 'float',
        'diskon_nominal' => 'float',
        'subtotal' => 'float',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}