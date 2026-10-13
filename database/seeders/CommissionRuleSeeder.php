<?php

namespace Database\Seeders;

use App\Models\CommissionRule;
use Illuminate\Database\Seeder;

class CommissionRuleSeeder extends Seeder
{
    public function run(): void
    {
        CommissionRule::updateOrCreate(
            ['employee_id' => null, 'service_id' => null, 'effective_date' => now()->startOfYear()->toDateString()],
            [
                'commission_type' => 'PERCENTAGE',
                'commission_value' => 10,
                'end_date' => null,
                'status' => 'ACTIVE',
            ]
        );
    }
}
