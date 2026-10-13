<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = ['warehouse_id', 'product_id', 'movement_type', 'quantity', 'unit_cost', 'reference_type', 'reference_id', 'movement_date', 'notes', 'created_by'];

    protected $casts = ['movement_date' => 'date'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
