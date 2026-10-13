<?php

namespace Database\Seeders;

use App\Models\SalaryComponent;
use Illuminate\Database\Seeder;

class SalaryComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            ['code' => 'BASIC', 'name' => 'Basic Salary', 'type' => 'EARNING', 'calculation_method' => 'FIXED', 'is_taxable' => false, 'is_bpjs' => false],
            ['code' => 'ALLOWANCE', 'name' => 'Tunjangan', 'type' => 'EARNING', 'calculation_method' => 'FIXED', 'is_taxable' => true, 'is_bpjs' => false],
            ['code' => 'BONUS', 'name' => 'Bonus', 'type' => 'EARNING', 'calculation_method' => 'FIXED', 'is_taxable' => true, 'is_bpjs' => false],
            ['code' => 'COMMISSION', 'name' => 'Komisi', 'type' => 'EARNING', 'calculation_method' => 'FIXED', 'is_taxable' => true, 'is_bpjs' => false],
            ['code' => 'BPJS', 'name' => 'BPJS', 'type' => 'DEDUCTION', 'calculation_method' => 'FIXED', 'is_taxable' => false, 'is_bpjs' => true],
            ['code' => 'DEDUCTION', 'name' => 'Potongan', 'type' => 'DEDUCTION', 'calculation_method' => 'FIXED', 'is_taxable' => false, 'is_bpjs' => false],
        ];

        foreach ($components as $component) {
            SalaryComponent::updateOrCreate(
                ['code' => $component['code']],
                [
                    'name' => $component['name'],
                    'type' => $component['type'],
                    'calculation_method' => $component['calculation_method'],
                    'is_taxable' => $component['is_taxable'],
                    'is_bpjs' => $component['is_bpjs'],
                    'status' => 'ACTIVE',
                ]
            );
        }
    }
}
