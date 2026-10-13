<?php

namespace App\Services;

use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;

class SalesReturnService
{
    public function __construct(protected InventoryService $inventory) {}

    public function create(SalesTransaction $transaction, array $items, array $data): SalesReturn
    {
        return DB::transaction(function () use ($transaction, $items, $data) {
            $warehouseId = $data['warehouse_id'] ?? $this->inventory->defaultWarehouseId($transaction->branch_id);

            $return = SalesReturn::create([
                'return_number' => DocumentNumber::generate('RTN', SalesReturn::class, 'return_number', $data['return_date'] ?? null),
                'sales_transaction_id' => $transaction->id,
                'branch_id' => $transaction->branch_id,
                'return_date' => $data['return_date'] ?? now()->toDateString(),
                'reason' => $data['reason'] ?? null,
                'status' => 'COMPLETED',
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $item) {
                $saleItem = $transaction->items()->find($item['sales_transaction_item_id']);

                if (! $saleItem) {
                    continue;
                }

                $quantity = (float) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    continue;
                }

                $amount = $quantity * (float) $saleItem->unit_price;

                $return->items()->create([
                    'sales_transaction_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'quantity' => $quantity,
                    'amount' => $amount,
                ]);

                if ($saleItem->product_id && $warehouseId) {
                    $this->inventory->increase($warehouseId, (int) $saleItem->product_id, $quantity, [
                        'movement_type' => 'RETURN',
                        'reference_type' => SalesReturn::class,
                        'reference_id' => $return->id,
                        'notes' => "Retur {$return->return_number}",
                    ]);
                }
            }

            return $return->load('items');
        });
    }
}
