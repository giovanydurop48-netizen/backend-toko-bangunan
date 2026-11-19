<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama' => 'Administrator Toko',
                'email' => 'admin@tokobangunan.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 2. Akun Kasir
        User::updateOrCreate(
            ['username' => 'kasir'],
            [
                'nama' => 'Kasir Utama',
                'email' => 'kasir@tokobangunan.com',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
            ]
        );

        // 3. Akun Owner
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'nama' => 'Pemilik Toko',
                'email' => 'owner@tokobangunan.com',
                'password' => Hash::make('password123'),
                'role' => 'owner',
            ]
        );
    }
}