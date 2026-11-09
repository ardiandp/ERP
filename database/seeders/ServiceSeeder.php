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
        $massageCat = ServiceCategory::where('code', 'MASSAGE')->first() ?? ServiceCategory::first();
        $facialCat = ServiceCategory::where('code', 'FACIAL')->first() ?? $massageCat;
        $spaCat = ServiceCategory::where('code', 'SPA')->first() ?? $massageCat;

        if (! $massageCat) {
            return;
        }

        $services = [
            ['service_code' => 'SVC-001', 'category_id' => $massageCat->id, 'name' => 'Body Massage 60 Menit', 'pricing_method' => 'DURATION', 'duration' => 60, 'price' => 150000],
            ['service_code' => 'SVC-002', 'category_id' => $massageCat->id, 'name' => 'Body Massage 90 Menit', 'pricing_method' => 'DURATION', 'duration' => 90, 'price' => 220000],
            ['service_code' => 'SVC-003', 'category_id' => $facialCat->id, 'name' => 'Facial Glow Muslimah', 'pricing_method' => 'FIXED', 'duration' => 60, 'price' => 175000],
            ['service_code' => 'SVC-004', 'category_id' => $facialCat->id, 'name' => 'Totok Wajah & Relaksasi', 'pricing_method' => 'FIXED', 'duration' => 45, 'price' => 120000],
            ['service_code' => 'SVC-005', 'category_id' => $spaCat->id, 'name' => 'Lulur Tradisional & Body Scrub', 'pricing_method' => 'FIXED', 'duration' => 60, 'price' => 180000],
            ['service_code' => 'SVC-006', 'category_id' => $spaCat->id, 'name' => 'Ratus V-Spa Herbal', 'pricing_method' => 'FIXED', 'duration' => 30, 'price' => 95000],
            ['service_code' => 'SVC-007', 'category_id' => $spaCat->id, 'name' => 'Creambath Herbal Muslimah', 'pricing_method' => 'FIXED', 'duration' => 60, 'price' => 110000],
            ['service_code' => 'SVC-008', 'category_id' => $spaCat->id, 'name' => 'Manicure & Pedicure Halal', 'pricing_method' => 'FIXED', 'duration' => 60, 'price' => 135000],
        ];

        foreach ($services as $data) {
            $service = Service::updateOrCreate(
                ['service_code' => $data['service_code']],
                [
                    'category_id' => $data['category_id'],
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
