<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['booking_number', 'customer_id', 'branch_id', 'booking_date', 'start_time', 'end_time', 'status', 'notes', 'created_by'];

    protected $casts = ['booking_date' => 'date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }
}
