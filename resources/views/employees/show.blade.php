@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>{{ $employee->full_name }}</h4>
    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-secondary btn-sm">Edit</a>
</div>
<div class="row">
    <div class="col-md-6">
        <h6>Data Karyawan</h6>
        <table class="table table-bordered">
            <tr><th>Code</th><td>{{ $employee->employee_code }}</td></tr>
            <tr><th>NIK</th><td>{{ $employee->nik }}</td></tr>
            <tr><th>Branch</th><td>{{ $employee->branch->name ?? '-' }}</td></tr>
            <tr><th>Department</th><td>{{ $employee->department->name ?? '-' }}</td></tr>
            <tr><th>Position</th><td>{{ $employee->position->name ?? '-' }}</td></tr>
            <tr><th>Manager</th><td>{{ $employee->manager->full_name ?? '-' }}</td></tr>
            <tr><th>Join Date</th><td>{{ $employee->join_date }}</td></tr>
            <tr><th>Status</th><td>{{ $employee->status }}</td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6>Keluarga</h6>
        <table class="table table-sm table-bordered">
            <thead><tr><th>Nama</th><th>Hubungan</th><th>Tanggungan</th></tr></thead>
            <tbody>
                @forelse($employee->familyMembers as $fm)
                <tr><td>{{ $fm->name }}</td><td>{{ $fm->relationship }}</td><td>{{ $fm->is_dependent ? 'Ya' : 'Tidak' }}</td></tr>
                @empty
                <tr><td colspan="3">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
        <h6>BPJS / Asuransi</h6>
        <table class="table table-sm table-bordered">
            <thead><tr><th>Jenis</th><th>Provider</th><th>No. Peserta</th></tr></thead>
            <tbody>
                @forelse($employee->insurances as $ins)
                <tr><td>{{ $ins->insurance_type }}</td><td>{{ $ins->provider }}</td><td>{{ $ins->membership_number }}</td></tr>
                @empty
                <tr><td colspan="3">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
