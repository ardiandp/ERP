@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Struktur Gaji</h4>
<form method="POST" action="{{ route('salary-structures.store') }}">
    @csrf
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Karyawan</label>
            <select name="employee_id" class="form-select">
                <option value="">-- pilih --</option>
                @foreach($employees as $employee)
                <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>{{ $employee->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Effective Date</label><input type="date" name="effective_date" class="form-control" value="{{ old('effective_date', now()->toDateString()) }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Basic Salary</label><input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ old('basic_salary') }}"></div>
        <div class="col-md-12 mb-3"><label class="form-label">Catatan</label><input name="notes" class="form-control" value="{{ old('notes') }}"></div>
    </div>

    <h6>Komponen Gaji</h6>
    <table class="table table-sm">
        <thead><tr><th>Komponen</th><th>Amount</th><th>Calculation</th></tr></thead>
        <tbody>
            @for($i = 0; $i < 5; $i++)
            <tr>
                <td>
                    <select name="components[{{ $i }}][salary_component_id]" class="form-select form-select-sm">
                        <option value="">-</option>
                        @foreach($components as $component)
                        <option value="{{ $component->id }}">{{ $component->name }} ({{ $component->type }})</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" step="0.01" name="components[{{ $i }}][amount]" class="form-control form-control-sm"></td>
                <td>
                    <select name="components[{{ $i }}][calculation_type]" class="form-select form-select-sm">
                        <option value="FIXED">FIXED</option>
                        <option value="PERCENTAGE">PERCENTAGE</option>
                    </select>
                </td>
            </tr>
            @endfor
        </tbody>
    </table>
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Simpan</button>
</form>
@endsection
