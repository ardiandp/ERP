<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ExpenseService;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalsTest extends TestCase
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

    public function test_approvals_index_renders_with_pending_lists(): void
    {
        $response = $this->actingAs($this->admin)->get(route('approvals.index'));

        $response->assertOk();
        $response->assertSee('Pusat Persetujuan');
        $response->assertSee('Purchase Orders');
        $response->assertSee('Biaya / Expenses');
        $response->assertSee('Transfer Stok');
        $response->assertSee('Stock Opname');
        $response->assertSee('Payroll');
    }

    public function test_approvals_can_approve_purchase_order(): void
    {
        $supplier = Supplier::firstOrFail();
        $product = Product::firstOrFail();
        $poService = app(PurchaseOrderService::class);

        $order = $poService->create([
            'supplier_id' => $supplier->id,
            'branch_id' => $this->branch->id,
            'order_date' => now()->toDateString(),
        ], [
            ['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 50000],
        ]);

        $this->assertEquals('DRAFT', $order->status);

        $response = $this->actingAs($this->admin)->patch(route('approvals.approve', [
            'type' => 'purchase_order',
            'id' => $order->id,
        ]));

        $response->assertRedirect();
        $this->assertEquals('APPROVED', $order->fresh()->status);
    }

    public function test_approvals_can_reject_purchase_order(): void
    {
        $supplier = Supplier::firstOrFail();
        $product = Product::firstOrFail();
        $poService = app(PurchaseOrderService::class);

        $order = $poService->create([
            'supplier_id' => $supplier->id,
            'branch_id' => $this->branch->id,
            'order_date' => now()->toDateString(),
        ], [
            ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 50000],
        ]);

        $response = $this->actingAs($this->admin)->patch(route('approvals.reject', [
            'type' => 'purchase_order',
            'id' => $order->id,
        ]));

        $response->assertRedirect();
        $this->assertEquals('CANCELLED', $order->fresh()->status);
    }

    public function test_approvals_can_approve_expense(): void
    {
        $category = ExpenseCategory::firstOrFail();
        $expenseService = app(ExpenseService::class);

        $expense = $expenseService->create([
            'branch_id' => $this->branch->id,
            'expense_category_id' => $category->id,
            'amount' => 150000,
            'expense_date' => now()->toDateString(),
            'description' => 'Listrik operasional',
        ]);

        $this->assertEquals('DRAFT', $expense->status);

        $response = $this->actingAs($this->admin)->patch(route('approvals.approve', [
            'type' => 'expense',
            'id' => $expense->id,
        ]));

        $response->assertRedirect();
        $this->assertEquals('APPROVED', $expense->fresh()->status);
    }

    public function test_approvals_can_reject_expense(): void
    {
        $category = ExpenseCategory::firstOrFail();
        $expenseService = app(ExpenseService::class);

        $expense = $expenseService->create([
            'branch_id' => $this->branch->id,
            'expense_category_id' => $category->id,
            'amount' => 200000,
            'expense_date' => now()->toDateString(),
            'description' => 'Renovasi',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('approvals.reject', [
            'type' => 'expense',
            'id' => $expense->id,
        ]));

        $response->assertRedirect();
        $this->assertEquals('REJECTED', $expense->fresh()->status);
    }
}
