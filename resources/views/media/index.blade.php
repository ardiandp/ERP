@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Media Files</h4>
    <a href="{{ route('media.create') }}" class="btn btn-primary btn-sm">Unggah File</a>
</div>
<table class="table table-striped">
    <thead><tr><th>#</th><th>Nama Asli</th><th>MIME</th><th>Ukuran</th><th>Diunggah Oleh</th><th>Waktu</th><th></th></tr></thead>
    <tbody>
        @forelse($files as $file)
        <tr>
            <td>{{ $file->id }}</td>
            <td>{{ $file->original_name }}</td>
            <td>{{ $file->mime_type }}</td>
            <td>{{ number_format($file->file_size / 1024, 1) }} KB</td>
            <td>{{ $file->uploader?->name ?? '-' }}</td>
            <td>{{ $file->uploaded_at?->format('d/m/Y H:i') }}</td>
            <td>
                <a href="{{ route('media.show', $file) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                <form method="POST" action="{{ route('media.destroy', $file) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada file.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $files->links() }}
@endsection
