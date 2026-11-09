<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Supplier;
use App\Models\SupplierBankAccount;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $bca = Bank::where('code', 'BCA')->first();
        $bni = Bank::where('code', 'BNI')->first();

        $suppliers = [
            [
                'supplier_code' => 'SUP-001',
                'name' => 'PT Kosmetik Nusantara',
                'phone' => '0211234567',
                'email' => 'sales@kosmetiknusantara.test',
                'contact_person' => 'Bambang Sudiro',
                'address' => 'Kawasan Industri Pulogadung Blok B No. 12, Jakarta Timur',
                'tax_number' => '01.234.567.8-012.000',
                'payment_terms_days' => 30,
                'bank_id' => $bca?->id,
                'account_number' => '8830129840',
                'account_name' => 'PT Kosmetik Nusantara',
                'branch_name' => 'KCP Pulogadung',
            ],
            [
                'supplier_code' => 'SUP-002',
                'name' => 'CV Aroma Spa Supply',
                'phone' => '0217654321',
                'email' => 'order@aromaspa.test',
                'contact_person' => 'Siti Aminah',
                'address' => 'Jl. Kemang Raya No. 45, Jakarta Selatan',
                'tax_number' => '02.345.678.9-034.000',
                'payment_terms_days' => 14,
                'bank_id' => $bni?->id,
                'account_number' => '0192837465',
                'account_name' => 'CV Aroma Spa Supply',
                'branch_name' => 'KCP Fatmawati',
            ],
        ];

        foreach ($suppliers as $data) {
            $bankId = $data['bank_id'] ?? null;
            $accountNumber = $data['account_number'] ?? null;
            $accountName = $data['account_name'] ?? null;
            $branchName = $data['branch_name'] ?? null;

            unset($data['bank_id'], $data['account_number'], $data['account_name'], $data['branch_name']);

            $supplier = Supplier::updateOrCreate(
                ['supplier_code' => $data['supplier_code']],
                array_merge($data, ['status' => 'ACTIVE'])
            );

            if ($bankId && $accountNumber) {
                SupplierBankAccount::updateOrCreate(
                    ['supplier_id' => $supplier->id, 'account_number' => $accountNumber],
                    [
                        'bank_id' => $bankId,
                        'account_name' => $accountName,
                        'branch_name' => $branchName,
                        'is_primary' => true,
                        'status' => 'ACTIVE',
                    ]
                );
            }
        }
    }
}
