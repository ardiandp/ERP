@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Reports</h4>
    <a href="{{ route('reports.export', request()->query()) }}" class="btn btn-success btn-sm">Export CSV</a>
</div>

<form method="GET" action="{{ route('reports.index') }}" class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="type" class="form-select form-select-sm">
            @foreach($types as $key => $label)
            <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] }}"></div>
    <div class="col-md-2"><input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] }}"></div>
    <div class="col-md-3">
        <select name="branch_id" class="form-select form-select-sm">
            <option value="">Semua Cabang</option>
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected($filters['branch_id'] === $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-1"><button class="btn btn-primary btn-sm w-100">Tampilkan</button></div>
</form>

<div class="card">
    <div class="card-header">{{ $report['title'] }}</div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    @foreach($report['columns'] as $column)
                    <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($report['rows'] as $row)
                <tr>
                    @foreach($row as $value)
                    <td>@if(is_numeric($value)){{ (float) $value == (int) $value ? number_format((float) $value, 0, ',', '.') : number_format((float) $value, 2, ',', '.') }}@else{{ $value }}@endif</td>
                    @endforeach
                </tr>
                @empty
                <tr><td colspan="{{ count($report['columns']) }}">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
