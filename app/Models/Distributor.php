<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distributor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'distributor';

    protected $fillable = [
        'kode_distributor',
        'nama_distributor',
        'telepon',
        'alamat',
    ];

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'distributor_id');
    }
}