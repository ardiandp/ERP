<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CompanySeeder::class,
            BranchSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            BankSeeder::class,
            UnitSeeder::class,
            ProductCategorySeeder::class,
            ServiceCategorySeeder::class,
            PaymentMethodSeeder::class,
            AdminUserSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
