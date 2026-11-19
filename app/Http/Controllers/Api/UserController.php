<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return response()->json(['success' => true, 'data' => $users]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,kasir,owner',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        return response()->json(['success' => true, 'message' => 'Pengguna berhasil ditambahkan', 'data' => $user], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'email' => 'required|email|max:100|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,kasir,owner',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return response()->json(['success' => true, 'message' => 'Pengguna berhasil diperbarui', 'data' => $user]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri'], 400);
        }
        $user->delete();
        return response()->json(['success' => true, 'message' => 'Pengguna berhasil dihapus']);
    }
}