<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Service;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
        $this->branch = Branch::firstOrFail();
    }

    public function test_payslip_print_view_renders(): void
    {
        $employee = Employee::create([
            'employee_code' => 'EMP-PRINT-01',
            'full_name' => 'Siti Aminah',
            'branch_id' => $this->branch->id,
            'employment_status' => 'PERMANENT',
            'status' => 'ACTIVE',
        ]);

        $period = PayrollPeriod::create([
            'code' => 'PP-2026-10',
            'name' => 'Oktober 2026',
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'payment_date' => '2026-11-01',
            'status' => 'OPEN',
        ]);

        $payroll = Payroll::create([
            'payroll_number' => 'PAY-202610-001',
            'payroll_period_id' => $period->id,
            'employee_id' => $employee->id,
            'basic_salary' => 3500000,
            'total_allowances' => 500000,
            'total_deductions' => 100000,
            'net_salary' => 3900000,
            'status' => 'APPROVED',
        ]);

        $payslip = Payslip::create([
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'payslip_number' => 'SLP-202610-001',
            'issue_date' => now()->toDateString(),
            'gross_salary' => 4000000,
            'total_deduction' => 100000,
            'net_salary' => 3900000,
            'status' => 'PUBLISHED',
            'verification_code' => 'VERIF-XYZ-123',
        ]);

        $response = $this->actingAs($this->admin)->get(route('payslips.print', $payslip));

        $response->assertOk();
        $response->assertSee('Dian Mustika');
        $response->assertSee('SLP-202610-001');
        $response->assertSee('Siti Aminah');
        $response->assertSee('Rp 3.900.000');
        $response->assertSee('VERIF-XYZ-123');
    }

    public function test_sales_receipt_print_view_renders(): void
    {
        $customer = Customer::create([
            'customer_code' => 'CUST-001',
            'name' => 'Ibu Rahmawati',
            'phone' => '081299998888',
            'branch_id' => $this->branch->id,
        ]);

        $product = Product::firstOrFail();
        $salesService = app(SalesService::class);
        $method = PaymentMethod::firstOrFail();

        $sale = $salesService->create([
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'transaction_date' => now()->toDateString(),
            'payment_method_id' => $method->id,
            'paid_amount' => (float) $product->price * 2,
        ], [
            [
                'item_type' => 'PRODUCT',
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => (float) $product->price,
                'discount' => 0,
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('sales.print', $sale));

        $response->assertOk();
        $response->assertSee('DIAN MUSTIKA');
        $response->assertSee($sale->invoice_number);
        $response->assertSee('Ibu Rahmawati');
        $response->assertSee('TOTAL:');
    }

    public function test_purchase_order_print_view_renders(): void
    {
        $supplier = Supplier::firstOrFail();
        $product = Product::firstOrFail();

        $order = PurchaseOrder::create([
            'po_number' => 'PO-TEST-001',
            'supplier_id' => $supplier->id,
            'branch_id' => $this->branch->id,
            'order_date' => now()->toDateString(),
            'expected_date' => now()->addDays(3)->toDateString(),
            'subtotal' => 200000,
            'tax' => 22000,
            'grand_total' => 222000,
            'status' => 'APPROVED',
            'created_by' => $this->admin->id,
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 20000,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchase-orders.print', $order));

        $response->assertOk();
        $response->assertSee('SURAT PESANAN');
        $response->assertSee('PO-TEST-001');
        $response->assertSee($supplier->name);
        $response->assertSee($product->name);
    }

    public function test_stock_transfer_print_view_renders(): void
    {
        $wh1 = Warehouse::firstOrFail();
        $wh2 = Warehouse::where('id', '!=', $wh1->id)->first() ?? Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'WH-TEST-2',
            'name' => 'Gudang Cadangan',
            'status' => 'ACTIVE',
        ]);
        $product = Product::firstOrFail();

        $transfer = StockTransfer::create([
            'transfer_number' => 'TRF-TEST-001',
            'from_warehouse_id' => $wh1->id,
            'to_warehouse_id' => $wh2->id,
            'transfer_date' => now()->toDateString(),
            'status' => 'COMPLETED',
            'requested_by' => $this->admin->id,
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        $transfer->items()->create([
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response = $this->actingAs($this->admin)->get(route('stock-transfers.print', $transfer));

        $response->assertOk();
        $response->assertSee('SURAT JALAN');
        $response->assertSee('TRF-TEST-001');
        $response->assertSee($wh1->name);
        $response->assertSee($wh2->name);
    }

    public function test_report_print_view_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.print', [
            'type' => 'sales_daily',
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('DIAN MUSTIKA');
        $response->assertSee('Penjualan Harian');
    }

    public function test_booking_to_pos_prefill_and_completion(): void
    {
        $customer = Customer::create([
            'customer_code' => 'CUST-BKG-01',
            'name' => 'Ibu Kartini',
            'phone' => '081234567890',
            'branch_id' => $this->branch->id,
        ]);

        $service = Service::firstOrFail();
        $employee = Employee::create([
            'employee_code' => 'EMP-BKG-01',
            'full_name' => 'Dewi Terapis',
            'branch_id' => $this->branch->id,
            'employment_status' => 'PERMANENT',
            'status' => 'ACTIVE',
        ]);

        $booking = Booking::create([
            'booking_number' => 'BKG-CONVERT-01',
            'customer_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'booking_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'CONFIRMED',
            'created_by' => $this->admin->id,
        ]);

        $booking->items()->create([
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'status' => 'CONFIRMED',
        ]);

        // 1. Visit POS create with booking_id
        $createResponse = $this->actingAs($this->admin)->get(route('sales.create', ['booking_id' => $booking->id]));
        $createResponse->assertOk();
        $createResponse->assertSee('BKG-CONVERT-01');
        $createResponse->assertSee('Ibu Kartini');

        // 2. Submit sales transaction linked to booking
        $storeResponse = $this->actingAs($this->admin)->post(route('sales.store'), [
            'booking_id' => $booking->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'transaction_date' => now()->toDateString(),
            'items' => [
                [
                    'ref' => "SERVICE:{$service->id}",
                    'quantity' => 1,
                    'unit_price' => 150000,
                    'discount' => 0,
                    'employee_id' => $employee->id,
                ],
            ],
        ]);

        $storeResponse->assertRedirect();
        $this->assertEquals('COMPLETED', $booking->fresh()->status);
    }
}
