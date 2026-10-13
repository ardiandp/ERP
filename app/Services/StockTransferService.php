<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockTransferService
{
    public function __construct(protected InventoryService $inventory) {}

    public function create(array $data, array $items): StockTransfer
    {
        $items = array_values(array_filter($items, fn ($item) => ! empty($item['product_id']) && (float) ($item['quantity'] ?? 0) > 0));

        if (empty($items)) {
            throw new InvalidArgumentException('Transfer stok harus memiliki minimal satu item.');
        }

        if ((int) $data['from_warehouse_id'] === (int) $data['to_warehouse_id']) {
            throw new InvalidArgumentException('Gudang asal dan tujuan tidak boleh sama.');
        }

        return DB::transaction(function () use ($data, $items) {
            $transfer = StockTransfer::create([
                'transfer_number' => DocumentNumber::generate('TRF', StockTransfer::class, 'transfer_number', $data['transfer_date'] ?? null),
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'status' => 'DRAFT',
                'requested_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $transfer->items()->createMany(array_map(fn ($item) => [
                'product_id' => $item['product_id'],
                'quantity' => (float) $item['quantity'],
            ], $items));

            return $transfer->load('items');
        });
    }

    public function approve(StockTransfer $transfer): StockTransfer
    {
        if ($transfer->status !== 'DRAFT') {
            throw new InvalidArgumentException('Hanya transfer berstatus DRAFT yang dapat disetujui.');
        }

        return DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                $this->inventory->decrease((int) $transfer->from_warehouse_id, (int) $item->product_id, (float) $item->quantity, [
                    'movement_type' => 'TRANSFER_OUT',
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'notes' => "Transfer {$transfer->transfer_number} keluar",
                ]);

                $this->inventory->increase((int) $transfer->to_warehouse_id, (int) $item->product_id, (float) $item->quantity, [
                    'movement_type' => 'TRANSFER_IN',
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'notes' => "Transfer {$transfer->transfer_number} masuk",
                ]);
            }

            $transfer->update([
                'status' => 'COMPLETED',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return $transfer;
        });
    }
}
