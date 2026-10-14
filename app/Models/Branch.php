<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['company_id', 'code', 'name', 'branch_type', 'phone', 'email', 'address', 'status'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function salesTransactions()
    {
        return $this->hasMany(SalesTransaction::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
