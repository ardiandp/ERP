<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'SKINCARE', 'name' => 'Skincare'],
            ['code' => 'CONSUMABLE', 'name' => 'Consumable'],
            ['code' => 'RETAIL', 'name' => 'Retail'],
        ];

        foreach ($categories as $category) {
            ProductCategory::updateOrCreate(
                ['code' => $category['code']],
                ['name' => $category['name'], 'status' => 'ACTIVE']
            );
        }
    }
}
