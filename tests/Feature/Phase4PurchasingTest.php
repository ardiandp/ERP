<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase4PurchasingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_phase4_pages_render(): void
    {
        $this->actingAs($this->admin)->get(route('purchase-orders.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('purchase-orders.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('goods-receipts.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('goods-receipts.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('stock-transfers.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('stock-transfers.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('stock-opnames.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('stock-opnames.create'))->assertOk();
    }

    public function test_purchase_order_is_created_and_approved(): void
    {
        $supplier = Supplier::firstOrFail();
        $branch = Branch::firstOrFail();
        $product = Product::firstOrFail();

        $this->actingAs($this->admin)->post(route('purchase-orders.store'), [
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'order_date' => now()->toDateString(),
            'discount' => 0,
            'tax' => 0,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 25000, 'discount' => 0],
            ],
        ])->assertRedirect();

        $order = PurchaseOrder::firstOrFail();
        $this->assertSame('DRAFT', $order->status);
        $this->assertSame(250000.0, (float) $order->grand_total);
        $this->assertSame(1, $order->items()->count());

        $this->actingAs($this->admin)->patch(route('purchase-orders.approve', $order))->assertRedirect();

        $this->assertSame('APPROVED', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->approved_at);
    }

    public function test_goods_receipt_increases_stock_and_completes_purchase_order(): void
    {
        $supplier = Supplier::firstOrFail();
        $branch = Branch::firstOrFail();
        $warehouse = Warehouse::firstOrFail();
        $product = Product::firstOrFail();

        $this->actingAs($this->admin)->post(route('purchase-orders.store'), [
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'order_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 25000, 'discount' => 0],
            ],
        ])->assertRedirect();

        $order = PurchaseOrder::firstOrFail();
        $before = (float) InventoryStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity');

        $this->actingAs($this->admin)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $order->id,
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 25000],
            ],
        ])->assertRedirect();

        $after = (float) InventoryStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity');
        $this->assertEquals($before + 5, $after);

        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'movement_type' => 'PURCHASE',
        ]);
        $this->assertSame('RECEIVED', $order->fresh()->status);
    }

    public function test_stock_transfer_moves_stock_between_warehouses(): void
    {
        $product = Product::firstOrFail();
        [$from, $to] = $this->twoWarehouses();

        $fromBefore = (float) InventoryStock::where('warehouse_id', $from->id)->where('product_id', $product->id)->value('quantity');
        $toBefore = (float) InventoryStock::where('warehouse_id', $to->id)->where('product_id', $product->id)->value('quantity');

        $this->actingAs($this->admin)->post(route('stock-transfers.store'), [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'transfer_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ])->assertRedirect();

        $transfer = StockTransfer::firstOrFail();
        $this->assertSame('DRAFT', $transfer->status);

        $this->actingAs($this->admin)->patch(route('stock-transfers.approve', $transfer))->assertRedirect();

        $fromAfter = (float) InventoryStock::where('warehouse_id', $from->id)->where('product_id', $product->id)->value('quantity');
        $toAfter = (float) InventoryStock::where('warehouse_id', $to->id)->where('product_id', $product->id)->value('quantity');

        $this->assertEquals($fromBefore - 3, $fromAfter);
        $this->assertEquals($toBefore + 3, $toAfter);
        $this->assertSame('COMPLETED', $transfer->fresh()->status);
        $this->assertDatabaseHas('inventory_movements', ['movement_type' => 'TRANSFER_OUT']);
        $this->assertDatabaseHas('inventory_movements', ['movement_type' => 'TRANSFER_IN']);
    }

    public function test_stock_opname_adjusts_stock_to_physical_quantity(): void
    {
        $warehouse = Warehouse::firstOrFail();
        $product = Product::firstOrFail();

        $system = (float) InventoryStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity');

        $this->actingAs($this->admin)->post(route('stock-opnames.store'), [
            'warehouse_id' => $warehouse->id,
            'opname_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'physical_quantity' => 40],
            ],
        ])->assertRedirect();

        $opname = StockOpname::firstOrFail();
        $item = $opname->items()->firstOrFail();

        $this->assertEquals($system, (float) $item->system_quantity);
        $this->assertEquals(40.0, (float) $item->physical_quantity);
        $this->assertEquals(40 - $system, (float) $item->variance_quantity);

        $this->actingAs($this->admin)->patch(route('stock-opnames.approve', $opname))->assertRedirect();

        $this->assertEquals(40.0, (float) InventoryStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity'));
        $this->assertSame('COMPLETED', $opname->fresh()->status);
        $this->assertDatabaseHas('inventory_movements', ['movement_type' => 'STOCK_OPNAME']);
    }

    public function test_transfer_with_insufficient_stock_is_rejected(): void
    {
        $product = Product::firstOrFail();
        [$from, $to] = $this->twoWarehouses();

        InventoryStock::where('warehouse_id', $from->id)->where('product_id', $product->id)->update(['quantity' => 1]);

        $this->actingAs($this->admin)->post(route('stock-transfers.store'), [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'transfer_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ])->assertRedirect();

        $transfer = StockTransfer::firstOrFail();

        $this->actingAs($this->admin)->patch(route('stock-transfers.approve', $transfer))->assertSessionHasErrors('status');

        $this->assertSame('DRAFT', $transfer->fresh()->status);
    }

    protected function twoWarehouses(): array
    {
        $warehouses = Warehouse::orderBy('id')->take(2)->get();

        if ($warehouses->count() < 2) {
            $warehouses->push(Warehouse::create([
                'branch_id' => Branch::firstOrFail()->id,
                'code' => 'WH-TEST-2',
                'name' => 'Gudang Test 2',
                'warehouse_type' => 'STORE',
                'status' => 'ACTIVE',
            ]));
        }

        return [$warehouses[0], $warehouses[1]];
    }
}
