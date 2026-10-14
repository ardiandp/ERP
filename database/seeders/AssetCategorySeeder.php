<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'ELEC', 'name' => 'Elektronik'],
            ['code' => 'FURN', 'name' => 'Furniture'],
            ['code' => 'VEHI', 'name' => 'Kendaraan'],
            ['code' => 'EQPT', 'name' => 'Peralatan'],
        ];

        foreach ($categories as $category) {
            AssetCategory::updateOrCreate(
                ['code' => $category['code']],
                ['name' => $category['name'], 'status' => 'ACTIVE']
            );
        }
    }
}
