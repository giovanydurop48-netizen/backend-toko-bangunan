<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Pengeluaran::with('user:id,nama')->latest()->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_pengeluaran' => 'required|in:operasional,lain_lain',
            'nama_pengeluaran' => 'required|string|max:150',
            'nominal' => 'required|numeric|gt:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['nomor_pengeluaran'] = 'EXP-' . date('YmdHis');
        $validated['user_id'] = auth()->id();
        $validated['tanggal'] = now();

        $pengeluaran = Pengeluaran::create($validated);
        return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil dicatat', 'data' => $pengeluaran], 201);
    }
}