<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Kategori::latest()->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:50',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $kategori = Kategori::create($validated);
        return response()->json(['success' => true, 'message' => 'Kategori berhasil dibuat', 'data' => $kategori], 201);
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:50',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $kategori->update($validated);
        return response()->json(['success' => true, 'message' => 'Kategori berhasil diubah', 'data' => $kategori]);
    }

    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();
        return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus']);
    }
}