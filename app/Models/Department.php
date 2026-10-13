<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['company_id', 'code', 'name', 'status'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}
