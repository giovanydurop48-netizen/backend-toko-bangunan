<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangKeluar;
use App\Models\Produk;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangKeluarController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => BarangKeluar::with(['produk', 'user:id,nama'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'jumlah' => 'required|numeric|gt:0',
            'alasan' => 'required|in:rusak,expired,diberikan,penyesuaian_lain',
            'keterangan' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $produk = Produk::lockForUpdate()->findOrFail($validated['produk_id']);

            if ($produk->stok < $validated['jumlah']) {
                throw new \Exception("Stok tidak mencukupi untuk dikeluarkan.");
            }

            $stokAwal = $produk->stok;
            $produk->stok -= $validated['jumlah'];
            $produk->save();

            $totalBeban = $validated['jumlah'] * $produk->hpp_rata_rata;

            $barangKeluar = BarangKeluar::create([
                'nomor_barang_keluar' => 'BK-' . date('YmdHis'),
                'produk_id' => $produk->id,
                'user_id' => auth()->id(),
                'tanggal' => now(),
                'jumlah' => $validated['jumlah'],
                'alasan' => $validated['alasan'],
                'hpp_saat_ini' => $produk->hpp_rata_rata,
                'total_beban' => $totalBeban,
                'keterangan' => $validated['keterangan'],
            ]);

            RiwayatStok::create([
                'produk_id' => $produk->id,
                'user_id' => auth()->id(),
                'jenis_perubahan' => 'barang_keluar',
                'stok_awal' => $stokAwal,
                'jumlah_perubahan' => -$validated['jumlah'],
                'stok_akhir' => $produk->stok,
                'referensi_id' => $barangKeluar->nomor_barang_keluar,
                'keterangan' => 'Barang Keluar: ' . $validated['alasan'],
            ]);

            return response()->json(['success' => true, 'message' => 'Barang keluar berhasil dicatat sebagai beban', 'data' => $barangKeluar], 201);
        });
    }
}