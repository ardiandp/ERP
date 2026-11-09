<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Order {{ $order->po_number }} - Dian Mustika ERP</title>
    <link href="{{ asset('theme/matrix-admin/dist/css/style.min.css') }}" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-size: 13px; color: #212529; }
        .po-container { max-width: 820px; margin: 20px auto; background: #fff; padding: 36px 40px; box-shadow: 0 0 10px rgba(0,0,0,0.08); border-radius: 4px; }
        .company-header h3 { margin: 0; font-weight: 700; color: #27a9e3; }
        .table-items th { background-color: #27a9e3 !important; color: #fff !important; font-weight: 600; text-align: center; font-size: 12px; }
        .table-items td { vertical-align: middle; }
        .sign-box { border-top: 1px dashed #6c757d; margin-top: 55px; padding-top: 6px; font-weight: 600; font-size: 12px; }
        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .po-container { box-shadow: none; margin: 0; width: 100%; max-width: 100%; padding: 15px 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print py-2 bg-dark text-white mb-2 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 820px;">
        <span class="small"><i class="mdi mdi-printer"></i> Pratinjau Dokumen Purchase Order: <strong>{{ $order->po_number }}</strong></span>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-info me-2 text-white"><i class="mdi mdi-printer"></i> Cetak PO</button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light">Tutup</button>
        </div>
    </div>
</div>

<div class="po-container">
    {{-- KOP SURAT --}}
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div class="company-header">
            <h3>DIAN MUSTIKA</h3>
            <p class="mb-0 text-muted small fw-bold">SPA &bull; WELLNESS &bull; BEAUTY TREATMENT</p>
            <small class="text-muted d-block">{{ $order->branch?->company?->name ?? 'PT Dian Mustika Pratama' }}</small>
            <small class="text-muted d-block">{{ $order->branch?->address ?? 'Jl. Merdeka No. 1, Jakarta Pusat' }}</small>
            <small class="text-muted">Telp: {{ $order->branch?->phone ?? '021-1234567' }} | Email: purchasing@dianmustika.test</small>
        </div>
        <div class="text-end">
            <h4 class="text-uppercase fw-bold text-dark mb-1">SURAT PESANAN</h4>
            <div class="text-muted small">PURCHASE ORDER</div>
            <div class="mt-2 text-primary fw-bold fs-5">{{ $order->po_number }}</div>
            <span class="badge {{ $order->status === 'APPROVED' ? 'bg-success' : 'bg-warning text-dark' }}">
                {{ $order->status }}
            </span>
        </div>
    </div>

    {{-- INFORMASI SUPPLIER & ORDER --}}
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="p-2 border rounded bg-light">
                <strong class="d-block text-uppercase text-muted small mb-1">Pemasok / Supplier:</strong>
                <div class="fw-bold fs-6">{{ $order->supplier?->name }}</div>
                <div class="small text-muted">{{ $order->supplier?->address ?? '-' }}</div>
                <div class="small text-muted">Telp: {{ $order->supplier?->phone ?? '-' }} | Email: {{ $order->supplier?->email ?? '-' }}</div>
                @if($order->supplier?->bankAccounts && $order->supplier->bankAccounts->isNotEmpty())
                    @php($acc = $order->supplier->bankAccounts->first())
                    <div class="small text-muted mt-1 pt-1 border-top border-secondary-subtle">
                        Rek: <strong>{{ $acc->bank?->code }} - {{ $acc->account_number }}</strong> (a.n {{ $acc->account_name }})
                    </div>
                @endif
            </div>
        </div>
        <div class="col-6">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <th class="text-muted small" style="width: 45%;">Tanggal Order:</th>
                    <td class="fw-bold">{{ optional($order->order_date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th class="text-muted small">Perkiraan Tiba:</th>
                    <td>{{ optional($order->expected_date)->format('d/m/Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <th class="text-muted small">Gudang / Cabang:</th>
                    <td>{{ $order->branch?->name }}</td>
                </tr>
                <tr>
                    <th class="text-muted small">Termin Pembayaran:</th>
                    <td>{{ $order->supplier?->payment_terms_days ? $order->supplier->payment_terms_days . ' Hari' : 'Cash / Segera' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- TABEL ITEM BARANG --}}
    <table class="table table-bordered table-sm table-items mb-3">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Nama Produk / Barang</th>
                <th style="width: 12%;">Qty</th>
                <th style="width: 18%;">Harga Satuan</th>
                <th style="width: 20%;">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $item->product?->name }}</strong>
                    <small class="text-muted d-block">SKU: {{ $item->product?->sku }}</small>
                </td>
                <td class="text-center">
                    {{ (float) $item->quantity }} {{ $item->product?->unit?->name ?? 'PCS' }}
                </td>
                <td class="text-end">
                    Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                </td>
                <td class="text-end fw-bold">
                    Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end bg-light">Subtotal:</th>
                <td class="text-end bg-light fw-bold">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if((float) $order->discount > 0)
            <tr>
                <th colspan="4" class="text-end bg-light">Diskon:</th>
                <td class="text-end bg-light text-danger">- Rp {{ number_format((float) $order->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if((float) $order->tax > 0)
            <tr>
                <th colspan="4" class="text-end bg-light">Pajak (PPN):</th>
                <td class="text-end bg-light">Rp {{ number_format((float) $order->tax, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="table-primary">
                <th colspan="4" class="text-end fs-6">TOTAL PEMESANAN:</th>
                <td class="text-end fs-6 fw-bold">Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($order->notes)
    <div class="p-2 border rounded bg-light small mb-4">
        <strong>Catatan Pemesanan:</strong> {{ $order->notes }}
    </div>
    @endif

    {{-- TANDA TANGAN --}}
    <div class="row text-center mt-4">
        <div class="col-4">
            <div class="text-muted small">Dipesan Oleh:</div>
            <div class="sign-box">{{ $order->creator?->name ?? 'Staff Purchasing' }}</div>
            <small class="text-muted">Purchasing Staff</small>
        </div>
        <div class="col-4">
            <div class="text-muted small">Disetujui Oleh:</div>
            <div class="sign-box">{{ $order->approver?->name ?? 'Manager / Direktur' }}</div>
            <small class="text-muted">
                {{ $order->approved_at ? 'Disetujui: ' . $order->approved_at->format('d/m/Y') : 'Pending Approval' }}
            </small>
        </div>
        <div class="col-4">
            <div class="text-muted small">Konfirmasi Supplier:</div>
            <div class="sign-box">{{ $order->supplier?->contact_person ?? $order->supplier?->name }}</div>
            <small class="text-muted">Cap &amp; Tanda Tangan</small>
        </div>
    </div>
</div>

</body>
</html>
