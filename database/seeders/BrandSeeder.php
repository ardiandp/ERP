<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['code' => 'DM-BOTANIC', 'name' => 'Dian Mustika Botanicals', 'status' => 'ACTIVE'],
            ['code' => 'DM-SKIN', 'name' => 'Dian Mustika Skincare', 'status' => 'ACTIVE'],
            ['code' => 'HERB-CARE', 'name' => 'Mustika Care Herbal', 'status' => 'ACTIVE'],
            ['code' => 'WARDAH', 'name' => 'Wardah Professional', 'status' => 'ACTIVE'],
            ['code' => 'SENSATIA', 'name' => 'Sensatia Botanicals', 'status' => 'ACTIVE'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(['code' => $brand['code']], $brand);
        }
    }
}

