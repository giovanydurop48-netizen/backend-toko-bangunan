<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\Pengeluaran;

class DashboardController extends Controller
{
    public function index()
    {
        $today = date('Y-m-d');
        $user = auth()->user();

        $data = [
            'total_produk' => Produk::count(),
            'stok_menipis' => Produk::whereRaw('stok <= stok_minimum')->count(),
            'penjualan_hari_ini' => Penjualan::where('status', 'selesai')->whereDate('tanggal', $today)->sum('total_akhir'),
            'transaksi_hari_ini' => Penjualan::where('status', 'selesai')->whereDate('tanggal', $today)->count(),
        ];

        if (in_array($user->role, ['admin', 'owner'])) {
            $data['pembelian_hari_ini'] = Pembelian::whereDate('tanggal', $today)->sum('total_harga');
            $data['pengeluaran_hari_ini'] = Pengeluaran::whereDate('tanggal', $today)->sum('nominal');
        }

        return response()->json(['success' => true, 'data' => $data]);
    }
}