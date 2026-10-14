<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMovement;
use App\Models\Branch;
use App\Services\AssetService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AssetController extends Controller
{
    public function __construct(protected AssetService $service) {}

    public function index()
    {
        return view('assets.index', [
            'assets' => Asset::with('category', 'branch')->latest('id')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('assets.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->service->create($this->validated($request));

        return redirect()->route('assets.index')->with('success', 'Aset dibuat.');
    }

    public function show(Asset $asset)
    {
        $asset->load('category', 'branch', 'movements.fromBranch', 'movements.toBranch', 'movements.movedBy');

        return view('assets.show', [
            'asset' => $asset,
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function edit(Asset $asset)
    {
        return view('assets.edit', array_merge(['asset' => $asset], $this->formData()));
    }

    public function update(Request $request, Asset $asset)
    {
        $this->service->update($asset, $this->validated($request, $asset));

        return redirect()->route('assets.index')->with('success', 'Aset diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Aset dihapus.');
    }

    public function move(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'to_branch_id' => ['nullable', 'exists:branches,id'],
            'to_location' => ['nullable', 'string'],
            'movement_date' => ['required', 'date'],
            'reason' => ['nullable', 'string'],
        ]);

        $this->service->move($asset, $data);

        return back()->with('success', 'Mutasi aset dicatat.');
    }

    public function approveMovement(AssetMovement $asset_movement)
    {
        try {
            $this->service->approveMovement($asset_movement);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Mutasi aset disetujui.');
    }

    protected function validated(Request $request, ?Asset $asset = null): array
    {
        return $request->validate([
            'asset_code' => ['nullable', 'unique:assets,asset_code'.($asset ? ','.$asset->id : '')],
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'name' => ['required', 'string'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'location' => ['nullable', 'string'],
            'condition' => ['required', 'in:GOOD,FAIR,DAMAGED'],
            'status' => ['required', 'in:ACTIVE,INACTIVE,DISPOSED'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function formData(): array
    {
        return [
            'categories' => AssetCategory::orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
        ];
    }
}
