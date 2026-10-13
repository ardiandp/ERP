<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseOrderService
{
    public function create(array $data, array $items): PurchaseOrder
    {
        $items = array_values(array_filter($items, fn ($item) => ! empty($item['product_id'])));

        if (empty($items)) {
            throw new InvalidArgumentException('Purchase order harus memiliki minimal satu item.');
        }

        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0.0;
            $prepared = [];

            foreach ($items as $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);
                $line = max(0, ($quantity * $unitPrice) - $discount);
                $subtotal += $line;

                $prepared[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'subtotal' => $line,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);

            $order = PurchaseOrder::create([
                'po_number' => DocumentNumber::generate('PO', PurchaseOrder::class, 'po_number', $data['order_date'] ?? null),
                'supplier_id' => $data['supplier_id'],
                'branch_id' => $data['branch_id'],
                'order_date' => $data['order_date'] ?? now()->toDateString(),
                'expected_date' => $data['expected_date'] ?? null,
                'status' => 'DRAFT',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => max(0, $subtotal - $discount + $tax),
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $order->items()->createMany($prepared);

            return $order->load('items');
        });
    }

    public function approve(PurchaseOrder $order): PurchaseOrder
    {
        if ($order->status !== 'DRAFT') {
            throw new InvalidArgumentException('Hanya purchase order berstatus DRAFT yang dapat disetujui.');
        }

        $order->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $order;
    }
}
