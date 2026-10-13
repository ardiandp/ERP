<?php

namespace App\Http\Controllers;

use App\Models\BillTemplate;
use App\Models\Branch;
use App\Models\FinancialAccount;
use App\Models\Supplier;
use Illuminate\Http\Request;

class BillTemplateController extends Controller
{
    public function index()
    {
        return view('bill-templates.index', [
            'templates' => BillTemplate::with('branch', 'supplier')->latest('id')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('bill-templates.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        BillTemplate::create($data);

        return redirect()->route('bill-templates.index')->with('success', 'Template tagihan dibuat.');
    }

    public function edit(BillTemplate $bill_template)
    {
        return view('bill-templates.edit', array_merge(['template' => $bill_template], $this->formData()));
    }

    public function update(Request $request, BillTemplate $bill_template)
    {
        $bill_template->update($this->validated($request, $bill_template));

        return redirect()->route('bill-templates.index')->with('success', 'Template tagihan diperbarui.');
    }

    public function destroy(BillTemplate $bill_template)
    {
        $bill_template->delete();

        return back()->with('success', 'Template tagihan dihapus.');
    }

    protected function validated(Request $request, ?BillTemplate $template = null): array
    {
        return $request->validate([
            'code' => ['required', 'unique:bill_templates,code'.($template ? ','.$template->id : '')],
            'name' => ['required', 'string'],
            'category' => ['required', 'string'],
            'frequency' => ['required', 'in:MONTHLY,QUARTERLY,SEMESTERLY,YEARLY,ONE_TIME'],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'default_due_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'financial_account_id' => ['nullable', 'exists:financial_accounts,id'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function formData(): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'accounts' => FinancialAccount::orderBy('account_name')->get(),
        ];
    }
}
