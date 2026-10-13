<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function stock(int $warehouseId, int $productId): InventoryStock
    {
        return InventoryStock::firstOrCreate(
            ['warehouse_id' => $warehouseId, 'product_id' => $productId],
            ['quantity' => 0, 'reserved_quantity' => 0],
        );
    }

    public function increase(int $warehouseId, int $productId, float $quantity, array $options = []): InventoryStock
    {
        if ($quantity <= 0) {
            throw new RuntimeException('Jumlah harus lebih dari nol.');
        }

        return $this->applyMovement($warehouseId, $productId, 'IN', $quantity, $options);
    }

    public function decrease(int $warehouseId, int $productId, float $quantity, array $options = []): InventoryStock
    {
        if ($quantity <= 0) {
            throw new RuntimeException('Jumlah harus lebih dari nol.');
        }

        $stock = $this->stock($warehouseId, $productId);

        if ((float) $stock->quantity < $quantity && ! ($options['allow_negative'] ?? false)) {
            throw new RuntimeException("Stok tidak mencukupi untuk produk #{$productId}.");
        }

        return $this->applyMovement($warehouseId, $productId, 'OUT', $quantity, $options);
    }

    public function adjust(int $warehouseId, int $productId, float $quantity, array $options = []): InventoryStock
    {
        $stock = $this->stock($warehouseId, $productId);
        $delta = $quantity - (float) $stock->quantity;

        if ($delta === 0.0) {
            return $stock;
        }

        return $this->applyMovement(
            $warehouseId,
            $productId,
            $delta > 0 ? 'IN' : 'OUT',
            abs($delta),
            array_merge($options, ['movement_type' => 'ADJUSTMENT', 'allow_negative' => true]),
        );
    }

    public function defaultWarehouseId(?int $branchId = null): ?int
    {
        return Warehouse::query()
            ->where('status', 'ACTIVE')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('id')
            ->value('id');
    }

    protected function applyMovement(int $warehouseId, int $productId, string $direction, float $quantity, array $options): InventoryStock
    {
        return DB::transaction(function () use ($warehouseId, $productId, $direction, $quantity, $options) {
            $stock = InventoryStock::query()
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = InventoryStock::create([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            $stock->quantity = $direction === 'IN'
                ? (float) $stock->quantity + $quantity
                : (float) $stock->quantity - $quantity;
            $stock->save();

            InventoryMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'movement_type' => $options['movement_type'] ?? ($direction === 'IN' ? 'IN' : 'OUT'),
                'quantity' => $direction === 'IN' ? $quantity : -$quantity,
                'unit_cost' => $options['unit_cost'] ?? null,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'movement_date' => $options['movement_date'] ?? now()->toDateString(),
                'notes' => $options['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            return $stock;
        });
    }
}
