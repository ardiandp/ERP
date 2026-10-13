<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('code', 'DM')->first();
        if (! $company) {
            return;
        }

        Branch::updateOrCreate(
            ['code' => 'DM-PUSAT'],
            ['company_id' => $company->id, 'name' => 'Pusat', 'branch_type' => 'HEAD_OFFICE', 'status' => 'ACTIVE']
        );
    }
}
