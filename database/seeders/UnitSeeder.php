<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['code' => 'PCS', 'name' => 'Pieces', 'decimal_scale' => 0],
            ['code' => 'BOX', 'name' => 'Box', 'decimal_scale' => 0],
            ['code' => 'ML', 'name' => 'Milliliter', 'decimal_scale' => 2],
            ['code' => 'GR', 'name' => 'Gram', 'decimal_scale' => 2],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                ['name' => $unit['name'], 'decimal_scale' => $unit['decimal_scale'], 'status' => 'ACTIVE']
            );
        }
    }
}
