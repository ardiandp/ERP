<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'OPERASIONAL', 'name' => 'Operasional'],
            ['code' => 'GAJI', 'name' => 'Gaji & Tunjangan'],
            ['code' => 'UTILITAS', 'name' => 'Listrik, Air & Internet'],
            ['code' => 'SEWA', 'name' => 'Sewa'],
            ['code' => 'PERAWATAN', 'name' => 'Perawatan & Perbaikan'],
            ['code' => 'LAINNYA', 'name' => 'Lain-lain'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['code' => $category['code']],
                ['name' => $category['name'], 'status' => 'ACTIVE']
            );
        }
    }
}
