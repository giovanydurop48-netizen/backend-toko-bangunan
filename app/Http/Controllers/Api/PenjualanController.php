<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenjualanStoreRequest;
use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $query = Penjualan::with(['user:id,nama', 'pembeli:id,nama', 'detailPenjualan.produk:id,nama_produk,satuan_dasar']);

        // Kasir hanya bisa melihat riwayat transaksinya sendiri
        if (auth()->user()->role === 'kasir') {
            $query->where('user_id', auth()->id());
        }

        return response()->json(['success' => true, 'data' => $query->latest()->get()]);
    }

    public function store(PenjualanStoreRequest $request)
    {
        $user = auth()->user();

        return DB::transaction(function () use ($request, $user) {
            $subtotal = 0;
            $diskonTotal = 0;
            $detailItems = [];

            foreach ($request->items as $item) {
                $produk = Produk::lockForUpdate()->findOrFail($item['produk_id']);

                if ($produk->stok < $item['jumlah']) {
                    throw new \Exception("Stok {$produk->nama_produk} tidak mencukupi (Tersedia: {$produk->stok} {$produk->satuan_dasar})");
                }

                $hargaJual = $produk->harga_jual;
                $jumlah = $item['jumlah'];
                $diskonPersen = $item['diskon_persen'] ?? 0;
                $diskonNominal = ($hargaJual * $jumlah) * ($diskonPersen / 100);
                $itemSubtotal = ($hargaJual * $jumlah) - $diskonNominal;

                $subtotal += ($hargaJual * $jumlah);
                $diskonTotal += $diskonNominal;

                // Pengurangan Stok
                $stokAwal = $produk->stok;
                $produk->stok -= $jumlah;
                $produk->save();

                // Audit Log Stok
                RiwayatStok::create([
                    'produk_id' => $produk->id,
                    'user_id' => $user->id,
                    'jenis_perubahan' => 'penjualan',
                    'stok_awal' => $stokAwal,
                    'jumlah_perubahan' => -$jumlah,
                    'stok_akhir' => $produk->stok,
                    'keterangan' => 'Penjualan Kasir',
                ]);

                $detailItems[] = [
                    'produk_id' => $produk->id,
                    'hpp_saat_ini' => $produk->hpp_rata_rata, // Kunci HPP Moving Average
                    'harga_jual' => $hargaJual,
                    'jumlah' => $jumlah,
                    'diskon_persen' => $diskonPersen,
                    'diskon_nominal' => $diskonNominal,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $totalAkhir = $subtotal - $diskonTotal;
            if ($request->bayar < $totalAkhir) {
                throw new \Exception("Uang pembayaran kurang dari total tagihan.");
            }

            $kembalian = $request->bayar - $totalAkhir;
            $nomorTransaksi = 'TRX-' . date('YmdHis') . '-' . rand(100, 999);

            $penjualan = Penjualan::create([
                'nomor_transaksi' => $nomorTransaksi,
                'user_id' => $user->id,
                'pembeli_id' => $request->pembeli_id,
                'tanggal' => now(),
                'subtotal' => $subtotal,
                'diskon_total' => $diskonTotal,
                'total_akhir' => $totalAkhir,
                'metode_pembayaran' => $request->metode_pembayaran,
                'referensi_pembayaran' => $request->referensi_pembayaran,
                'bayar' => $request->bayar,
                'kembalian' => $kembalian,
                'status' => 'selesai',
            ]);

            foreach ($detailItems as $detail) {
                $detail['penjualan_id'] = $penjualan->id;
                DetailPenjualan::create($detail);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi penjualan berhasil',
                'data' => $penjualan->load('detailPenjualan.produk'),
            ], 201);
        });
    }

    public function show($id)
    {
        $penjualan = Penjualan::with(['user:id,nama', 'pembeli', 'detailPenjualan.produk'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $penjualan]);
    }

    public function batal(Request $request, $id)
    {
        $request->validate(['alasan_pembatalan' => 'required|string']);

        return DB::transaction(function () use ($request, $id) {
            $penjualan = Penjualan::with('detailPenjualan')->findOrFail($id);

            if ($penjualan->status === 'dibatalkan') {
                return response()->json(['success' => false, 'message' => 'Transaksi ini sudah dibatalkan sebelumnya.'], 400);
            }

            $penjualan->update([
                'status' => 'dibatalkan',
                'alasan_pembatalan' => $request->alasan_pembatalan,
            ]);

            // Restorasi stok otomatis
            foreach ($penjualan->detailPenjualan as $detail) {
                $produk = Produk::lockForUpdate()->findOrFail($detail->produk_id);
                $stokAwal = $produk->stok;
                $produk->stok += $detail->jumlah;
                $produk->save();

                RiwayatStok::create([
                    'produk_id' => $produk->id,
                    'user_id' => auth()->id(),
                    'jenis_perubahan' => 'pembatalan_penjualan',
                    'stok_awal' => $stokAwal,
                    'jumlah_perubahan' => $detail->jumlah,
                    'stok_akhir' => $produk->stok,
                    'referensi_id' => $penjualan->nomor_transaksi,
                    'keterangan' => 'Pembatalan Transaksi: ' . $request->alasan_pembatalan,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Transaksi berhasil dibatalkan dan stok telah dikembalikan']);
        });
    }
}