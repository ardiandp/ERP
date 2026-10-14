<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaAttachment extends Model
{
    protected $fillable = ['media_file_id', 'attachable_type', 'attachable_id', 'collection_name'];

    public function mediaFile()
    {
        return $this->belongsTo(MediaFile::class);
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
