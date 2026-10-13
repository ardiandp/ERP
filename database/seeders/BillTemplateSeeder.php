<?php

namespace Database\Seeders;

use App\Models\BillTemplate;
use Illuminate\Database\Seeder;

class BillTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['code' => 'BPJS', 'name' => 'BPJS Kesehatan', 'category' => 'Asuransi', 'frequency' => 'MONTHLY', 'default_amount' => 500000, 'default_due_day' => 15],
            ['code' => 'INTERNET', 'name' => 'Internet', 'category' => 'Utilitas', 'frequency' => 'MONTHLY', 'default_amount' => 350000, 'default_due_day' => 10],
            ['code' => 'LISTRIK', 'name' => 'Listrik', 'category' => 'Utilitas', 'frequency' => 'MONTHLY', 'default_amount' => 750000, 'default_due_day' => 20],
            ['code' => 'SEWA', 'name' => 'Sewa Tempat', 'category' => 'Sewa', 'frequency' => 'YEARLY', 'default_amount' => 24000000, 'default_due_day' => 1],
        ];

        foreach ($templates as $template) {
            BillTemplate::updateOrCreate(
                ['code' => $template['code']],
                [
                    'name' => $template['name'],
                    'category' => $template['category'],
                    'frequency' => $template['frequency'],
                    'default_amount' => $template['default_amount'],
                    'default_due_day' => $template['default_due_day'],
                    'status' => 'ACTIVE',
                ]
            );
        }
    }
}
