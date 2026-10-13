<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    protected $fillable = ['booking_id', 'service_id', 'package_id', 'employee_id', 'scheduled_start', 'scheduled_end', 'status', 'notes'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
