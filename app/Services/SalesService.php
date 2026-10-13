<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalesService
{
    public function __construct(
        protected InventoryService $inventory,
        protected CommissionService $commissions,
        protected PaymentService $payments,
    ) {}

    public function create(array $data, array $items): SalesTransaction
    {
        $items = array_values(array_filter($items, fn ($item) => ! empty($item['item_type'])));

        if (empty($items)) {
            throw new InvalidArgumentException('Transaksi harus memiliki minimal satu item.');
        }

        return DB::transaction(function () use ($data, $items) {
            $warehouseId = $data['warehouse_id'] ?? $this->inventory->defaultWarehouseId($data['branch_id'] ?? null);

            $subtotal = 0.0;
            $prepared = [];

            foreach ($items as $item) {
                $quantity = (float) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);
                $lineSubtotal = max(0, ($quantity * $unitPrice) - $discount);

                $subtotal += $lineSubtotal;

                $prepared[] = [
                    'item_type' => $item['item_type'],
                    'product_id' => $item['product_id'] ?? null,
                    'service_id' => $item['service_id'] ?? null,
                    'package_id' => $item['package_id'] ?? null,
                    'item_name' => $item['item_name'] ?? $this->resolveName($item),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'subtotal' => $lineSubtotal,
                    'employee_id' => $item['employee_id'] ?? null,
                    'source_booking_item_id' => $item['source_booking_item_id'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $transactionDiscount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $grandTotal = max(0, $subtotal - $transactionDiscount + $tax);

            $transaction = SalesTransaction::create([
                'invoice_number' => DocumentNumber::generate('INV', SalesTransaction::class, 'invoice_number', $data['transaction_date'] ?? null),
                'branch_id' => $data['branch_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'cashier_id' => $data['cashier_id'] ?? auth()->id(),
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'subtotal' => $subtotal,
                'discount' => $transactionDiscount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'status' => 'COMPLETED',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($prepared as $row) {
                $item = $transaction->items()->create($row);

                if ($row['item_type'] === 'PRODUCT' && $row['product_id']) {
                    if (! $warehouseId) {
                        throw new InvalidArgumentException('Gudang tidak tersedia untuk pengurangan stok produk.');
                    }

                    $this->inventory->decrease($warehouseId, (int) $row['product_id'], (float) $row['quantity'], [
                        'movement_type' => 'SALE',
                        'reference_type' => SalesTransaction::class,
                        'reference_id' => $transaction->id,
                        'notes' => "Penjualan {$transaction->invoice_number}",
                    ]);
                }

                if ($row['item_type'] === 'SERVICE') {
                    $this->commissions->computeForItem($item);
                }
            }

            if (! empty($data['payment']['amount'])) {
                $this->payments->record($transaction, $data['payment']);
            }

            return $transaction->load('items', 'payments');
        });
    }

    protected function resolveName(array $item): string
    {
        return match ($item['item_type']) {
            'PRODUCT' => Product::find($item['product_id'])?->name ?? 'Produk',
            'SERVICE' => Service::find($item['service_id'])?->name ?? 'Layanan',
            'PACKAGE' => ServicePackage::find($item['package_id'])?->name ?? 'Paket',
            default => 'Item',
        };
    }
}
