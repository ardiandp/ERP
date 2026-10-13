@extends('layouts.app')
@section('content')
<h4 class="mb-3">Sales Returns</h4>
<table class="table table-striped">
    <thead><tr><th>No</th><th>Invoice</th><th>Date</th><th>Status</th><th>Reason</th></tr></thead>
    <tbody>
        @forelse($returns as $return)
        <tr>
            <td>{{ $return->return_number }}</td>
            <td>{{ $return->transaction?->invoice_number }}</td>
            <td>{{ optional($return->return_date)->format('Y-m-d') }}</td>
            <td>{{ $return->status }}</td>
            <td>{{ $return->reason }}</td>
        </tr>
        @empty
        <tr><td colspan="5">Belum ada retur.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $returns->links() }}
@endsection
