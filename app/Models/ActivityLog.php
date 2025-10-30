<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'activity_type', 'table_name', 'record_id', 'old_data', 'new_data'];
    
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'detail' => 'array',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}