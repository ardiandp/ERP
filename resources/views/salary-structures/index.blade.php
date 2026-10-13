@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Salary Structures</h4>
    <a href="{{ route('salary-structures.create') }}" class="btn btn-primary btn-sm">Tambah Struktur</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Karyawan</th><th>Basic Salary</th><th>Effective</th><th>Komponen</th></tr></thead>
    <tbody>
        @forelse($employees as $employee)
        @php $structure = $employee->salaryStructures->first(); @endphp
        <tr>
            <td>{{ $employee->full_name }}</td>
            <td>{{ $structure ? number_format($structure->basic_salary, 2) : '-' }}</td>
            <td>{{ $structure?->effective_date?->format('d/m/Y') ?? '-' }}</td>
            <td>
                @forelse($employee->salaryComponents as $component)
                <span class="badge bg-secondary">{{ $component->salaryComponent?->name }}: {{ number_format($component->amount, 2) }} ({{ $component->calculation_type }})</span>
                @empty
                -
                @endforelse
            </td>
        </tr>
        @empty
        <tr><td colspan="4">Belum ada karyawan.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
