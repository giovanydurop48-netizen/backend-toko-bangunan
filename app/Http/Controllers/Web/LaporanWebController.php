<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanWebController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $transaksi = Transaksi::with(['user', 'items.produk'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->get();

        $totalOmset = $transaksi->sum('total_bayar');
        $totalTransaksi = $transaksi->count();

        return view('laporan.index', compact('transaksi', 'startDate', 'endDate', 'totalOmset', 'totalTransaksi'));
    }

    public function cetak(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $transaksi = Transaksi::with(['user'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->get();

        $totalOmset = $transaksi->sum('total_bayar');

        return view('laporan.cetak', compact('transaksi', 'startDate', 'endDate', 'totalOmset'));
    }
}