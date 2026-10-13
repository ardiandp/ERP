<?php

namespace Tests\Feature;

use App\Models\BankTransaction;
use App\Models\Bill;
use App\Models\BillTemplate;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialAccount;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\FinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5FinanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_phase5_pages_render(): void
    {
        $this->actingAs($this->admin)->get(route('finance.accounts'))->assertOk();
        $this->actingAs($this->admin)->get(route('bank-transactions.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('bank-transactions.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('bank-reconciliations.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('bank-reconciliations.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('bill-templates.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('bill-templates.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('bills.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('bills.generate'))->assertOk();
        $this->actingAs($this->admin)->get(route('expenses.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('expenses.create'))->assertOk();
    }

    public function test_expense_is_created_and_approved(): void
    {
        $category = ExpenseCategory::firstOrFail();
        $account = FinancialAccount::firstOrFail();

        $this->actingAs($this->admin)->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'financial_account_id' => $account->id,
            'amount' => 150000,
            'expense_date' => now()->toDateString(),
            'description' => 'Beli ATK',
        ])->assertRedirect();

        $expense = Expense::firstOrFail();
        $this->assertSame('DRAFT', $expense->status);
        $this->assertStringStartsWith('EXP-', $expense->expense_number);

        $this->actingAs($this->admin)->patch(route('expenses.approve', $expense))->assertRedirect();

        $this->assertSame('APPROVED', $expense->fresh()->status);
        $this->assertNotNull($expense->fresh()->approved_at);
    }

    public function test_bills_generated_from_templates_and_paid(): void
    {
        $templateCount = BillTemplate::where('status', 'ACTIVE')->count();
        $this->assertGreaterThan(0, $templateCount);

        $this->actingAs($this->admin)->post(route('bills.store-generate'), [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
        ])->assertRedirect();

        $this->assertSame($templateCount, Bill::count());

        $bill = Bill::firstOrFail();
        $this->assertSame('UNPAID', $bill->status);

        $this->actingAs($this->admin)->patch(route('bills.pay', $bill))->assertRedirect();

        $this->assertSame('PAID', $bill->fresh()->status);
        $this->assertNotNull($bill->fresh()->paid_at);
    }

    public function test_bank_transaction_reconciled_with_payment(): void
    {
        $account = FinancialAccount::where('account_type', 'BANK')->firstOrFail();

        $this->actingAs($this->admin)->post(route('bank-transactions.store'), [
            'financial_account_id' => $account->id,
            'transaction_date' => now()->toDateString(),
            'transaction_type' => 'IN',
            'amount' => 200000,
            'reference_number' => 'TRF-001',
        ])->assertRedirect();

        $bankTransaction = BankTransaction::firstOrFail();
        $this->assertSame('UNRECONCILED', $bankTransaction->reconciliation_status);

        $payment = $this->makePayment($account, 200000);

        $this->actingAs($this->admin)->post(route('bank-reconciliations.store'), [
            'bank_transaction_id' => $bankTransaction->id,
            'payment_id' => $payment->id,
            'matched_amount' => 200000,
        ])->assertRedirect();

        $this->assertSame('RECONCILED', $bankTransaction->fresh()->reconciliation_status);
        $this->assertDatabaseHas('bank_reconciliations', [
            'bank_transaction_id' => $bankTransaction->id,
            'payment_id' => $payment->id,
        ]);
    }

    public function test_finance_account_balance_is_computed(): void
    {
        $account = FinancialAccount::where('account_type', 'BANK')->firstOrFail();
        $category = ExpenseCategory::firstOrFail();

        $this->actingAs($this->admin)->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'financial_account_id' => $account->id,
            'amount' => 50000,
            'expense_date' => now()->toDateString(),
        ])->assertRedirect();

        $expense = Expense::firstOrFail();
        $this->actingAs($this->admin)->patch(route('expenses.approve', $expense));

        $this->actingAs($this->admin)->post(route('bank-transactions.store'), [
            'financial_account_id' => $account->id,
            'transaction_date' => now()->toDateString(),
            'transaction_type' => 'IN',
            'amount' => 200000,
        ]);

        $this->makePayment($account, 100000);

        $balances = app(FinanceService::class)->accountsWithBalances();
        $row = $balances->firstWhere('id', $account->id);

        $expected = (float) $account->opening_balance + 100000 + 200000 - 50000;
        $this->assertEquals($expected, (float) $row->computed_balance);
    }

    protected function makePayment(FinancialAccount $account, float $amount): Payment
    {
        return Payment::create([
            'payment_number' => 'PAY-'.uniqid(),
            'transaction_type' => 'App\Models\SalesTransaction',
            'transaction_id' => 1,
            'payment_method_id' => PaymentMethod::firstOrFail()->id,
            'financial_account_id' => $account->id,
            'amount' => $amount,
            'payment_date' => now()->toDateString(),
            'status' => 'POSTED',
            'created_by' => $this->admin->id,
        ]);
    }
}
