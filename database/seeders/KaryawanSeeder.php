<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Karyawan;

class KaryawanSeeder extends Seeder
{
    public function run()
    {
        $karyawans = [
            [
                'nama_karyawan' => 'Andi Pratama',
                'no_rm' => 'RM001',
                'tanggal_lahir' => '1990-05-15',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Mawar No. 10, Jakarta',
                'no_telepon' => '081234567890',
                'nid' => 'K001',
            ],
            [
                'nama_karyawan' => 'Budi Santoso',
                'no_rm' => 'RM002',
                'tanggal_lahir' => '1985-09-22',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Melati No. 5, Surabaya',
                'no_telepon' => '081234567891',
                'nid' => 'K002',
            ],
            [
                'nama_karyawan' => 'Citra Dewi',
                'no_rm' => 'RM003',
                'tanggal_lahir' => '1992-03-10',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Anggrek No. 8, Bandung',
                'no_telepon' => '081234567892',
                'nid' => 'K003',
            ],
        ];

        foreach ($karyawans as $karyawan) {
            Karyawan::create($karyawan);
        }
    }
}