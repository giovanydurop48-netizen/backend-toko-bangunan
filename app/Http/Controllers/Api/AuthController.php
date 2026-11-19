<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function __construct()
    {
        // Pastikan method 'login' TIDAK terkena middleware auth:sanctum
        $this->middleware('auth:sanctum')->except(['login']);
    }

    public function login(Request $request)
    {
        $loginInput = $request->input('login') ?? $request->input('username') ?? $request->input('email');
        $password   = $request->input('password');

        if (empty($loginInput) || empty($password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username/email dan password wajib diisi.',
            ], 422);
        }

        $user = User::query()
            ->where('email', $loginInput)
            ->when(Schema::hasColumn('users', 'username'), function ($q) use ($loginInput) {
                $q->orWhere('username', $loginInput);
            })
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username/email atau password salah.',
            ], 401);
        }

        $token = method_exists($user, 'createToken') 
            ? $user->createToken('pos_token')->plainTextToken 
            : 'pos_session_token';

        $userData = [
            'id'       => $user->id,
            'nama'     => $user->nama ?? $user->name ?? 'Kasir',
            'username' => $user->email,
            'role'     => $user->role ?? 'kasir',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => $userData,
            'data'    => [
                'token' => $token,
                'user'  => $userData,
            ],
        ], 200);
    }
}