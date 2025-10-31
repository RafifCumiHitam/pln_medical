<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Visitor;
use App\Models\Prescription;
use App\Models\Medicine;
use App\Models\Karyawan;
use Faker\Factory as Faker;
use Carbon\Carbon;

class VisitorSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Pastikan ada minimal 3 medicine di tabel medicines
        $medicines = Medicine::all();
        if ($medicines->count() < 3) {
            Medicine::factory()->count(5)->create();
            $medicines = Medicine::all();
        }

        // Ambil semua karyawan dari DB
        $karyawans = Karyawan::all();

        for ($i = 1; $i <= 10; $i++) {
            // Tentukan kategori
            $kategori = $faker->randomElement(['karyawan', 'non_karyawan']);

            if ($kategori === 'karyawan' && $karyawans->count() > 0) {
                // Ambil karyawan random dari DB
                $karyawan = $karyawans->random();
                $detail = [
                    'nid' => $karyawan->nid,
                    'nama' => $karyawan->nama_karyawan,
                ];
            } else {
                // non_karyawan dummy
                $detail = [
                    'nama' => $faker->name,
                    'asal' => $faker->company,
                ];
            }

            // Simpan visitor
            $visitor = Visitor::create([
                'kategori' => $kategori,
                'detail' => $detail,
                'tanggal_kunjungan' => Carbon::now()->format('Y-m-d'),
                'keluhan' => $faker->sentence,
                'diagnosis' => $faker->sentence,
                'tindakan' => $faker->sentence,
                'cek_tensi' => $faker->randomElement(['120/80', '130/85', '110/70']),
                'cek_suhu' => $faker->randomFloat(1, 36, 38),
                'heart_rate' => $faker->numberBetween(60, 100),
                'respiratory_rate' => $faker->numberBetween(12, 25),
                'user_id' => 1, // sesuaikan dengan user admin / dummy
            ]);

            // Tambahkan random prescriptions (1-3 obat)
            $prescriptionCount = rand(1, 3);
            $medicineSamples = $medicines->random($prescriptionCount);
            foreach ($medicineSamples as $medicine) {
                Prescription::create([
                    'visitor_id' => $visitor->id,
                    'medicine_id' => $medicine->id,
                    'jumlah' => rand(1, 5),
                    'aturan_pakai' => $faker->randomElement(['2x sehari', '3x sehari', '1x sehari']),
                    'keterangan' => $faker->sentence,
                ]);
            }
        }
    }
}
