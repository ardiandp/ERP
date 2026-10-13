<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = ['service_code', 'category_id', 'name', 'description', 'pricing_method', 'default_duration_minutes', 'status'];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function prices()
    {
        return $this->hasMany(ServicePrice::class);
    }
}
