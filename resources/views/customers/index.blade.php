@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Customers</h4>
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Phone</th><th>Type</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($customers as $customer)
        <tr>
            <td>{{ $customer->customer_code }}</td>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ $customer->customer_type }}</td>
            <td>{{ $customer->status }}</td>
            <td>
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $customers->links() }}
@endsection
