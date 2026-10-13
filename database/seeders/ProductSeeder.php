<?php

namespace Database\Seeders;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = ProductCategory::where('code', 'RETAIL')->first() ?? ProductCategory::first();
        $unit = Unit::where('code', 'PCS')->first() ?? Unit::first();

        if (! $category || ! $unit) {
            return;
        }

        $products = [
            ['sku' => 'PRD-001', 'name' => 'Facial Wash', 'purchase_price' => 25000, 'default_sell_price' => 45000],
            ['sku' => 'PRD-002', 'name' => 'Sunscreen SPF 50', 'purchase_price' => 55000, 'default_sell_price' => 90000],
            ['sku' => 'PRD-003', 'name' => 'Vitamin C Serum', 'purchase_price' => 80000, 'default_sell_price' => 140000],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'name' => $product['name'],
                    'purchase_price' => $product['purchase_price'],
                    'default_sell_price' => $product['default_sell_price'],
                    'minimum_stock' => 5,
                    'status' => 'ACTIVE',
                ]
            );
        }

        $warehouse = Warehouse::orderBy('id')->first();

        if (! $warehouse) {
            return;
        }

        foreach (Product::where('status', 'ACTIVE')->get() as $product) {
            InventoryStock::updateOrCreate(
                ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                ['quantity' => 50, 'reserved_quantity' => 0]
            );
        }
    }
}
