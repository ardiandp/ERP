<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalAccountCategory extends Model
{
    protected $fillable = ['code', 'name', 'status'];

    public function accounts()
    {
        return $this->hasMany(DigitalAccount::class, 'category_id');
    }
}
