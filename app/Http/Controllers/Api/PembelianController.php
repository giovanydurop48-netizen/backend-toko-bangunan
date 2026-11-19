<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PembelianStoreRequest;
use App\Models\DetailPembelian;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\RiwayatStok;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Pembelian::with(['distributor', 'user:id,nama', 'detailPembelian.produk'])->latest()->get()
        ]);
    }

    public function store(PembelianStoreRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $totalHarga = 0;
            $detailItems = [];

            foreach ($request->items as $item) {
                $produk = Produk::lockForUpdate()->findOrFail($item['produk_id']);

                $hargaBeliBaru = $item['harga_beli'];
                $jumlahBeli = $item['jumlah'];
                $itemSubtotal = $hargaBeliBaru * $jumlahBeli;
                $totalHarga += $itemSubtotal;

                // Hitung HPP Rata-Rata Bergerak (Moving Average)
                $stokLama = $produk->stok;
                $hppLama = $produk->hpp_rata_rata;
                $stokBaru = $stokLama + $jumlahBeli;

                $hppBaru = ($stokBaru > 0)
                    ? (($stokLama * $hppLama) + ($jumlahBeli * $hargaBeliBaru)) / $stokBaru
                    : $hargaBeliBaru;

                // Update Produk
                $produk->harga_beli_terakhir = $hargaBeliBaru;
                $produk->hpp_rata_rata = $hppBaru;
                $produk->stok = $stokBaru;
                $produk->save();

                // Catat Riwayat Stok
                RiwayatStok::create([
                    'produk_id' => $produk->id,
                    'user_id' => auth()->id(),
                    'jenis_perubahan' => 'pembelian',
                    'stok_awal' => $stokLama,
                    'jumlah_perubahan' => $jumlahBeli,
                    'stok_akhir' => $stokBaru,
                    'keterangan' => 'Pembelian Barang Masuk',
                ]);

                $detailItems[] = [
                    'produk_id' => $produk->id,
                    'harga_beli' => $hargaBeliBaru,
                    'jumlah' => $jumlahBeli,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $pembelian = Pembelian::create([
                'nomor_pembelian' => 'PO-' . date('YmdHis') . '-' . rand(100, 999),
                'distributor_id' => $request->distributor_id,
                'user_id' => auth()->id(),
                'tanggal' => now(),
                'total_harga' => $totalHarga,
                'metode_pembayaran' => $request->metode_pembayaran,
                'keterangan' => $request->keterangan,
            ]);

            foreach ($detailItems as $detail) {
                $detail['pembelian_id'] = $pembelian->id;
                DetailPembelian::create($detail);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil dicatat dan HPP telah diperbarui',
                'data' => $pembelian->load('detailPembelian.produk'),
            ], 201);
        });
    }
}