<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            'OPS' => [
                ['code' => 'THERAPIST', 'name' => 'Therapist'],
                ['code' => 'CASHIER', 'name' => 'Kasir'],
            ],
            'FIN' => [
                ['code' => 'ACCOUNTANT', 'name' => 'Akuntan'],
            ],
            'HR' => [
                ['code' => 'HR-STAFF', 'name' => 'HR Staff'],
            ],
        ];

        foreach ($positions as $deptCode => $items) {
            $department = Department::where('code', $deptCode)->first();
            if (! $department) {
                continue;
            }
            foreach ($items as $item) {
                Position::updateOrCreate(
                    ['code' => $item['code']],
                    ['department_id' => $department->id, 'name' => $item['name'], 'status' => 'ACTIVE']
                );
            }
        }
    }
}
