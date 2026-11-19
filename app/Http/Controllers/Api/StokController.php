<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\RiwayatStok;

class StokController extends Controller
{
    public function riwayat()
    {
        $riwayat = RiwayatStok::with(['produk:id,nama_produk,satuan_dasar', 'user:id,nama'])
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $riwayat]);
    }

    public function menipis()
    {
        $produk = Produk::whereRaw('stok <= stok_minimum')->get();

        if (auth()->user()->role === 'kasir') {
            $produk->makeHidden(['harga_beli_terakhir', 'hpp_rata_rata']);
        }

        return response()->json(['success' => true, 'data' => $produk]);
    }
}