<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class GoodsReceiptController extends Controller
{
    public function __construct(protected GoodsReceiptService $receipts) {}

    public function index()
    {
        return view('goods-receipts.index', [
            'receipts' => GoodsReceipt::with('supplier', 'warehouse')->latest('id')->paginate(15),
        ]);
    }

    public function create(Request $request)
    {
        $purchaseOrder = $request->filled('purchase_order')
            ? PurchaseOrder::with('items.product')->find($request->input('purchase_order'))
            : null;

        return view('goods-receipts.create', $this->formData() + ['purchaseOrder' => $purchaseOrder]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $receipt = $this->receipts->create($data, $request->input('items'));
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
        }

        return redirect()->route('goods-receipts.show', $receipt)->with('success', 'Penerimaan barang dicatat.');
    }

    public function show(GoodsReceipt $goods_receipt)
    {
        $goods_receipt->load('supplier', 'branch', 'warehouse', 'purchaseOrder', 'items.product');

        return view('goods-receipts.show', ['receipt' => $goods_receipt]);
    }

    protected function formData(): array
    {
        return [
            'suppliers' => Supplier::orderBy('name')->get(),
            'branches' => Branch::where('status', 'ACTIVE')->orderBy('name')->get(),
            'warehouses' => Warehouse::where('status', 'ACTIVE')->orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ];
    }
}
