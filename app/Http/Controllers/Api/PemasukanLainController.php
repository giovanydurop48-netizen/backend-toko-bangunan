<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PemasukanLain;
use Illuminate\Http\Request;

class PemasukanLainController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => PemasukanLain::with('user:id,nama')->latest()->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pemasukan' => 'required|string|max:150',
            'nominal' => 'required|numeric|gt:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['nomor_pemasukan'] = 'INC-' . date('YmdHis');
        $validated['user_id'] = auth()->id();
        $validated['tanggal'] = now();

        $pemasukan = PemasukanLain::create($validated);
        return response()->json(['success' => true, 'message' => 'Pemasukan lain berhasil dicatat', 'data' => $pemasukan], 201);
    }
}