<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produk';

    protected $fillable = [
        'kategori_id',
        'kode_produk',
        'nama_produk',
        'satuan_dasar',
        'harga_beli_terakhir',
        'hpp_rata_rata',
        'harga_jual',
        'stok',
        'stok_minimum',
        'tanggal_expired',
    ];

    protected $casts = [
        'harga_beli_terakhir' => 'float',
        'hpp_rata_rata' => 'float',
        'harga_jual' => 'float',
        'stok' => 'float',
        'stok_minimum' => 'float',
        'tanggal_expired' => 'date',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'produk_id');
    }

    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelian::class, 'produk_id');
    }

    public function barangKeluar()
    {
        return $this->hasMany(BarangKeluar::class, 'produk_id');
    }

    public function riwayatStok()
    {
        return $this->hasMany(RiwayatStok::class, 'produk_id');
    }
}