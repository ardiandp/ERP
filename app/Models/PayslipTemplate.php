<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayslipTemplate extends Model
{
    protected $fillable = ['code', 'name', 'layout_config', 'status', 'is_default'];

    protected $casts = ['layout_config' => 'array', 'is_default' => 'boolean'];
}
