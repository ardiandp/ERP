<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\ServicePackageItem;
use Illuminate\Database\Seeder;

class ServicePackageSeeder extends Seeder
{
    public function run(): void
    {
        $svcMassage = Service::where('service_code', 'SVC-001')->first();
        $svcMassage90 = Service::where('service_code', 'SVC-002')->first();
        $svcFacial = Service::where('service_code', 'SVC-003')->first();
        $svcTotok = Service::where('service_code', 'SVC-004')->first();
        $svcLulur = Service::where('service_code', 'SVC-005')->first();
        $svcRatus = Service::where('service_code', 'SVC-006')->first();
        $svcCreambath = Service::where('service_code', 'SVC-007')->first();

        $packages = [
            [
                'code' => 'PKG-WEDDING',
                'name' => 'Paket Pre-Wedding Muslimah',
                'description' => 'Paket lengkap persiapan calon pengantin: Facial Glow, Lulur Scrub, Massage 90m, dan Ratus V-Spa.',
                'price' => 650000,
                'status' => 'ACTIVE',
                'items' => [
                    ['service' => $svcFacial, 'quantity' => 1],
                    ['service' => $svcLulur, 'quantity' => 1],
                    ['service' => $svcMassage90, 'quantity' => 1],
                    ['service' => $svcRatus, 'quantity' => 1],
                ],
            ],
            [
                'code' => 'PKG-RELAX',
                'name' => 'Paket Relaksasi Total 120 Menit',
                'description' => 'Kombinasi Body Massage dan Totok Wajah untuk meredakan ketegangan otot dan memperlancar sirkulasi darah.',
                'price' => 320000,
                'status' => 'ACTIVE',
                'items' => [
                    ['service' => $svcMassage, 'quantity' => 1],
                    ['service' => $svcTotok, 'quantity' => 1],
                ],
            ],
            [
                'code' => 'PKG-GLOW',
                'name' => 'Paket Facial Glowing & Totok Wajah',
                'description' => 'Perawatan pencerah wajah menyeluruh dengan teknik totok acupressure alami.',
                'price' => 260000,
                'status' => 'ACTIVE',
                'items' => [
                    ['service' => $svcFacial, 'quantity' => 1],
                    ['service' => $svcTotok, 'quantity' => 1],
                ],
            ],
            [
                'code' => 'PKG-POSTNATAL',
                'name' => 'Paket Pemulihan Pasca Melahirkan',
                'description' => 'Perawatan relaksasi dan kebugaran tubuh khusus ibu pasca melahirkan.',
                'price' => 520000,
                'status' => 'ACTIVE',
                'items' => [
                    ['service' => $svcMassage90, 'quantity' => 1],
                    ['service' => $svcLulur, 'quantity' => 1],
                    ['service' => $svcRatus, 'quantity' => 1],
                ],
            ],
        ];

        foreach ($packages as $pkgData) {
            $package = ServicePackage::updateOrCreate(
                ['code' => $pkgData['code']],
                [
                    'name' => $pkgData['name'],
                    'description' => $pkgData['description'],
                    'price' => $pkgData['price'],
                    'valid_from' => now()->startOfYear()->toDateString(),
                    'valid_until' => now()->addYear()->endOfYear()->toDateString(),
                    'status' => $pkgData['status'],
                ]
            );

            // Re-create items cleanly
            ServicePackageItem::where('package_id', $package->id)->delete();

            foreach ($pkgData['items'] as $item) {
                if ($item['service']) {
                    ServicePackageItem::create([
                        'package_id' => $package->id,
                        'service_id' => $item['service']->id,
                        'quantity' => $item['quantity'],
                    ]);
                }
            }
        }
    }
}

