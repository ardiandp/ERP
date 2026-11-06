<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\User;
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
}
