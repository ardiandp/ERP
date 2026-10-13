<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = ['parent_id', 'code', 'name', 'description', 'status'];

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }
}
