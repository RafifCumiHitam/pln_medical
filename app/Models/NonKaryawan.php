<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NonKaryawan extends Model
{
    protected $fillable = ['nama_pasien', 'no_rm', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'no_telepon'];
    
    public function visitors()
    {
        
        return $this->hasMany(Visitor::class);
    }
}