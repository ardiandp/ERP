<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePackage extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'description', 'price', 'valid_from', 'valid_until', 'status'];

    public function items()
    {
        return $this->hasMany(ServicePackageItem::class, 'package_id');
    }
}
