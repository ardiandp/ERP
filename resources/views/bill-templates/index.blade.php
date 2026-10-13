@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Bill Templates</h4>
    <a href="{{ route('bill-templates.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Frequency</th><th class="text-end">Default</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($templates as $template)
        <tr>
            <td>{{ $template->code }}</td>
            <td>{{ $template->name }}</td>
            <td>{{ $template->category }}</td>
            <td>{{ $template->frequency }}</td>
            <td class="text-end">{{ number_format($template->default_amount, 2) }}</td>
            <td>{{ $template->status }}</td>
            <td>
                <a href="{{ route('bill-templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('bill-templates.destroy', $template) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada template.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $templates->links() }}
@endsection
