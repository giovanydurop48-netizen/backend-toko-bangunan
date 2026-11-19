<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembeli;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PembeliController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Pembeli::latest()->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        $validated['kode_pembeli'] = 'PBL-' . strtoupper(Str::random(6));
        $pembeli = Pembeli::create($validated);

        return response()->json(['success' => true, 'message' => 'Data pembeli berhasil dibuat', 'data' => $pembeli], 201);
    }

    public function update(Request $request, $id)
    {
        $pembeli = Pembeli::findOrFail($id);
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        $pembeli->update($validated);
        return response()->json(['success' => true, 'message' => 'Data pembeli berhasil diubah', 'data' => $pembeli]);
    }

    public function destroy($id)
    {
        $pembeli = Pembeli::findOrFail($id);
        $pembeli->delete();
        return response()->json(['success' => true, 'message' => 'Data pembeli berhasil dihapus']);
    }
}