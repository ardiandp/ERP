<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\EmployeeSalaryStructure;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\InventoryStock;
use App\Models\PaymentMethod;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use App\Services\PayrollService;
use App\Services\PurchaseOrderService;
use App\Services\StockTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $cashier;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
        $this->owner = User::where('email', 'owner@dianmustika.test')->firstOrFail();
        $this->cashier = User::where('email', 'kasir@dianmustika.test')->first() ?? $this->admin;
    }

    public function test_complete_customer_treatment_to_pos_to_commission_and_payroll_lifecycle(): void
    {
        $branch = Branch::firstOrFail();
        $warehouse = Warehouse::where('branch_id', $branch->id)->first() ?? Warehouse::firstOrFail();

        $customer = Customer::firstOrCreate(
            ['customer_code' => 'CUST-E2E-001'],
            ['name' => 'Siti Rahmawati', 'phone' => '081299990001', 'email' => 'siti.rahmawati@example.test', 'status' => 'ACTIVE']
        );

        $service = Service::firstOrFail();
        $product = Product::firstOrFail();
        $paymentMethod = PaymentMethod::where('status', 'ACTIVE')->firstOrFail();

        // 1. Setup therapist with active salary structure
        $therapist = Employee::firstOrCreate(
            ['employee_code' => 'EMP-E2E-001'],
            ['full_name' => 'Dewi Anggraini (Terapis)', 'branch_id' => $branch->id, 'join_date' => now()->toDateString(), 'status' => 'ACTIVE']
        );

        EmployeeSalaryStructure::firstOrCreate(
            ['employee_id' => $therapist->id, 'status' => 'ACTIVE'],
            ['basic_salary' => 3500000, 'effective_date' => now()->startOfMonth()->toDateString()]
        );

        // Ensure warehouse has stock
        InventoryStock::updateOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
            ['quantity' => 50, 'reserved_quantity' => 0]
        );

        // 2. Customer creates treatment booking
        $booking = Booking::create([
            'booking_number' => 'BK-E2E-001',
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'employee_id' => $therapist->id,
            'booking_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'CHECKED_IN',
            'source' => 'ONLINE',
            'notes' => 'Treatment E2E Booking',
        ]);

        // Verify prefill endpoint
        $prefillResponse = $this->actingAs($this->cashier)->get(route('sales.create', ['booking_id' => $booking->id]));
        $prefillResponse->assertOk();
        $prefillResponse->assertSee($customer->name);

        // Initial product stock
        $initialStock = (float) InventoryStock::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->value('quantity');

        // 3. Process POS checkout with treatment + retail product
        $servicePrice = 200000;
        $productPrice = 50000;
        $productQty = 2;
        $paidAmount = $servicePrice + ($productPrice * $productQty);

        $salesResponse = $this->actingAs($this->cashier)->post(route('sales.store'), [
            'booking_id' => $booking->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'transaction_date' => now()->toDateString(),
            'discount' => 0,
            'tax' => 0,
            'payment_method_id' => $paymentMethod->id,
            'paid_amount' => $paidAmount,
            'items' => [
                [
                    'ref' => "SERVICE:{$service->id}",
                    'quantity' => 1,
                    'unit_price' => $servicePrice,
                    'discount' => 0,
                    'employee_id' => $therapist->id,
                ],
                [
                    'ref' => "PRODUCT:{$product->id}",
                    'quantity' => $productQty,
                    'unit_price' => $productPrice,
                    'discount' => 0,
                ],
            ],
        ]);

        $salesResponse->assertRedirect();
        $sale = SalesTransaction::latest('id')->firstOrFail();

        // 4. Verify transaction, booking completion, and stock reduction
        $this->assertEquals('COMPLETED', $sale->status);
        $this->assertEquals($paidAmount, (float) $sale->grand_total);
        $this->assertEquals('COMPLETED', $booking->fresh()->status);

        $finalStock = (float) InventoryStock::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->value('quantity');
        $this->assertEquals($initialStock - $productQty, $finalStock);

        // 5. Verify Commission generated for the therapist
        $commission = EmployeeCommission::where('employee_id', $therapist->id)
            ->where('status', 'PENDING')
            ->latest('id')
            ->first();
        $this->assertNotNull($commission, 'Therapist commission must be generated upon service sale.');
        $this->assertGreaterThan(0, (float) $commission->commission_amount);

        // 6. Verify Daily Cashier Closing reflects the transaction
        $closingView = $this->actingAs($this->cashier)->get(route('sales.closing', ['date' => now()->toDateString()]));
        $closingView->assertOk();
        $closingView->assertSee($sale->invoice_number);

        $closingPrint = $this->actingAs($this->cashier)->get(route('sales.closing.print', ['date' => now()->toDateString()]));
        $closingPrint->assertOk();
        $closingPrint->assertSee('BERITA ACARA TUTUP KASIR');

        // 7. Generate Payroll for the therapist including commission
        $period = PayrollPeriod::create([
            'code' => 'PP-E2E-'.now()->format('Ym'),
            'name' => 'Payroll Period E2E '.now()->format('M Y'),
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'payment_date' => now()->toDateString(),
            'status' => 'OPEN',
        ]);

        $payrollService = app(PayrollService::class);
        $payrollRecords = $payrollService->generate($period);
        $this->assertNotEmpty($payrollRecords);

        $therapistPayroll = Payroll::where('payroll_period_id', $period->id)
            ->where('employee_id', $therapist->id)
            ->firstOrFail();

        // Check commission item exists in payroll
        $commissionItem = $therapistPayroll->items()
            ->where('component_name', 'Commission')
            ->first();
        $this->assertNotNull($commissionItem, 'Payroll items must contain the therapist commission item.');

        // 8. Approve Payroll and Generate Payslip
        $payrollService->approve($therapistPayroll);
        $payslip = $payrollService->generatePayslip($therapistPayroll);
        $this->assertInstanceOf(Payslip::class, $payslip);

        $payslipPrint = $this->actingAs($this->admin)->get(route('payslips.print', $payslip));
        $payslipPrint->assertOk();
        $payslipPrint->assertSee($therapist->full_name);

        // 9. Verify POS receipt print
        $receiptPrint = $this->actingAs($this->cashier)->get(route('sales.print', $sale));
        $receiptPrint->assertOk();
        $receiptPrint->assertSee($sale->invoice_number);
    }

    public function test_complete_purchasing_and_stock_transfer_lifecycle(): void
    {
        $branch = Branch::firstOrFail();
        $otherBranch = Branch::where('id', '!=', $branch->id)->first() ?? Branch::create(['company_id' => $branch->company_id, 'code' => 'BR-TEST-2', 'name' => 'Cabang E2E 2']);
        $warehouse1 = Warehouse::where('branch_id', $branch->id)->first() ?? Warehouse::create(['branch_id' => $branch->id, 'code' => 'WH-E2E-1', 'name' => 'Gudang E2E 1']);
        $warehouse2 = Warehouse::where('branch_id', $otherBranch->id)->first() ?? Warehouse::create(['branch_id' => $otherBranch->id, 'code' => 'WH-E2E-2', 'name' => 'Gudang E2E 2']);
        $supplier = Supplier::firstOrFail();
        $product = Product::firstOrFail();

        // 1. Create Purchase Order
        $poService = app(PurchaseOrderService::class);
        $this->actingAs($this->admin);

        $po = $poService->create([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'notes' => 'PO E2E Test',
        ], [
            [
                'product_id' => $product->id,
                'quantity' => 20,
                'unit_price' => 25000,
            ],
        ]);

        $this->assertEquals('DRAFT', $po->status);

        // 2. Approve Purchase Order
        $po = $poService->approve($po);
        $this->assertEquals('APPROVED', $po->status);

        // Verify PO printable view
        $poPrint = $this->actingAs($this->admin)->get(route('purchase-orders.print', $po));
        $poPrint->assertOk();
        $poPrint->assertSee($po->po_number);

        // 3. Receive Goods (Goods Receipt)
        $initialStockW1 = (float) InventoryStock::where('warehouse_id', $warehouse1->id)
            ->where('product_id', $product->id)
            ->value('quantity');

        $grService = app(GoodsReceiptService::class);
        $receipt = $grService->create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse1->id,
            'receipt_date' => now()->toDateString(),
        ], [
            [
                'product_id' => $product->id,
                'quantity' => 20,
                'unit_cost' => 25000,
            ],
        ]);

        $this->assertEquals('POSTED', $receipt->status);
        $newStockW1 = (float) InventoryStock::where('warehouse_id', $warehouse1->id)
            ->where('product_id', $product->id)
            ->value('quantity');
        $this->assertEquals($initialStockW1 + 20, $newStockW1);

        // 4. Transfer Stock from Warehouse 1 to Warehouse 2
        $transferService = app(StockTransferService::class);
        $transfer = $transferService->create([
            'from_warehouse_id' => $warehouse1->id,
            'to_warehouse_id' => $warehouse2->id,
            'transfer_date' => now()->toDateString(),
            'notes' => 'Transfer E2E Test',
        ], [
            [
                'product_id' => $product->id,
                'quantity' => 5,
            ],
        ]);

        $this->assertEquals('DRAFT', $transfer->status);

        // Approve transfer -> updates stock in both source and destination
        $transfer = $transferService->approve($transfer);
        $this->assertEquals('COMPLETED', $transfer->status);

        $afterTransferW1 = (float) InventoryStock::where('warehouse_id', $warehouse1->id)
            ->where('product_id', $product->id)
            ->value('quantity');
        $this->assertEquals($newStockW1 - 5, $afterTransferW1);

        // Verify Surat Jalan delivery order print
        $transferPrint = $this->actingAs($this->admin)->get(route('stock-transfers.print', $transfer));
        $transferPrint->assertOk();
        $transferPrint->assertSee($transfer->transfer_number);
    }

    public function test_expense_and_executive_reporting_lifecycle(): void
    {
        $branch = Branch::firstOrFail();
        $category = ExpenseCategory::firstOrFail();

        // 1. Record Expense
        $expense = Expense::create([
            'expense_number' => 'EXP-E2E-001',
            'branch_id' => $branch->id,
            'expense_category_id' => $category->id,
            'expense_date' => now()->toDateString(),
            'amount' => 175000,
            'description' => 'Pembelian sabun cuci dan desinfektan operasional',
            'status' => 'APPROVED',
            'payment_status' => 'PAID',
        ]);

        // 2. Verify Executive Reports view
        $reportIndex = $this->actingAs($this->owner)->get(route('reports.index', [
            'type' => 'expense_by_category',
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
            'branch_id' => $branch->id,
        ]));
        $reportIndex->assertOk();
        $reportIndex->assertSee('175.000');

        // 3. Verify Executive Report Print
        $reportPrint = $this->actingAs($this->owner)->get(route('reports.print', [
            'type' => 'expense_by_category',
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
            'branch_id' => $branch->id,
        ]));
        $reportPrint->assertOk();
        $reportPrint->assertSee('DIAN MUSTIKA');
        $reportPrint->assertSee('Pengeluaran per Kategori');
        $reportPrint->assertSee('175.000');
    }
}
