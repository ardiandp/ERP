<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DigitalAccount extends Model
{
    protected $fillable = ['category_id', 'name', 'username', 'email', 'url', 'owner_type', 'owner_id', 'renewal_date', 'status', 'notes'];

    protected $casts = ['renewal_date' => 'date'];

    public function category()
    {
        return $this->belongsTo(DigitalAccountCategory::class, 'category_id');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
