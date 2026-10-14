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
            ExpenseCategorySeeder::class,
            PaymentMethodSeeder::class,
            AssetCategorySeeder::class,
            DigitalAccountCategorySeeder::class,
            SalaryComponentSeeder::class,
            ShiftSeeder::class,
            SupplierSeeder::class,
            WarehouseSeeder::class,
            ProductSeeder::class,
            ServiceSeeder::class,
            CommissionRuleSeeder::class,
            FinancialAccountSeeder::class,
            BillTemplateSeeder::class,
            PayslipTemplateSeeder::class,
            AdminUserSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
