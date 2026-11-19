<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'total_kotor' => 'required|numeric',
            'diskon_persen' => 'required|numeric|max:10',
            'total_bayar' => 'required|numeric',
            'nominal_bayar' => 'required|numeric',
            'items' => 'required|array',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric',
            'items.*.subtotal' => 'required|numeric',
        ]);

        if ($request->nominal_bayar < $request->total_bayar) {
            return response()->json([
                'success' => false,
                'message' => 'Uang pembayaran kurang!'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Simpan Header Transaksi
            $kodeTransaksi = 'TRX-' . date('YmdHis');
            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'user_id' => $request->user()->id,
                'total_kotor' => $request->total_kotor,
                'diskon_persen' => $request->diskon_persen,
                'total_bayar' => $request->total_bayar,
                'nominal_bayar' => $request->nominal_bayar,
                'kembalian' => $request->nominal_bayar - $request->total_bayar,
            ]);

            // 2. Simpan Detail Transaksi & Kurangkan Stok Produk
            foreach ($request->items as $item) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $item['produk_id'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Potong stok di database
                $produk = Produk::find($item['produk_id']);
                if ($produk) {
                    $produk->decrement('stok', $item['jumlah']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses',
                'data' => $transaksi
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}