@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>{{ $cfg['title'] }}</h4>
    <a href="{{ route('masterdata.create', $entity) }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr>
        @foreach($cfg['fields'] as $field => $def)<th>{{ $def['label'] ?? $field }}</th>@endforeach
        <th></th>
    </tr></thead>
    <tbody>
        @forelse($items as $item)
        <tr>
            @foreach($cfg['fields'] as $field => $def)
            <td>{{ data_get($item, $field) }}</td>
            @endforeach
            <td>
                <a href="{{ route('masterdata.edit', [$entity, $item->id]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('masterdata.destroy', [$entity, $item->id]) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="{{ count($cfg['fields']) + 1 }}">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $items->links() }}
@endsection
