<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::orderBy('id')->get();

        foreach ($branches as $branch) {
            Warehouse::updateOrCreate(
                ['code' => 'WH-'.$branch->code],
                [
                    'branch_id' => $branch->id,
                    'name' => 'Gudang '.$branch->name,
                    'warehouse_type' => 'MAIN',
                    'status' => 'ACTIVE',
                ]
            );
        }
    }
}
