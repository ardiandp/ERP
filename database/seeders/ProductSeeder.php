<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = ProductCategory::where('code', 'RETAIL')->first() ?? ProductCategory::first();
        $unit = Unit::where('code', 'PCS')->first() ?? Unit::first();
        $brandBotanic = Brand::where('code', 'DM-BOTANIC')->first();
        $brandSkin = Brand::where('code', 'DM-SKIN')->first();

        if (! $category || ! $unit) {
            return;
        }

        $products = [
            ['sku' => 'PRD-001', 'name' => 'Facial Wash Herbal 100ml', 'purchase_price' => 25000, 'default_sell_price' => 45000, 'brand_id' => $brandSkin?->id],
            ['sku' => 'PRD-002', 'name' => 'Sunscreen Glow SPF 50', 'purchase_price' => 55000, 'default_sell_price' => 90000, 'brand_id' => $brandSkin?->id],
            ['sku' => 'PRD-003', 'name' => 'Vitamin C Serum 20ml', 'purchase_price' => 80000, 'default_sell_price' => 140000, 'brand_id' => $brandBotanic?->id],
            ['sku' => 'PRD-004', 'name' => 'Minyak Massage Herbal 250ml', 'purchase_price' => 40000, 'default_sell_price' => 75000, 'brand_id' => $brandBotanic?->id],
            ['sku' => 'PRD-005', 'name' => 'Lulur Bengkoang Tradisional', 'purchase_price' => 30000, 'default_sell_price' => 55000, 'brand_id' => $brandBotanic?->id],
        ];

        foreach ($products as $pData) {
            $product = Product::updateOrCreate(
                ['sku' => $pData['sku']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $pData['brand_id'],
                    'unit_id' => $unit->id,
                    'name' => $pData['name'],
                    'purchase_price' => $pData['purchase_price'],
                    'default_sell_price' => $pData['default_sell_price'],
                    'minimum_stock' => 5,
                    'status' => 'ACTIVE',
                ]
            );

            ProductPrice::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'price_type' => 'DEFAULT',
                    'effective_date' => now()->startOfYear()->toDateString(),
                ],
                [
                    'price' => $pData['default_sell_price'],
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
