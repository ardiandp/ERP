@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Overtime</h4>
    <a href="{{ route('overtimes.create') }}" class="btn btn-primary btn-sm">Catat Lembur</a>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<table class="table table-striped">
    <thead><tr><th>Tanggal</th><th>Karyawan</th><th>Mulai</th><th>Selesai</th><th>Durasi (menit)</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($overtimes as $overtime)
        <tr>
            <td>{{ $overtime->overtime_date?->format('d/m/Y') }}</td>
            <td>{{ $overtime->employee?->full_name }}</td>
            <td>{{ $overtime->start_time }}</td>
            <td>{{ $overtime->end_time }}</td>
            <td>{{ $overtime->duration_minutes }}</td>
            <td><span class="badge bg-{{ $overtime->status === 'APPROVED' ? 'success' : 'secondary' }}">{{ $overtime->status }}</span></td>
            <td>
                @if($overtime->status !== 'APPROVED')
                <form method="POST" action="{{ route('overtimes.approve', $overtime) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm btn-outline-success">Setujui</button>
                </form>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada lembur.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $overtimes->links() }}
@endsection
