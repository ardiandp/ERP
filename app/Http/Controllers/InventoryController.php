<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(protected InventoryService $inventory) {}

    public function stock(Request $request)
    {
        $stocks = InventoryStock::with(['warehouse', 'product'])
            ->when($request->filled('warehouse_id'), fn ($query) => $query->where('warehouse_id', $request->input('warehouse_id')))
            ->when($request->filled('search'), fn ($query) => $query->whereHas('product', fn ($q) => $q->where('name', 'like', '%'.$request->input('search').'%')))
            ->orderBy('warehouse_id')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.stock', [
            'stocks' => $stocks,
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function movements(Request $request)
    {
        $movements = InventoryMovement::with(['warehouse', 'product'])
            ->when($request->filled('warehouse_id'), fn ($query) => $query->where('warehouse_id', $request->input('warehouse_id')))
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->input('product_id')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.movements', [
            'movements' => $movements,
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function adjustmentForm()
    {
        return view('inventory.adjustment', [
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function storeAdjustment(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->inventory->adjust((int) $data['warehouse_id'], (int) $data['product_id'], (float) $data['quantity'], [
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('inventory.stock')->with('success', 'Stok disesuaikan.');
    }
}
