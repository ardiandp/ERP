<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::setValue('app.name', 'ERP Dian Mustika', 'general');
        Setting::setValue('app.timezone', 'Asia/Jakarta', 'general');
        Setting::setValue('app.currency', 'IDR', 'general');
    }
}
