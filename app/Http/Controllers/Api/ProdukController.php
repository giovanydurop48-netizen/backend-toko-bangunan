<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::with('kategori');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('kode_produk', 'like', "%{$search}%");
            });
        }

        $produk = $query->latest()->get();

        // Sembunyikan harga beli & HPP jika diakses tanpa login (publik) atau oleh kasir
        $user = auth('sanctum')->user() ?? auth()->user();
        if (!$user || $user->role === 'kasir') {
            $produk->makeHidden(['harga_beli_terakhir', 'hpp_rata_rata']);
        }

        return response()->json(['success' => true, 'data' => $produk]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'kode_produk' => 'required|string|max:50|unique:produk',
            'nama_produk' => 'required|string|max:150',
            'satuan_dasar' => 'required|string|max:20',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'stok_minimum' => 'required|numeric|min:0',
            'harga_beli_terakhir' => 'nullable|numeric|min:0',
            'tanggal_expired' => 'nullable|date',
        ]);

        $validated['harga_beli_terakhir'] = $validated['harga_beli_terakhir'] ?? 0;
        $validated['hpp_rata_rata'] = $validated['harga_beli_terakhir'];

        $produk = Produk::create($validated);
        return response()->json(['success' => true, 'message' => 'Produk berhasil ditambahkan', 'data' => $produk], 201);
    }

    public function show($id)
    {
        $produk = Produk::with('kategori')->findOrFail($id);

        $user = auth('sanctum')->user() ?? auth()->user();
        if (!$user || $user->role === 'kasir') {
            $produk->makeHidden(['harga_beli_terakhir', 'hpp_rata_rata']);
        }

        return response()->json(['success' => true, 'data' => $produk]);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'kode_produk' => 'required|string|max:50|unique:produk,kode_produk,' . $id,
            'nama_produk' => 'required|string|max:150',
            'satuan_dasar' => 'required|string|max:20',
            'harga_jual' => 'required|numeric|min:0',
            'stok_minimum' => 'required|numeric|min:0',
            'tanggal_expired' => 'nullable|date',
        ]);

        $produk->update($validated);
        return response()->json(['success' => true, 'message' => 'Produk berhasil diubah', 'data' => $produk]);
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();
        return response()->json(['success' => true, 'message' => 'Produk berhasil dihapus']);
    }
}