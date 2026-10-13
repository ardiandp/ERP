<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PurchaseOrderController extends Controller
{
    public function __construct(protected PurchaseOrderService $orders) {}

    public function index(Request $request)
    {
        $orders = PurchaseOrder::with('supplier', 'branch')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('purchase-orders.index', ['orders' => $orders]);
    }

    public function create()
    {
        return view('purchase-orders.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $order = $this->orders->create($data, $request->input('items'));
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
        }

        return redirect()->route('purchase-orders.show', $order)->with('success', 'Purchase order dibuat.');
    }

    public function show(PurchaseOrder $purchase_order)
    {
        $purchase_order->load('supplier', 'branch', 'items.product', 'receipts');

        return view('purchase-orders.show', ['order' => $purchase_order]);
    }

    public function approve(PurchaseOrder $purchase_order)
    {
        try {
            $this->orders->approve($purchase_order);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('success', 'Purchase order disetujui.');
    }

    public function destroy(PurchaseOrder $purchase_order)
    {
        if ($purchase_order->status !== 'DRAFT') {
            return back()->withErrors(['status' => 'Hanya PO draft yang dapat dihapus.']);
        }

        $purchase_order->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order dihapus.');
    }

    protected function formData(): array
    {
        return [
            'suppliers' => Supplier::orderBy('name')->get(),
            'branches' => Branch::where('status', 'ACTIVE')->orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ];
    }
}
