<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'MASSAGE', 'name' => 'Massage'],
            ['code' => 'FACIAL', 'name' => 'Facial'],
            ['code' => 'REFLEXOLOGY', 'name' => 'Reflexology'],
        ];

        foreach ($categories as $category) {
            ServiceCategory::updateOrCreate(
                ['code' => $category['code']],
                ['name' => $category['name'], 'status' => 'ACTIVE']
            );
        }
    }
}
