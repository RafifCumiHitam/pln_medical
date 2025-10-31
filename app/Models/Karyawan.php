<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Karyawan extends Model
{
    protected $fillable = [
        'nama_karyawan',
        'no_rm',
        'tanggal_lahir',
        'jenis_kelamin',
        'no_telepon',
        'nid'
    ];

    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }

    /**
     * Fetch data dari DB2 dan langsung insert/update ke tabel karyawans
     */
    public static function importFromDb2()
    {
        // Ambil data dari DB2 (users + profiles)
        $users = DB::connection('db2')->table('users as u')
            ->join('profiles as p', 'u.nid', '=', 'p.nid')
            ->select('u.nid', 'u.name', 'p.gender', 'p.tahun_lahir', 'p.telp')
            ->get();

        return $users->map(function($u){
            // Mapping gender sesuai DB1
            $jk = strtoupper($u->gender);
            if (in_array($jk, ['L', 'LAKI', 'LAKI-LAKI'])) {
                $jk = 'L';
            } elseif (in_array($jk, ['P', 'PEREMPUAN'])) {
                $jk = 'P';
            } else {
                $jk = null;
            }

            return (object)[
                'nid' => $u->nid,
                'nama_karyawan' => $u->name,
                'jenis_kelamin' => $jk,
                'tanggal_lahir' => $u->tahun_lahir, // hanya tahun, sesuai DB2
                'no_telepon' => $u->telp,
                'no_rm' => 'RM' . time() . rand(100, 999),
            ];
        });
    }

    public function syncKaryawanDb2ToDb1($nid)
    {
        // Ambil data karyawan dari DB2
        $karyawanDb2 = DB::connection('db2')->table('users as u')
            ->join('profiles as p', 'u.nid', '=', 'p.nid')
            ->where('u.nid', $nid)
            ->select('u.nid', 'u.name', 'p.gender', 'p.tahun_lahir', 'p.telp')
            ->first();

        if (!$karyawanDb2) {
            return null;
        }

        // Mapping gender
        $jk = strtoupper($karyawanDb2->gender);
        if (in_array($jk, ['L', 'LAKI', 'LAKI-LAKI'])) {
            $jk = 'L';
        } elseif (in_array($jk, ['P', 'PEREMPUAN'])) {
            $jk = 'P';
        } else {
            $jk = null;
        }

        // Simpan hanya tahun lahir (bukan format tanggal penuh)
        $tahunLahir = $karyawanDb2->tahun_lahir;

        // Cek apakah karyawan sudah ada di DB1
        $karyawan = Karyawan::where('nid', $karyawanDb2->nid)->first();

        if ($karyawan) {
            // Update data
            $karyawan->update([
                'nama_karyawan' => $karyawanDb2->name,
                'jenis_kelamin' => $jk,
                'tanggal_lahir' => $tahunLahir, // hanya tahun
                'no_telepon' => $karyawanDb2->telp,
            ]);
        } else {
            // Generate no_rm unik
            $no_rm = 'RM' . time() . rand(100, 999);

            // Insert data baru
            $karyawan = Karyawan::create([
                'nid' => $karyawanDb2->nid,
                'nama_karyawan' => $karyawanDb2->name,
                'jenis_kelamin' => $jk,
                'tanggal_lahir' => $tahunLahir, // hanya tahun
                'no_telepon' => $karyawanDb2->telp,
                'no_rm' => $no_rm,
            ]);
        }

        return $karyawan;
    }
}
