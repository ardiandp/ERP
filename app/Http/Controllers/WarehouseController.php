<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('warehouses.index', ['warehouses' => Warehouse::with('branch')->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('warehouses.create', ['branches' => Branch::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $warehouse = Warehouse::create($data);
        AuditLog::record('create', $warehouse, [], $data);

        return redirect()->route('warehouses.index')->with('success', 'Gudang dibuat.');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', ['warehouse' => $warehouse, 'branches' => Branch::orderBy('name')->get()]);
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $this->validated($request, $warehouse);
        $warehouse->update($data);
        AuditLog::record('update', $warehouse, [], $data);

        return redirect()->route('warehouses.index')->with('success', 'Gudang diperbarui.');
    }

    public function destroy(Warehouse $warehouse)
    {
        AuditLog::record('delete', $warehouse, $warehouse->toArray());
        $warehouse->delete();

        return back()->with('success', 'Gudang dihapus.');
    }

    protected function validated(Request $request, ?Warehouse $warehouse = null): array
    {
        return $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'code' => ['required', 'unique:warehouses,code'.($warehouse ? ','.$warehouse->id : '')],
            'name' => ['required', 'string'],
            'warehouse_type' => ['required', 'string'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
    }
}
