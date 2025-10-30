<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $fillable = ['nama_obat', 'kode_obat', 'kategori', 'satuan', 'stok_minim', 'expired_date',];

    public function stocks()
    {
        return $this->hasMany(MedicineStock::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
    public function latestStock()
    {
        return $this->hasOne(MedicineStock::class)->latestOfMany('id');
    }
    public function getCurrentStockAttribute()
    {
        return $this->latestStock ? $this->latestStock->stok_akhir : 0;
    }


}