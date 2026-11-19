<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembeli extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pembeli';

    protected $fillable = [
        'kode_pembeli',
        'nama',
        'telepon',
        'alamat',
    ];

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'pembeli_id');
    }
}