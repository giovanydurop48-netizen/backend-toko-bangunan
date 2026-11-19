<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangKeluar;
use App\Models\DetailPenjualan;
use App\Models\PemasukanLain;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function penjualan(Request $request)
    {
        $query = Penjualan::with(['user:id,nama', 'pembeli:id,nama'])->where('status', 'selesai');

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal', [$request->tanggal_mulai . ' 00:00:00', $request->tanggal_akhir . ' 23:59:59']);
        }

        $penjualan = $query->get();
        return response()->json([
            'success' => true,
            'ringkasan' => [
                'total_transaksi' => $penjualan->count(),
                'total_omset' => $penjualan->sum('total_akhir'),
            ],
            'data' => $penjualan
        ]);
    }

    public function labaRugi(Request $request)
    {
        $mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $akhir = $request->input('tanggal_akhir', date('Y-m-t'));

        // 1. Pendapatan Penjualan
        $pendapatanPenjualan = Penjualan::where('status', 'selesai')
            ->whereBetween('tanggal', [$mulai . ' 00:00:00', $akhir . ' 23:59:59'])
            ->sum('total_akhir');

        // 2. HPP Penjualan (Tercatat saat transaksi)
        $totalHpp = DetailPenjualan::whereHas('penjualan', function ($q) use ($mulai, $akhir) {
            $q->where('status', 'selesai')
              ->whereBetween('tanggal', [$mulai . ' 00:00:00', $akhir . ' 23:59:59']);
        })->sum(DB::raw('hpp_saat_ini * jumlah'));

        // 3. Beban Barang Keluar
        $bebanBarangKeluar = BarangKeluar::whereBetween('tanggal', [$mulai . ' 00:00:00', $akhir . ' 23:59:59'])
            ->sum('total_beban');

        // 4. Pengeluaran Operasional & Lainnya
        $pengeluaran = Pengeluaran::whereBetween('tanggal', [$mulai . ' 00:00:00', $akhir . ' 23:59:59'])
            ->sum('nominal');

        // 5. Pemasukan Lain
        $pemasukanLain = PemasukanLain::whereBetween('tanggal', [$mulai . ' 00:00:00', $akhir . ' 23:59:59'])
            ->sum('nominal');

        $labaKotor = $pendapatanPenjualan - $totalHpp;
        $labaBersih = ($labaKotor + $pemasukanLain) - ($bebanBarangKeluar + $pengeluaran);

        return response()->json([
            'success' => true,
            'periode' => ['mulai' => $mulai, 'akhir' => $akhir],
            'data' => [
                'pendapatan_penjualan' => $pendapatanPenjualan,
                'hpp_penjualan' => $totalHpp,
                'laba_kotor' => $labaKotor,
                'pemasukan_lain' => $pemasukanLain,
                'beban_barang_keluar' => $bebanBarangKeluar,
                'beban_pengeluaran' => $pengeluaran,
                'laba_bersih' => $labaBersih,
            ]
        ]);
    }
}