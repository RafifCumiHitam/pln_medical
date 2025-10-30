<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicineStock extends Model
{
    use SoftDeletes;

    protected $fillable = ['medicine_id', 'jumlah_masuk', 'jumlah_keluar', 'stok_akhir', 'tanggal_transaksi', 'keterangan', 'user_id', 'old_data', 'new_data'];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}