<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = ['kategori', 'detail', 'tanggal_kunjungan', 'keluhan', 'diagnosis', 'tindakan', 'user_id', 'cek_tensi', 'heart_rate', 'respiratory_rate', 'cek_suhu', ];

    protected $casts = [
        'detail' => 'array',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}