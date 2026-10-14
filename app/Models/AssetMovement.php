<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetMovement extends Model
{
    protected $fillable = ['asset_id', 'from_branch_id', 'to_branch_id', 'from_location', 'to_location', 'movement_date', 'reason', 'moved_by', 'approved_by'];

    protected $casts = ['movement_date' => 'date'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function movedBy()
    {
        return $this->belongsTo(User::class, 'moved_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
