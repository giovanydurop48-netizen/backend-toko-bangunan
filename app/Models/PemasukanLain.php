<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemasukanLain extends Model
{
    use HasFactory;

    protected $table = 'pemasukan_lain';

    protected $fillable = [
        'nomor_pemasukan',
        'user_id',
        'tanggal',
        'nama_pemasukan',
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