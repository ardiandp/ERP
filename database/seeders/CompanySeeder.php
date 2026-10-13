<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::updateOrCreate(
            ['code' => 'DM'],
            ['name' => 'Dian Mustika', 'legal_name' => 'Dian Mustika', 'status' => 'ACTIVE']
        );
    }
}
