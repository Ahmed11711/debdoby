<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = ['device_id', 'active'];

    protected $casts = ['active' => 'boolean'];
}
