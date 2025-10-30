<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $fillable = ['visitor_id', 'medicine_id', 'jumlah', 'aturan_pakai', 'keterangan'];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}