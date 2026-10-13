<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\StockTransferService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class StockTransferController extends Controller
{
    public function __construct(protected StockTransferService $transfers) {}

    public function index()
    {
        return view('stock-transfers.index', [
            'transfers' => StockTransfer::with('fromWarehouse', 'toWarehouse')->latest('id')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('stock-transfers.create', [
            'warehouses' => Warehouse::where('status', 'ACTIVE')->orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        try {
            $transfer = $this->transfers->create($data, $request->input('items'));
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
        }

        return redirect()->route('stock-transfers.show', $transfer)->with('success', 'Transfer stok dibuat.');
    }

    public function show(StockTransfer $stock_transfer)
    {
        $stock_transfer->load('fromWarehouse', 'toWarehouse', 'items.product');

        return view('stock-transfers.show', ['transfer' => $stock_transfer]);
    }

    public function approve(StockTransfer $stock_transfer)
    {
        try {
            $this->transfers->approve($stock_transfer);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('success', 'Transfer stok disetujui dan stok dipindahkan.');
    }
}
