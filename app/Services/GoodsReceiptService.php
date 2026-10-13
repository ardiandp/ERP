<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GoodsReceiptService
{
    public function __construct(protected InventoryService $inventory) {}

    public function create(array $data, array $items): GoodsReceipt
    {
        $items = array_values(array_filter($items, fn ($item) => ! empty($item['product_id']) && (float) ($item['quantity'] ?? 0) > 0));

        if (empty($items)) {
            throw new InvalidArgumentException('Penerimaan barang harus memiliki minimal satu item.');
        }

        return DB::transaction(function () use ($data, $items) {
            $warehouseId = $data['warehouse_id'] ?? $this->inventory->defaultWarehouseId($data['branch_id'] ?? null);

            if (! $warehouseId) {
                throw new InvalidArgumentException('Gudang penerimaan tidak tersedia.');
            }

            $receipt = GoodsReceipt::create([
                'receipt_number' => DocumentNumber::generate('GRN', GoodsReceipt::class, 'receipt_number', $data['receipt_date'] ?? null),
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'supplier_id' => $data['supplier_id'],
                'branch_id' => $data['branch_id'],
                'warehouse_id' => $warehouseId,
                'receipt_date' => $data['receipt_date'] ?? now()->toDateString(),
                'status' => 'POSTED',
                'notes' => $data['notes'] ?? null,
                'received_by' => auth()->id(),
            ]);

            foreach ($items as $item) {
                $quantity = (float) $item['quantity'];

                $receipt->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $item['unit_cost'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);

                $this->inventory->increase((int) $warehouseId, (int) $item['product_id'], $quantity, [
                    'movement_type' => 'PURCHASE',
                    'unit_cost' => $item['unit_cost'] ?? null,
                    'reference_type' => GoodsReceipt::class,
                    'reference_id' => $receipt->id,
                    'notes' => "Penerimaan {$receipt->receipt_number}",
                ]);
            }

            if ($receipt->purchase_order_id) {
                $this->syncPurchaseOrderStatus(PurchaseOrder::find($receipt->purchase_order_id));
            }

            return $receipt->load('items');
        });
    }

    protected function syncPurchaseOrderStatus(?PurchaseOrder $order): void
    {
        if (! $order) {
            return;
        }

        $received = GoodsReceipt::query()
            ->where('purchase_order_id', $order->id)
            ->where('status', 'POSTED')
            ->with('items')
            ->get()
            ->flatMap->items
            ->groupBy('product_id')
            ->map(fn ($group) => (float) $group->sum('quantity'));

        $fullyReceived = $order->items->every(
            fn ($item) => ($received[$item->product_id] ?? 0) >= (float) $item->quantity
        );

        $order->update(['status' => $fullyReceived ? 'RECEIVED' : 'PARTIALLY_RECEIVED']);
    }
}
