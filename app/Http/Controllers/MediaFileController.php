<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\MediaFile;
use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaFileController extends Controller
{
    protected array $attachableTypes = [
        'asset' => Asset::class,
        'employee' => Employee::class,
    ];

    public function __construct(protected MediaService $service) {}

    public function index()
    {
        return view('media.index', [
            'files' => MediaFile::with('uploader')->latest('id')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('media.create', [
            'assets' => Asset::orderBy('name')->get(),
            'employees' => Employee::orderBy('full_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'attachable_type' => ['nullable', 'in:asset,employee'],
            'attachable_id' => ['nullable', 'integer'],
            'collection_name' => ['nullable', 'string'],
        ]);

        $file = $this->service->store($request->file('file'));

        if (! empty($data['attachable_type']) && ! empty($data['attachable_id'])) {
            $this->service->attach(
                $file,
                $this->attachableTypes[$data['attachable_type']],
                (int) $data['attachable_id'],
                $data['collection_name'] ?? null,
            );
        }

        return redirect()->route('media.index')->with('success', 'File diunggah.');
    }

    public function show(MediaFile $media)
    {
        $media->load('uploader', 'attachments');

        return view('media.show', ['media' => $media]);
    }

    public function destroy(MediaFile $media)
    {
        $this->service->delete($media);

        return redirect()->route('media.index')->with('success', 'File dihapus.');
    }
}
