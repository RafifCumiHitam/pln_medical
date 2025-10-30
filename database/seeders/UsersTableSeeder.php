<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nama_lengkap' => 'Admin',
            'nid' => 'ADMIN001',
            'no_telepon' => '08123456789',
            'password' => bcrypt('password'),
        ]);
    }
}
