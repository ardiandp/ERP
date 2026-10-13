<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    protected $fillable = ['code', 'name', 'type', 'calculation_method', 'is_taxable', 'is_bpjs', 'status'];

    protected $casts = ['is_taxable' => 'boolean', 'is_bpjs' => 'boolean'];

    public function employeeComponents()
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }
}
