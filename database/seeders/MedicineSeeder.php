<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicine;
use App\Models\MedicineStock;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MedicineSeeder extends Seeder
{
    public function run()
    {
        $medicineNames = [
            'Paracetamol', 'Amoxicillin', 'Ibuprofen', 
            'Vitamin C', 'Cefalexin', 'Aspirin', 
            'Metformin', 'Cetirizine', 'Omeprazole'
        ];

        foreach ($medicineNames as $index => $name) {
            $stokMinim = rand(20, 50); // stok minimum random
            $stokAwal = rand(0, 200);  // stok awal random

            // random expired date: beberapa dekat kadaluarsa (<=30 hari)
            $expiredOffsetDays = rand(5, 365); // 5–365 hari ke depan
            $expiredDate = Carbon::now()->addDays($expiredOffsetDays)->format('Y-m-d');

            $medicine = Medicine::create([
                'nama_obat' => $name,
                'kode_obat' => 'MED'.str_pad($index+1, 3, '0', STR_PAD_LEFT),
                'kategori' => 'Kategori '.($index+1),
                'satuan' => 'Tablet',
                'stok_minim' => $stokMinim,
                'expired_date' => $expiredDate,
            ]);

            // Tentukan status stok awal
            if ($stokAwal <= 0) {
                $statusStok = 'habis';
            } elseif ($stokAwal <= $stokMinim) {
                $statusStok = 'menipis';
            } else {
                $statusStok = 'aman';
            }

            $medicine->status_stok = $statusStok;

            // Buat log stok awal di tabel medicine_stocks
            MedicineStock::create([
                'medicine_id' => $medicine->id,
                'jumlah_masuk' => $stokAwal,
                'jumlah_keluar' => 0,
                'stok_akhir' => $stokAwal,
                'tanggal_transaksi' => now(),
                'keterangan' => 'Stok awal seeding',
                'user_id' => 1,
            ]);
        }
    }
}
