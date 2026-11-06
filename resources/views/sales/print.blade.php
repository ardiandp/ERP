<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $transaction->invoice_number }}</title>
    <link href="{{ asset('theme/matrix-admin/dist/css/style.min.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            color: #222;
            font-family: 'Courier New', Courier, monospace, 'Segoe UI', sans-serif;
        }
        .receipt-container {
            max-width: 420px;
            margin: 25px auto;
            background: #fff;
            padding: 25px 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 4px;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 15px;
        }
        .brand-name {
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .brand-desc {
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }
        .dotted-divider {
            border-top: 1px dashed #777;
            margin: 10px 0;
        }
        .double-divider {
            border-top: 2px double #333;
            margin: 12px 0;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 3px;
        }
        .item-row {
            font-size: 12px;
            margin-bottom: 6px;
        }
        .item-main {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
        }
        .item-sub {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 11px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .grand-total {
            font-size: 15px;
            font-weight: 700;
            margin-top: 5px;
        }
        .receipt-footer {
            text-align: center;
            font-size: 11px;
            color: #555;
            margin-top: 15px;
        }
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .receipt-container {
                box-shadow: none !important;
                padding: 5px !important;
                margin: 0 auto !important;
                max-width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print bg-dark text-white p-2 text-center mb-3">
        <button onclick="window.print()" class="btn btn-sm btn-success me-2">
            <i class="mdi mdi-printer"></i> Cetak Struk (Thermal / PDF)
        </button>
        <a href="{{ route('sales.show', $transaction) }}" class="btn btn-sm btn-outline-light">
            Kembali ke Detail Penjualan
        </a>
    </div>

    <div class="receipt-container">
        {{-- STORE HEADER --}}
        <div class="receipt-header">
            <div class="brand-name">DIAN MUSTIKA</div>
            <div class="brand-desc">
                Klinik Perawatan &amp; Salon Muslimah<br>
                Cabang: {{ $transaction->branch?->name ?? 'Pusat' }}<br>
                {{ $transaction->branch?->address ?? '' }}
                @if($transaction->branch?->phone)
                <br>Telp: {{ $transaction->branch->phone }}
                @endif
            </div>
        </div>

        <div class="dotted-divider"></div>

        {{-- METADATA --}}
        <div class="meta-row">
            <span>No. Faktur:</span>
            <strong>{{ $transaction->invoice_number }}</strong>
        </div>
        <div class="meta-row">
            <span>Waktu:</span>
            <span>{{ optional($transaction->transaction_date)->format('d/m/Y H:i') }}</span>
        </div>
        <div class="meta-row">
            <span>Pelanggan:</span>
            <span>{{ $transaction->customer?->name ?? 'Pelanggan Umum' }}</span>
        </div>
        <div class="meta-row">
            <span>Kasir / Petugas:</span>
            <span>{{ auth()->user()?->name ?? 'Staff' }}</span>
        </div>

        <div class="dotted-divider"></div>

        {{-- ITEMS --}}
        <div class="items-list">
            @foreach($transaction->items as $item)
            <div class="item-row">
                <div class="item-main">
                    <span>{{ $item->item_name }}</span>
                    <span>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="item-sub">
                    <span>{{ number_format((float) $item->quantity, 0) }} x {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                        @if((float) $item->discount > 0)
                        (Disc: {{ number_format((float) $item->discount, 0, ',', '.') }})
                        @endif
                    </span>
                    @if($item->employee)
                    <span>Terapis: {{ $item->employee->full_name }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="dotted-divider"></div>

        {{-- TOTALS --}}
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format((float) $transaction->subtotal, 0, ',', '.') }}</span>
        </div>
        @if((float) $transaction->discount > 0)
        <div class="total-row">
            <span>Diskon Nota:</span>
            <span>- Rp {{ number_format((float) $transaction->discount, 0, ',', '.') }}</span>
        </div>
        @endif
        @if((float) $transaction->tax > 0)
        <div class="total-row">
            <span>PPN:</span>
            <span>Rp {{ number_format((float) $transaction->tax, 0, ',', '.') }}</span>
        </div>
        @endif

        <div class="double-divider"></div>

        <div class="total-row grand-total">
            <span>TOTAL:</span>
            <span>Rp {{ number_format((float) $transaction->grand_total, 0, ',', '.') }}</span>
        </div>

        <div class="dotted-divider"></div>

        {{-- PAYMENT BREAKDOWN --}}
        @php
            $totalPaid = $transaction->payments->sum('amount');
            $change = max(0, $totalPaid - $transaction->grand_total);
        @endphp
        @forelse($transaction->payments as $payment)
        <div class="total-row">
            <span>Bayar ({{ $payment->paymentMethod?->name ?? 'TUNAI' }}):</span>
            <span>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</span>
        </div>
        @empty
        <div class="total-row text-danger">
            <span>Status:</span>
            <span>BELUM LUNAS</span>
        </div>
        @endforelse

        @if($totalPaid > 0)
        <div class="total-row fw-bold mt-1">
            <span>Kembalian:</span>
            <span>Rp {{ number_format((float) $change, 0, ',', '.') }}</span>
        </div>
        @endif

        <div class="dotted-divider"></div>

        {{-- FOOTER --}}
        <div class="receipt-footer">
            Terima kasih atas kunjungan Anda.<br>
            Barang atau jasa yang telah dibeli tidak dapat dikembalikan.<br>
            <em>Cantik Alami Bersama Dian Mustika</em>
        </div>
    </div>
</body>
</html>

