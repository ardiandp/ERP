<?php

namespace Database\Seeders;

use App\Models\PayslipTemplate;
use Illuminate\Database\Seeder;

class PayslipTemplateSeeder extends Seeder
{
    public function run(): void
    {
        PayslipTemplate::updateOrCreate(
            ['code' => 'STANDARD'],
            [
                'name' => 'Standard Payslip',
                'layout_config' => [
                    'show_company' => true,
                    'show_employee' => true,
                    'show_verification' => true,
                    'sections' => ['earnings', 'deductions', 'summary'],
                ],
                'status' => 'ACTIVE',
                'is_default' => true,
            ]
        );
    }
}
