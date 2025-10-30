<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;

class User extends Model implements Authenticatable
{
    use AuthenticatableTrait;

    protected $fillable = ['nid', 'password', 'nama_lengkap', 'no_telepon'];
    protected $hidden = ['password'];

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }

    public function medicineStocks()
    {
        return $this->hasMany(MedicineStock::class);
    }
}