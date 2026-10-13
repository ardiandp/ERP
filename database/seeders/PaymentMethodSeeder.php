<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['code' => 'CASH', 'name' => 'Tunai', 'method_type' => 'CASH'],
            ['code' => 'TRANSFER', 'name' => 'Transfer Bank', 'method_type' => 'BANK'],
            ['code' => 'QRIS', 'name' => 'QRIS', 'method_type' => 'E_WALLET'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                ['name' => $method['name'], 'method_type' => $method['method_type'], 'status' => 'ACTIVE']
            );
        }
    }
}
