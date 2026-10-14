@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>File: {{ $media->original_name }}</h4>
    <a href="{{ route('media.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>
<div class="card mb-3">
    <div class="card-body row">
        <div class="col-md-4"><strong>Nama Simpan:</strong> {{ $media->file_name }}</div>
        <div class="col-md-4"><strong>MIME:</strong> {{ $media->mime_type }}</div>
        <div class="col-md-4"><strong>Disk:</strong> {{ $media->storage_disk }}</div>
        <div class="col-md-4"><strong>Path:</strong> {{ $media->storage_path }}</div>
        <div class="col-md-4"><strong>Ukuran:</strong> {{ number_format($media->file_size / 1024, 1) }} KB</div>
        <div class="col-md-4"><strong>Checksum:</strong> {{ $media->checksum }}</div>
        <div class="col-md-4 mt-2"><a href="{{ $media->url() }}" class="btn btn-sm btn-outline-primary" target="_blank">Buka File</a></div>
    </div>
</div>
<table class="table table-sm">
    <thead><tr><th>Terlampir Ke</th><th>ID</th><th>Koleksi</th></tr></thead>
    <tbody>
        @forelse($media->attachments as $attachment)
        <tr>
            <td>{{ $attachment->attachable_type }}</td>
            <td>{{ $attachment->attachable_id }}</td>
            <td>{{ $attachment->collection_name ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="3">Tidak ada lampiran.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
