<?php

namespace App\Http\Controllers;

use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use App\Models\Warehouse;
use App\Services\SalesReturnService;
use Illuminate\Http\Request;

class SalesReturnController extends Controller
{
    public function __construct(protected SalesReturnService $returns) {}

    public function index()
    {
        return view('sales.returns.index', [
            'returns' => SalesReturn::with('transaction')->latest('id')->paginate(15),
        ]);
    }

    public function create(SalesTransaction $sale)
    {
        $sale->load('items.product', 'items.service', 'items.package');

        return view('sales.returns.create', [
            'transaction' => $sale,
            'warehouses' => Warehouse::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, SalesTransaction $sale)
    {
        $data = $request->validate([
            'return_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'items' => ['required', 'array'],
            'items.*.sales_transaction_item_id' => ['required', 'exists:sales_transaction_items,id'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $items = collect($request->input('items'))
            ->filter(fn ($item) => (float) ($item['quantity'] ?? 0) > 0)
            ->values()
            ->all();

        if (empty($items)) {
            return back()->withErrors(['items' => 'Tidak ada item untuk diretur.']);
        }

        $return = $this->returns->create($sale, $items, $data);

        return redirect()->route('sales.returns.index')->with('success', "Retur {$return->return_number} dibuat.");
    }
}
