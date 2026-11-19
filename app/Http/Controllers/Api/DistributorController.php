<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DistributorController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Distributor::latest()->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_distributor' => 'required|string|max:100',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        $validated['kode_distributor'] = 'DST-' . strtoupper(Str::random(6));
        $distributor = Distributor::create($validated);

        return response()->json(['success' => true, 'message' => 'Distributor berhasil dibuat', 'data' => $distributor], 201);
    }

    public function update(Request $request, $id)
    {
        $distributor = Distributor::findOrFail($id);
        $validated = $request->validate([
            'nama_distributor' => 'required|string|max:100',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        $distributor->update($validated);
        return response()->json(['success' => true, 'message' => 'Distributor berhasil diubah', 'data' => $distributor]);
    }

    public function destroy($id)
    {
        $distributor = Distributor::findOrFail($id);
        $distributor->delete();
        return response()->json(['success' => true, 'message' => 'Distributor berhasil dihapus']);
    }
}