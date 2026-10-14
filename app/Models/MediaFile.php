<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    protected $fillable = ['file_name', 'original_name', 'mime_type', 'file_size', 'storage_disk', 'storage_path', 'uploaded_by', 'uploaded_at', 'checksum'];

    protected $casts = ['uploaded_at' => 'datetime'];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function attachments()
    {
        return $this->hasMany(MediaAttachment::class);
    }

    public function url(): string
    {
        return Storage::disk($this->storage_disk)->url($this->storage_path);
    }
}
