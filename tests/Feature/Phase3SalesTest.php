<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\InventoryStock;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\Service;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3SalesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_phase3_pages_render(): void
    {
        $sales = SalesTransaction::create([
            'invoice_number' => 'INV-TEST-0001',
            'branch_id' => Branch::value('id'),
            'transaction_date' => now()->toDateString(),
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 0,
            'status' => 'COMPLETED',
        ]);

        $this->actingAs($this->admin)->get(route('sales.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('sales.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('sales.show', $sales))->assertOk();
        $this->actingAs($this->admin)->get(route('bookings.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('bookings.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('warehouses.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('inventory.stock'))->assertOk();
        $this->actingAs($this->admin)->get(route('inventory.movements'))->assertOk();
        $this->actingAs($this->admin)->get(route('commissions.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('sales.returns.index'))->assertOk();
    }

    public function test_product_sale_decreases_stock_and_records_payment(): void
    {
        $branch = Branch::firstOrFail();
        $warehouse = Warehouse::firstOrFail();
        $product = Product::firstOrFail();
        $method = PaymentMethod::firstOrFail();

        $before = InventoryStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity');

        $response = $this->actingAs($this->admin)->post(route('sales.store'), [
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'transaction_date' => now()->toDateString(),
            'discount' => 0,
            'tax' => 0,
            'payment_method_id' => $method->id,
            'paid_amount' => 90000,
            'items' => [
                ['ref' => "PRODUCT:{$product->id}", 'quantity' => 2, 'unit_price' => 45000, 'discount' => 0],
            ],
        ]);

        $transaction = SalesTransaction::latest('id')->firstOrFail();
        $response->assertRedirect(route('sales.show', $transaction));

        $this->assertSame(90000.0, (float) $transaction->grand_total);
        $this->assertDatabaseHas('sales_transaction_items', [
            'sales_transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $after = InventoryStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity');
        $this->assertEquals((float) $before - 2, (float) $after);
        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'movement_type' => 'SALE',
            'reference_id' => $transaction->id,
        ]);
        $this->assertDatabaseHas('payments', [
            'transaction_type' => 'SALE',
            'transaction_id' => $transaction->id,
            'amount' => 90000,
        ]);
        $this->assertSame(1, Payment::count());
    }

    public function test_service_sale_creates_commission_for_therapist(): void
    {
        $branch = Branch::firstOrFail();
        $service = Service::firstOrFail();
        $employee = Employee::create([
            'employee_code' => 'EMP-TEST',
            'full_name' => 'Therapist Test',
            'branch_id' => $branch->id,
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($this->admin)->post(route('sales.store'), [
            'branch_id' => $branch->id,
            'transaction_date' => now()->toDateString(),
            'items' => [
                ['ref' => "SERVICE:{$service->id}", 'quantity' => 1, 'unit_price' => 100000, 'discount' => 0, 'employee_id' => $employee->id],
            ],
        ])->assertRedirect();

        $transaction = SalesTransaction::latest('id')->firstOrFail();

        $this->assertDatabaseHas('employee_commissions', [
            'employee_id' => $employee->id,
            'sales_transaction_id' => $transaction->id,
            'commission_amount' => 10000,
        ]);
    }

    public function test_insufficient_stock_is_rejected(): void
    {
        $branch = Branch::firstOrFail();
        $warehouse = Warehouse::firstOrFail();
        $product = Product::firstOrFail();

        InventoryStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->update(['quantity' => 1]);

        $this->actingAs($this->admin)->post(route('sales.store'), [
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'transaction_date' => now()->toDateString(),
            'items' => [
                ['ref' => "PRODUCT:{$product->id}", 'quantity' => 5, 'unit_price' => 45000, 'discount' => 0],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, SalesTransaction::count());
    }

    public function test_booking_can_be_created_with_item(): void
    {
        $branch = Branch::firstOrFail();
        $customer = Customer::create(['customer_code' => 'CUST-TEST', 'name' => 'Pelanggan Test', 'customer_type' => 'REGULAR', 'status' => 'ACTIVE']);
        $service = Service::firstOrFail();

        $this->actingAs($this->admin)->post(route('bookings.store'), [
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'booking_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'BOOKED',
        ])->assertRedirect();

        $booking = Booking::firstOrFail();

        $this->actingAs($this->admin)->post(route('bookings.items.store', $booking), [
            'service_id' => $service->id,
        ])->assertRedirect();

        $booking->refresh();
        $this->assertSame(1, $booking->items()->count());
    }

    public function test_sales_return_restocks_product(): void
    {
        $branch = Branch::firstOrFail();
        $warehouse = Warehouse::firstOrFail();
        $product = Product::firstOrFail();

        $this->actingAs($this->admin)->post(route('sales.store'), [
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'items' => [
                ['ref' => "PRODUCT:{$product->id}", 'quantity' => 3, 'unit_price' => 45000, 'discount' => 0],
            ],
        ])->assertRedirect();

        $transaction = SalesTransaction::latest('id')->firstOrFail();
        $item = $transaction->items()->firstOrFail();
        $afterSale = InventoryStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity');

        $this->actingAs($this->admin)->post(route('sales.returns.store', $transaction), [
            'warehouse_id' => $warehouse->id,
            'return_date' => now()->toDateString(),
            'items' => [
                ['sales_transaction_item_id' => $item->id, 'quantity' => 2],
            ],
        ])->assertRedirect(route('sales.returns.index'));

        $this->assertDatabaseHas('sales_return_items', ['sales_transaction_item_id' => $item->id, 'quantity' => 2]);

        $afterReturn = InventoryStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity');
        $this->assertEquals((float) $afterSale + 2, (float) $afterReturn);
    }
}
