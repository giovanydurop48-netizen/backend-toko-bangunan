<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'nama'     => 'Administrator Toko',
                'username' => 'admin',
                'email'    => 'admin@keuangan.local',
                'password' => 'admin123',
                'role'     => 'admin',
            ],
            [
                'nama'     => 'Pemilik Toko (Owner)',
                'username' => 'owner',
                'email'    => 'owner@keuangan.local',
                'password' => 'owner123',
                'role'     => 'owner',
            ],
            [
                'nama'     => 'Kasir POS',
                'username' => 'kasir',
                'email'    => 'kasir@keuangan.local',
                'password' => 'kasir123',
                'role'     => 'kasir',
            ],
        ];

        foreach ($accounts as $acc) {
            $data = [
                'password'   => Hash::make($acc['password']),
                'role'       => $acc['role'],
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('users', 'nama')) {
                $data['nama'] = $acc['nama'];
            }
            if (Schema::hasColumn('users', 'name')) {
                $data['name'] = $acc['nama'];
            }
            if (Schema::hasColumn('users', 'username')) {
                $data['username'] = $acc['username'];
            }

            $exists = DB::table('users')->where('email', $acc['email'])->exists();
            if ($exists) {
                DB::table('users')->where('email', $acc['email'])->update($data);
            } else {
                $data['email'] = $acc['email'];
                $data['created_at'] = now();
                DB::table('users')->insert($data);
            }
        }
    }
}