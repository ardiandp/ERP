<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = ['asset_code', 'asset_category_id', 'name', 'purchase_date', 'purchase_price', 'branch_id', 'location', 'condition', 'status', 'notes'];

    protected $casts = ['purchase_date' => 'date', 'purchase_price' => 'decimal:2'];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function movements()
    {
        return $this->hasMany(AssetMovement::class)->latest('id');
    }
}
