<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['code', 'name', 'legal_name', 'tax_number', 'phone', 'email', 'address', 'status'];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
