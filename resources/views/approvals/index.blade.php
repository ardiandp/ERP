@extends('layouts.app')
@section('title', 'Pusat Persetujuan (Pending Approvals)')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Pusat Persetujuan (Pending Approvals)</h4>
        <span class="text-muted small">Kelola semua transaksi dan pengajuan yang memerlukan persetujuan</span>
    </div>
    <form method="GET" action="{{ route('approvals.index') }}" class="d-flex gap-2">
        <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Cabang</option>
            @foreach($branches as $b)
            <option value="{{ $b->id }}" @selected($branchId === $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Purchase Order</span>
            <h4 class="fw-bold mb-0 text-primary">{{ $purchaseOrders->count() }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Expense</span>
            <h4 class="fw-bold mb-0 text-danger">{{ $expenses->count() }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Transfer Stok</span>
            <h4 class="fw-bold mb-0 text-warning">{{ $stockTransfers->count() }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Stock Opname</span>
            <h4 class="fw-bold mb-0 text-info">{{ $stockOpnames->count() }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Payroll</span>
            <h4 class="fw-bold mb-0 text-success">{{ $payrolls->count() }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Lembur & Aset</span>
            <h4 class="fw-bold mb-0 text-dark">{{ $overtimes->count() + $assetMovements->count() }}</h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <ul class="nav nav-tabs customtab" id="approvalTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="po-tab" data-bs-toggle="tab" href="#tab-po" role="tab">
                    Purchase Orders <span class="badge bg-primary ms-1">{{ $purchaseOrders->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="expense-tab" data-bs-toggle="tab" href="#tab-expense" role="tab">
                    Biaya / Expenses <span class="badge bg-danger ms-1">{{ $expenses->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="transfer-tab" data-bs-toggle="tab" href="#tab-transfer" role="tab">
                    Transfer Stok <span class="badge bg-warning ms-1">{{ $stockTransfers->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="opname-tab" data-bs-toggle="tab" href="#tab-opname" role="tab">
                    Stock Opname <span class="badge bg-info ms-1">{{ $stockOpnames->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="payroll-tab" data-bs-toggle="tab" href="#tab-payroll" role="tab">
                    Payroll <span class="badge bg-success ms-1">{{ $payrolls->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="overtime-tab" data-bs-toggle="tab" href="#tab-overtime" role="tab">
                    Lembur <span class="badge bg-secondary ms-1">{{ $overtimes->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="asset-tab" data-bs-toggle="tab" href="#tab-asset" role="tab">
                    Mutasi Aset <span class="badge bg-dark ms-1">{{ $assetMovements->count() }}</span>
                </a>
            </li>
        </ul>

        <div class="tab-content p-3" id="approvalTabsContent">
            {{-- TAB PO --}}
            <div class="tab-pane fade show active" id="tab-po" role="tabpanel">
                <table class="table table-striped table-hover align-middle datatable-table">
                    <thead>
                        <tr>
                            <th>No. PO</th>
                            <th>Cabang</th>
                            <th>Supplier</th>
                            <th>Tanggal</th>
                            <th class="text-end">Total</th>
                            <th>Pembuat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                        <tr>
                            <td class="fw-bold">{{ $po->po_number }}</td>
                            <td>{{ $po->branch?->name }}</td>
                            <td>{{ $po->supplier?->name }}</td>
                            <td>{{ optional($po->order_date)->format('d/m/Y') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($po->grand_total, 0, ',', '.') }}</td>
                            <td>{{ $po->creator?->name }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-outline-secondary">Detail</a>
                                    <form method="POST" action="{{ route('approvals.approve', ['type' => 'purchase_order', 'id' => $po->id]) }}" class="d-inline" onsubmit="return confirm('Setujui Purchase Order ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-success">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('approvals.reject', ['type' => 'purchase_order', 'id' => $po->id]) }}" class="d-inline" onsubmit="return confirm('Tolak Purchase Order ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-danger">Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada purchase order pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB EXPENSE --}}
            <div class="tab-pane fade" id="tab-expense" role="tabpanel">
                <table class="table table-striped table-hover align-middle datatable-table">
                    <thead>
                        <tr>
                            <th>No. Expense</th>
                            <th>Cabang</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th class="text-end">Jumlah</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $exp)
                        <tr>
                            <td class="fw-bold">{{ $exp->expense_number }}</td>
                            <td>{{ $exp->branch?->name ?? 'Pusat' }}</td>
                            <td>{{ $exp->category?->name }}</td>
                            <td>{{ optional($exp->expense_date)->format('d/m/Y') }}</td>
                            <td class="text-end fw-bold text-danger">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                            <td>{{ Str::limit($exp->description, 40) }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('expenses.show', $exp) }}" class="btn btn-outline-secondary">Detail</a>
                                    <form method="POST" action="{{ route('approvals.approve', ['type' => 'expense', 'id' => $exp->id]) }}" class="d-inline" onsubmit="return confirm('Setujui pengeluaran ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-success">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('approvals.reject', ['type' => 'expense', 'id' => $exp->id]) }}" class="d-inline" onsubmit="return confirm('Tolak pengeluaran ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-danger">Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada pengeluaran pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB TRANSFER STOK --}}
            <div class="tab-pane fade" id="tab-transfer" role="tabpanel">
                <table class="table table-striped table-hover align-middle datatable-table">
                    <thead>
                        <tr>
                            <th>No. Transfer</th>
                            <th>Dari Gudang</th>
                            <th>Ke Gudang</th>
                            <th>Tanggal</th>
                            <th>Pemohon</th>
                            <th>Item</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockTransfers as $trf)
                        <tr>
                            <td class="fw-bold">{{ $trf->transfer_number }}</td>
                            <td>{{ $trf->fromWarehouse?->name }}</td>
                            <td>{{ $trf->toWarehouse?->name }}</td>
                            <td>{{ optional($trf->transfer_date)->format('d/m/Y') }}</td>
                            <td>{{ $trf->requester?->name }}</td>
                            <td><span class="badge bg-secondary">{{ $trf->items->count() }} item</span></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('stock-transfers.show', $trf) }}" class="btn btn-outline-secondary">Detail</a>
                                    <form method="POST" action="{{ route('approvals.approve', ['type' => 'stock_transfer', 'id' => $trf->id]) }}" class="d-inline" onsubmit="return confirm('Setujui mutasi transfer stok ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-success">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('approvals.reject', ['type' => 'stock_transfer', 'id' => $trf->id]) }}" class="d-inline" onsubmit="return confirm('Tolak mutasi transfer stok ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-danger">Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada transfer stok pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB OPNAME --}}
            <div class="tab-pane fade" id="tab-opname" role="tabpanel">
                <table class="table table-striped table-hover align-middle datatable-table">
                    <thead>
                        <tr>
                            <th>No. Opname</th>
                            <th>Gudang</th>
                            <th>Tanggal</th>
                            <th>Dibuat Oleh</th>
                            <th>Catatan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockOpnames as $opn)
                        <tr>
                            <td class="fw-bold">{{ $opn->opname_number }}</td>
                            <td>{{ $opn->warehouse?->name }}</td>
                            <td>{{ optional($opn->opname_date)->format('d/m/Y') }}</td>
                            <td>{{ $opn->creator?->name }}</td>
                            <td>{{ $opn->notes ?? '-' }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('stock-opnames.show', $opn) }}" class="btn btn-outline-secondary">Detail</a>
                                    <form method="POST" action="{{ route('approvals.approve', ['type' => 'stock_opname', 'id' => $opn->id]) }}" class="d-inline" onsubmit="return confirm('Setujui hasil stock opname ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-success">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('approvals.reject', ['type' => 'stock_opname', 'id' => $opn->id]) }}" class="d-inline" onsubmit="return confirm('Tolak hasil stock opname ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-danger">Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada stock opname pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB PAYROLL --}}
            <div class="tab-pane fade" id="tab-payroll" role="tabpanel">
                <table class="table table-striped table-hover align-middle datatable-table">
                    <thead>
                        <tr>
                            <th>No. Payroll</th>
                            <th>Karyawan</th>
                            <th>Cabang</th>
                            <th>Periode</th>
                            <th class="text-end">Gaji Bersih</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $pay)
                        <tr>
                            <td class="fw-bold">{{ $pay->payroll_number }}</td>
                            <td>{{ $pay->employee?->full_name }}</td>
                            <td>{{ $pay->employee?->branch?->name }}</td>
                            <td>{{ $pay->period?->name }}</td>
                            <td class="text-end fw-bold text-success">Rp {{ number_format($pay->net_salary, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('payrolls.show', $pay) }}" class="btn btn-outline-secondary">Detail</a>
                                    <form method="POST" action="{{ route('approvals.approve', ['type' => 'payroll', 'id' => $pay->id]) }}" class="d-inline" onsubmit="return confirm('Setujui payroll ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-success">Setujui</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada payroll pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB LEMBUR --}}
            <div class="tab-pane fade" id="tab-overtime" role="tabpanel">
                <table class="table table-striped table-hover align-middle datatable-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Cabang</th>
                            <th>Tanggal</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Total Jam</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overtimes as $ot)
                        <tr>
                            <td>{{ $ot->employee?->full_name }}</td>
                            <td>{{ $ot->employee?->branch?->name }}</td>
                            <td>{{ optional($ot->overtime_date)->format('d/m/Y') }}</td>
                            <td>{{ $ot->start_time }}</td>
                            <td>{{ $ot->end_time }}</td>
                            <td><span class="badge bg-secondary">{{ number_format($ot->hours, 1) }} jam</span></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" action="{{ route('approvals.approve', ['type' => 'overtime', 'id' => $ot->id]) }}" class="d-inline" onsubmit="return confirm('Setujui lembur karyawan ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-success">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('approvals.reject', ['type' => 'overtime', 'id' => $ot->id]) }}" class="d-inline" onsubmit="return confirm('Tolak lembur karyawan ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-danger">Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada pengajuan lembur pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB MUTASI ASET --}}
            <div class="tab-pane fade" id="tab-asset" role="tabpanel">
                <table class="table table-striped table-hover align-middle datatable-table">
                    <thead>
                        <tr>
                            <th>No. Mutasi</th>
                            <th>Aset</th>
                            <th>Dari Cabang</th>
                            <th>Ke Cabang</th>
                            <th>Tanggal</th>
                            <th>Pemohon</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assetMovements as $am)
                        <tr>
                            <td class="fw-bold">#{{ $am->id }}</td>
                            <td>{{ $am->asset?->name }}</td>
                            <td>{{ $am->fromBranch?->name ?? 'Gudang Pusat' }}</td>
                            <td>{{ $am->toBranch?->name }}</td>
                            <td>{{ optional($am->movement_date)->format('d/m/Y') }}</td>
                            <td>{{ $am->movedBy?->name }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" action="{{ route('approvals.approve', ['type' => 'asset_movement', 'id' => $am->id]) }}" class="d-inline" onsubmit="return confirm('Setujui mutasi aset ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-success">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('approvals.reject', ['type' => 'asset_movement', 'id' => $am->id]) }}" class="d-inline" onsubmit="return confirm('Tolak mutasi aset ini?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-danger">Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada mutasi aset pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

