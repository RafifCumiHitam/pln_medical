<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDb2 extends Model
{
    protected $connection = 'db2';
    protected $table = 'users';
    public $timestamps = false;
}
