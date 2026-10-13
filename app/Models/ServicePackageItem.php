<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackageItem extends Model
{
    protected $fillable = ['package_id', 'service_id', 'quantity'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
