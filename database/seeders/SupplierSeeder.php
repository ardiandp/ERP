<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['supplier_code' => 'SUP-001', 'name' => 'PT Kosmetik Nusantara', 'phone' => '0211234567', 'email' => 'sales@kosmetiknusantara.test', 'payment_terms_days' => 30],
            ['supplier_code' => 'SUP-002', 'name' => 'CV Aroma Spa Supply', 'phone' => '0217654321', 'email' => 'order@aromaspa.test', 'payment_terms_days' => 14],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['supplier_code' => $supplier['supplier_code']],
                array_merge($supplier, ['status' => 'ACTIVE'])
            );
        }
    }
}
