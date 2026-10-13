<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_code', 'name', 'gender', 'birth_date', 'phone', 'email', 'address', 'customer_type', 'source', 'notes', 'status'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function salesTransactions()
    {
        return $this->hasMany(SalesTransaction::class);
    }
}
