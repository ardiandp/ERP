<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePrice;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $category = ServiceCategory::where('code', 'MASSAGE')->first() ?? ServiceCategory::first();

        if (! $category) {
            return;
        }

        $services = [
            ['service_code' => 'SVC-001', 'name' => 'Body Massage 60 Menit', 'pricing_method' => 'DURATION', 'duration' => 60, 'price' => 150000],
            ['service_code' => 'SVC-002', 'name' => 'Body Massage 90 Menit', 'pricing_method' => 'DURATION', 'duration' => 90, 'price' => 220000],
            ['service_code' => 'SVC-003', 'name' => 'Facial Glow', 'pricing_method' => 'FIXED', 'duration' => null, 'price' => 175000],
        ];

        foreach ($services as $data) {
            $service = Service::updateOrCreate(
                ['service_code' => $data['service_code']],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'pricing_method' => $data['pricing_method'],
                    'default_duration_minutes' => $data['duration'],
                    'status' => 'ACTIVE',
                ]
            );

            ServicePrice::updateOrCreate(
                [
                    'service_id' => $service->id,
                    'duration_minutes' => $data['duration'],
                    'effective_date' => now()->startOfYear()->toDateString(),
                ],
                [
                    'price' => $data['price'],
                    'end_date' => null,
                ]
            );
        }
    }
}
