<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $fillable = ['nama_karyawan', 'no_rm', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'no_telepon', 'nid'];

    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }
}