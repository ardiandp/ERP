<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['branch_id', 'code', 'name', 'warehouse_type', 'address', 'status'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function stocks()
    {
        return $this->hasMany(InventoryStock::class);
    }
}
