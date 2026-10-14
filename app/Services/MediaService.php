<?php

namespace App\Services;

use App\Models\MediaAttachment;
use App\Models\MediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    public function store(UploadedFile $file, string $disk = 'public', string $directory = 'media'): MediaFile
    {
        $path = $file->store($directory, $disk);

        return MediaFile::create([
            'file_name' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'storage_disk' => $disk,
            'storage_path' => $path,
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
            'checksum' => md5_file($file->getRealPath()),
        ]);
    }

    public function attach(MediaFile $file, string $attachableType, int $attachableId, ?string $collection = null): MediaAttachment
    {
        return MediaAttachment::create([
            'media_file_id' => $file->id,
            'attachable_type' => $attachableType,
            'attachable_id' => $attachableId,
            'collection_name' => $collection,
        ]);
    }

    public function delete(MediaFile $file): void
    {
        Storage::disk($file->storage_disk)->delete($file->storage_path);
        $file->delete();
    }
}
