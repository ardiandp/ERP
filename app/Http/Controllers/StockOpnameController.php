<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockOpname;
use App\Models\Warehouse;
use App\Services\StockOpnameService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class StockOpnameController extends Controller
{
    public function __construct(protected StockOpnameService $opnames) {}

    public function index()
    {
        return view('stock-opnames.index', [
            'opnames' => StockOpname::with('warehouse')->latest('id')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('stock-opnames.create', [
            'warehouses' => Warehouse::where('status', 'ACTIVE')->orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.physical_quantity' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $opname = $this->opnames->create($data, $request->input('items'));
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
        }

        return redirect()->route('stock-opnames.show', $opname)->with('success', 'Stock opname dibuat.');
    }

    public function show(StockOpname $stock_opname)
    {
        $stock_opname->load('warehouse', 'items.product');

        return view('stock-opnames.show', ['opname' => $stock_opname]);
    }

    public function approve(StockOpname $stock_opname)
    {
        try {
            $this->opnames->approve($stock_opname);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('success', 'Stock opname disetujui dan stok disesuaikan.');
    }
}
