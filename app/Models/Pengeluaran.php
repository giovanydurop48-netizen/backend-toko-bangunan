<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran';

    protected $fillable = [
        'nomor_pengeluaran',
        'user_id',
        'tanggal',
        'kategori_pengeluaran',
        'nama_pengeluaran',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'nominal' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}