<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialAccount;
use App\Models\Supplier;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ExpenseController extends Controller
{
    public function __construct(protected ExpenseService $expenses) {}

    public function index(Request $request)
    {
        $expenses = Expense::with('category', 'branch', 'financialAccount')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('expense_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('expenses.index', ['expenses' => $expenses]);
    }

    public function create()
    {
        return view('expenses.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'financial_account_id' => ['nullable', 'exists:financial_accounts,id'],
            'description' => ['nullable', 'string'],
        ]);

        $expense = $this->expenses->create($data);

        return redirect()->route('expenses.show', $expense)->with('success', 'Expense dicatat.');
    }

    public function show(Expense $expense)
    {
        $expense->load('category', 'branch', 'supplier', 'financialAccount');

        return view('expenses.show', ['expense' => $expense]);
    }

    public function approve(Expense $expense)
    {
        try {
            $this->expenses->approve($expense);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('success', 'Expense disetujui.');
    }

    protected function formData(): array
    {
        return [
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'accounts' => FinancialAccount::orderBy('account_name')->get(),
        ];
    }
}
