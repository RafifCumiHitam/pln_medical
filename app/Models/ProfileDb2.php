<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileDb2 extends Model
{
    protected $connection = 'db2';
    protected $table = 'profiles';
    public $timestamps = false;
}
