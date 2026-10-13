@extends('layouts.app')
@section('content')
<h4 class="mb-3">Generate Tagihan dari Template</h4>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('bills.store-generate') }}">
    @csrf
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Periode Mulai</label><input type="date" name="period_start" class="form-control" value="{{ old('period_start', now()->startOfMonth()->toDateString()) }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Periode Akhir</label><input type="date" name="period_end" class="form-control" value="{{ old('period_end', now()->endOfMonth()->toDateString()) }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Jatuh Tempo</label><input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->addDays(10)->toDateString()) }}"></div>
    </div>
    <table class="table table-sm">
        <thead><tr><th></th><th>Template</th><th>Frequency</th><th>Branch</th><th class="text-end">Amount</th></tr></thead>
        <tbody>
            @forelse($templates as $template)
            <tr>
                <td><input type="checkbox" name="template_ids[]" value="{{ $template->id }}" @checked(in_array($template->id, old('template_ids', [])))></td>
                <td>{{ $template->name }}</td>
                <td>{{ $template->frequency }}</td>
                <td>{{ $template->branch?->name }}</td>
                <td><input type="number" step="0.01" name="amounts[{{ $template->id }}]" class="form-control form-control-sm text-end" value="{{ old('amounts.'.$template->id, $template->default_amount) }}"></td>
            </tr>
            @empty
            <tr><td colspan="5">Tidak ada template aktif.</td></tr>
            @endforelse
        </tbody>
    </table>
    <button class="btn btn-primary">Generate</button>
</form>
@endsection
