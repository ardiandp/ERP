@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Expenses</h4>
    <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">Tambah Expense</a>
</div>
<form class="row g-2 mb-3" method="GET">
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua Status</option>
            @foreach(['DRAFT', 'APPROVED', 'PAID'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-outline-secondary">Filter</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>Number</th><th>Tanggal</th><th>Kategori</th><th>Akun</th><th class="text-end">Amount</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($expenses as $expense)
        <tr>
            <td>{{ $expense->expense_number }}</td>
            <td>{{ $expense->expense_date?->format('d/m/Y') }}</td>
            <td>{{ $expense->category?->name }}</td>
            <td>{{ $expense->financialAccount?->account_name }}</td>
            <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
            <td><span class="badge bg-{{ $expense->status === 'APPROVED' ? 'success' : ($expense->status === 'PAID' ? 'primary' : 'secondary') }}">{{ $expense->status }}</span></td>
            <td><a href="{{ route('expenses.show', $expense) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada expense.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $expenses->links() }}
@endsection
