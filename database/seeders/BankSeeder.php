<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['code' => 'BCA', 'name' => 'Bank Central Asia'],
            ['code' => 'BNI', 'name' => 'Bank Negara Indonesia'],
        ];

        foreach ($banks as $bank) {
            Bank::updateOrCreate(['code' => $bank['code']], ['name' => $bank['name'], 'status' => 'ACTIVE']);
        }
    }
}
