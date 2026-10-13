@extends('layouts.app')
@section('content')
<h4 class="mb-3">Employee Commissions</h4>
<form class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="employee_id" class="form-select">
            <option value="">Semua Karyawan</option>
            @foreach($employees as $employee)
            <option value="{{ $employee->id }}" @selected((string) request('employee_id') === (string) $employee->id)>{{ $employee->full_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Semua Status</option>
            @foreach(['PENDING', 'PAID'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>Employee</th><th>Invoice</th><th class="text-end">Commission</th><th>Status</th></tr></thead>
    <tbody>
        @forelse($commissions as $commission)
        <tr>
            <td>{{ $commission->employee?->full_name }}</td>
            <td>{{ $commission->transaction?->invoice_number }}</td>
            <td class="text-end">{{ number_format((float) $commission->commission_amount, 2) }}</td>
            <td>{{ $commission->status }}</td>
        </tr>
        @empty
        <tr><td colspan="4">Belum ada komisi.</td></tr>
        @endforelse
    </tbody>
    <tfoot><tr><th colspan="2" class="text-end">Total</th><td class="text-end fw-bold">{{ number_format((float) $total, 2) }}</td><td></td></tr></tfoot>
</table>
{{ $commissions->links() }}
@endsection
