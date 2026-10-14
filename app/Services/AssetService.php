<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetMovement;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetService
{
    public function create(array $data): Asset
    {
        return DB::transaction(function () use ($data) {
            return Asset::create([
                'asset_code' => ! empty($data['asset_code']) ? $data['asset_code'] : DocumentNumber::generate('AST', Asset::class, 'asset_code'),
                'asset_category_id' => $data['asset_category_id'],
                'name' => $data['name'],
                'purchase_date' => $data['purchase_date'] ?? null,
                'purchase_price' => $data['purchase_price'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'location' => $data['location'] ?? null,
                'condition' => $data['condition'] ?? 'GOOD',
                'status' => $data['status'] ?? 'ACTIVE',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function update(Asset $asset, array $data): Asset
    {
        $asset->update($data);

        return $asset;
    }

    public function move(Asset $asset, array $data): AssetMovement
    {
        return DB::transaction(function () use ($asset, $data) {
            $movement = AssetMovement::create([
                'asset_id' => $asset->id,
                'from_branch_id' => $asset->branch_id,
                'to_branch_id' => $data['to_branch_id'] ?? null,
                'from_location' => $asset->location,
                'to_location' => $data['to_location'] ?? null,
                'movement_date' => $data['movement_date'] ?? now()->toDateString(),
                'reason' => $data['reason'] ?? null,
                'moved_by' => auth()->id(),
            ]);

            $asset->update([
                'branch_id' => $data['to_branch_id'] ?? $asset->branch_id,
                'location' => $data['to_location'] ?? $asset->location,
            ]);

            return $movement;
        });
    }

    public function approveMovement(AssetMovement $movement): AssetMovement
    {
        if ($movement->approved_by) {
            throw new InvalidArgumentException('Mutasi aset sudah disetujui.');
        }

        $movement->update(['approved_by' => auth()->id()]);

        return $movement;
    }
}
