<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\FinancialAccount;
use Illuminate\Database\Seeder;

class FinancialAccountSeeder extends Seeder
{
    public function run(): void
    {
        $bca = Bank::where('code', 'BCA')->first();

        $accounts = [
            ['account_code' => 'CASH-01', 'account_name' => 'Kas Utama', 'account_type' => 'CASH', 'bank_id' => null, 'opening_balance' => 0],
            ['account_code' => 'BANK-BCA', 'account_name' => 'Bank BCA', 'account_type' => 'BANK', 'bank_id' => $bca?->id, 'opening_balance' => 0],
        ];

        foreach ($accounts as $account) {
            FinancialAccount::updateOrCreate(
                ['account_code' => $account['account_code']],
                [
                    'account_type' => $account['account_type'],
                    'bank_id' => $account['bank_id'],
                    'account_name' => $account['account_name'],
                    'currency' => 'IDR',
                    'opening_balance' => $account['opening_balance'],
                    'status' => 'ACTIVE',
                ]
            );
        }
    }
}
