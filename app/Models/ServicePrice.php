<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePrice extends Model
{
    protected $fillable = ['service_id', 'duration_minutes', 'min_weight', 'max_weight', 'price', 'effective_date', 'end_date'];
}
