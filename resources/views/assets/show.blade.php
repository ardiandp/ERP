@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Aset: {{ $asset->name }}</h4>
    <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>
<div class="card mb-3">
    <div class="card-body row">
        <div class="col-md-3"><strong>Code:</strong> {{ $asset->asset_code }}</div>
        <div class="col-md-3"><strong>Category:</strong> {{ $asset->category?->name }}</div>
        <div class="col-md-3"><strong>Branch:</strong> {{ $asset->branch?->name ?? '-' }}</div>
        <div class="col-md-3"><strong>Location:</strong> {{ $asset->location ?? '-' }}</div>
        <div class="col-md-3"><strong>Condition:</strong> {{ $asset->condition }}</div>
        <div class="col-md-3"><strong>Status:</strong> {{ $asset->status }}</div>
        <div class="col-md-3"><strong>Purchase Date:</strong> {{ $asset->purchase_date?->format('d/m/Y') ?? '-' }}</div>
        <div class="col-md-3"><strong>Purchase Price:</strong> {{ number_format((float) $asset->purchase_price, 2) }}</div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Catat Mutasi Aset</div>
    <div class="card-body">
        <form method="POST" action="{{ route('assets.movements.store', $asset) }}" class="row g-2">
            @csrf
            <div class="col-md-3">
                <select name="to_branch_id" class="form-select form-select-sm">
                    <option value="">-- cabang tujuan --</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><input name="to_location" class="form-control form-control-sm" placeholder="lokasi tujuan"></div>
            <div class="col-md-2"><input type="date" name="movement_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}"></div>
            <div class="col-md-3"><input name="reason" class="form-control form-control-sm" placeholder="alasan"></div>
            <div class="col-md-1"><button class="btn btn-sm btn-primary">Simpan</button></div>
        </form>
    </div>
</div>

<table class="table table-striped">
    <thead><tr><th>Tanggal</th><th>Dari</th><th>Ke</th><th>Alasan</th><th>Oleh</th><th>Disetujui</th><th></th></tr></thead>
    <tbody>
        @forelse($asset->movements as $movement)
        <tr>
            <td>{{ $movement->movement_date?->format('d/m/Y') }}</td>
            <td>{{ $movement->fromBranch?->name ?? '-' }} / {{ $movement->from_location ?? '-' }}</td>
            <td>{{ $movement->toBranch?->name ?? '-' }} / {{ $movement->to_location ?? '-' }}</td>
            <td>{{ $movement->reason ?? '-' }}</td>
            <td>{{ $movement->movedBy?->name ?? '-' }}</td>
            <td>{{ $movement->approvedBy?->name ?? '-' }}</td>
            <td>
                @if(! $movement->approved_by)
                <form method="POST" action="{{ route('asset-movements.approve', $movement) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm btn-outline-success">Setujui</button>
                </form>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada mutasi.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
