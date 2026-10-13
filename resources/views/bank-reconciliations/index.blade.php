@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Bank Reconciliations</h4>
    <a href="{{ route('bank-reconciliations.create') }}" class="btn btn-primary btn-sm">Rekonsiliasi Baru</a>
</div>
<table class="table table-striped">
    <thead><tr><th>#</th><th>Bank Mutation</th><th>Payment</th><th class="text-end">Matched</th><th>Status</th><th>Reconciled At</th></tr></thead>
    <tbody>
        @forelse($reconciliations as $reconciliation)
        <tr>
            <td>{{ $reconciliation->id }}</td>
            <td>{{ $reconciliation->bankTransaction?->reference_number ?? $reconciliation->bankTransaction?->description }} ({{ $reconciliation->bankTransaction?->financialAccount?->account_name }})</td>
            <td>{{ $reconciliation->payment?->payment_number }}</td>
            <td class="text-end">{{ number_format($reconciliation->matched_amount, 2) }}</td>
            <td><span class="badge bg-success">{{ $reconciliation->status }}</span></td>
            <td>{{ $reconciliation->reconciled_at?->format('d/m/Y H:i') }}</td>
        </tr>
        @empty
        <tr><td colspan="6">Belum ada rekonsiliasi.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $reconciliations->links() }}
@endsection
