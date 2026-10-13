<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('code', 'DM')->first();
        if (! $company) {
            return;
        }

        $departments = [
            ['code' => 'OPS', 'name' => 'Operasional'],
            ['code' => 'FIN', 'name' => 'Keuangan'],
            ['code' => 'HR', 'name' => 'HRD'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                ['company_id' => $company->id, 'name' => $dept['name'], 'status' => 'ACTIVE']
            );
        }
    }
}
