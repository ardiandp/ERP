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
}
