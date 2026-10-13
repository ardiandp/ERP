<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $fillable = ['opname_number', 'warehouse_id', 'opname_date', 'status', 'created_by', 'approved_by', 'approved_at', 'notes'];

    protected $casts = ['opname_date' => 'date', 'approved_at' => 'datetime'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }
}
