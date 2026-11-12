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
            ['account_code' => 'CASH-01', 'account_name' => 'Kas Utama', 'account_number' => null, 'account_type' => 'CASH', 'bank_id' => null, 'opening_balance' => 0],
            ['account_code' => 'BCA-CV-DM', 'account_name' => 'BCA CV Dian Mustika Care', 'account_number' => '8831216999', 'account_type' => 'BANK', 'bank_id' => $bca?->id, 'opening_balance' => 5642865.35],
            ['account_code' => 'BCA-AKHDAN', 'account_name' => 'BCA Akhdan Fathur Rahman', 'account_number' => '8840430945', 'account_type' => 'BANK', 'bank_id' => $bca?->id, 'opening_balance' => 25228778.23],
            ['account_code' => 'BCA-ALYA', 'account_name' => 'BCA Aliya Madaniya', 'account_number' => '7615476862', 'account_type' => 'BANK', 'bank_id' => $bca?->id, 'opening_balance' => 309990.80],
        ];

        foreach ($accounts as $account) {
            FinancialAccount::updateOrCreate(
                ['account_code' => $account['account_code']],
                [
                    'account_type' => $account['account_type'],
                    'bank_id' => $account['bank_id'],
                    'account_number' => $account['account_number'],
                    'account_name' => $account['account_name'],
                    'currency' => 'IDR',
                    'opening_balance' => $account['opening_balance'],
                    'status' => 'ACTIVE',
                ]
            );
        }
    }
}
