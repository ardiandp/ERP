<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillTemplate;
use App\Models\Branch;
use App\Services\BillService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BillController extends Controller
{
    public function __construct(protected BillService $bills) {}

    public function index(Request $request)
    {
        $bills = Bill::with('template', 'branch')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('due_date')
            ->paginate(20)
            ->withQueryString();

        return view('bills.index', ['bills' => $bills]);
    }

    public function generate()
    {
        return view('bills.generate', [
            'templates' => BillTemplate::where('status', 'ACTIVE')->with('branch')->orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function storeGenerate(Request $request)
    {
        $data = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'template_ids' => ['nullable', 'array'],
            'template_ids.*' => ['exists:bill_templates,id'],
            'amounts' => ['nullable', 'array'],
            'amounts.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $created = $this->bills->generate($data);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['generate' => $exception->getMessage()]);
        }

        $count = count($created);

        return redirect()->route('bills.index')->with('success', $count > 0 ? "{$count} tagihan berhasil dibuat." : 'Tidak ada tagihan baru (sudah pernah dibuat untuk periode ini).');
    }

    public function show(Bill $bill)
    {
        $bill->load('template', 'branch', 'expenses');

        return view('bills.show', ['bill' => $bill]);
    }

    public function pay(Bill $bill)
    {
        try {
            $this->bills->markPaid($bill);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('success', 'Tagihan ditandai lunas.');
    }
}
