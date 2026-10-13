<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeFamilyMember extends Model
{
    protected $table = 'employee_family_members';

    protected $fillable = ['employee_id', 'name', 'relationship', 'nik', 'birth_date', 'gender', 'phone', 'address', 'is_dependent', 'notes'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
