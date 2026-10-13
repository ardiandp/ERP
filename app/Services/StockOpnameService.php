<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\StockOpname;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockOpnameService
{
    public function __construct(protected InventoryService $inventory) {}

    public function create(array $data, array $items): StockOpname
    {
        $items = array_values(array_filter($items, fn ($item) => ! empty($item['product_id'])));

        if (empty($items)) {
            throw new InvalidArgumentException('Stock opname harus memiliki minimal satu item.');
        }

        return DB::transaction(function () use ($data, $items) {
            $opname = StockOpname::create([
                'opname_number' => DocumentNumber::generate('OPN', StockOpname::class, 'opname_number', $data['opname_date'] ?? null),
                'warehouse_id' => $data['warehouse_id'],
                'opname_date' => $data['opname_date'] ?? now()->toDateString(),
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $systemQuantities = InventoryStock::query()
                ->where('warehouse_id', $data['warehouse_id'])
                ->whereIn('product_id', array_column($items, 'product_id'))
                ->pluck('quantity', 'product_id');

            $prepared = [];

            foreach ($items as $item) {
                $system = (float) ($systemQuantities[$item['product_id']] ?? 0);
                $physical = (float) ($item['physical_quantity'] ?? $system);

                $prepared[] = [
                    'product_id' => $item['product_id'],
                    'system_quantity' => $system,
                    'physical_quantity' => $physical,
                    'variance_quantity' => $physical - $system,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $opname->items()->createMany($prepared);

            return $opname->load('items');
        });
    }

    public function approve(StockOpname $opname): StockOpname
    {
        if ($opname->status !== 'DRAFT') {
            throw new InvalidArgumentException('Hanya stock opname berstatus DRAFT yang dapat disetujui.');
        }

        return DB::transaction(function () use ($opname) {
            foreach ($opname->items as $item) {
                $this->inventory->adjust((int) $opname->warehouse_id, (int) $item->product_id, (float) $item->physical_quantity, [
                    'movement_type' => 'STOCK_OPNAME',
                    'reference_type' => StockOpname::class,
                    'reference_id' => $opname->id,
                    'notes' => "Stock opname {$opname->opname_number}",
                ]);
            }

            $opname->update([
                'status' => 'COMPLETED',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return $opname;
        });
    }
}
