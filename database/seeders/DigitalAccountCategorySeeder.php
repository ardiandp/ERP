<?php

namespace Database\Seeders;

use App\Models\DigitalAccountCategory;
use Illuminate\Database\Seeder;

class DigitalAccountCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'DOMAIN', 'name' => 'Domain'],
            ['code' => 'HOSTING', 'name' => 'Hosting'],
            ['code' => 'EMAIL', 'name' => 'Email'],
            ['code' => 'SOCIAL', 'name' => 'Social Media'],
            ['code' => 'SUBS', 'name' => 'Subscription'],
        ];

        foreach ($categories as $category) {
            DigitalAccountCategory::updateOrCreate(
                ['code' => $category['code']],
                ['name' => $category['name'], 'status' => 'ACTIVE']
            );
        }
    }
}
