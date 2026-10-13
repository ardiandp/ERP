<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = ['supplier_code', 'name', 'contact_person', 'phone', 'email', 'address', 'tax_number', 'payment_terms_days', 'status'];
}
