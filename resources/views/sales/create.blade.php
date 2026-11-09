@extends('layouts.app')
@section('title', 'POS Kasir - Dian Mustika')
@section('content')

{{-- TOP BAR --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold"><i class="mdi mdi-point-of-sale text-success me-1"></i> Point of Sale (POS Kasir)</h4>
        <small class="text-muted">Layanan Treatment &bull; Paket Spa &bull; Produk Retail</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.closing') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-cash-register"></i> Rekap Tutup Kasir
        </a>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-history"></i> Riwayat Transaksi
        </a>
    </div>
</div>

@if(isset($booking) && $booking)
<div class="alert alert-info py-2 mb-3 d-flex justify-content-between align-items-center">
    <div>
        <i class="mdi mdi-information-outline font-18 align-middle me-1"></i>
        Mengonversi Reservasi <strong>#{{ $booking->booking_number }}</strong> (Pelanggan: <strong>{{ $booking->customer?->name }}</strong>) ke Transaksi Penjualan Kasir.
    </div>
    <span class="badge bg-primary fs-6">{{ $booking->status }}</span>
</div>
@endif

<form method="POST" action="{{ route('sales.store') }}" id="pos-form">
    @csrf
    @if(isset($booking) && $booking)
    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
    @endif

    <div class="row g-3">
        {{-- KOLOM KIRI: KATALOG CEPAT (TOUCH TILES) --}}
        <div class="col-lg-7 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white pb-2 pt-3">
                    {{-- SEARCH & FILTER BAR --}}
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-magnify"></i></span>
                                <input type="text" id="catalog-search" class="form-control border-start-0" placeholder="Cari layanan, paket, atau produk...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <button type="button" class="btn btn-outline-primary active catalog-filter" data-filter="ALL">Semua</button>
                                <button type="button" class="btn btn-outline-primary catalog-filter" data-filter="SERVICE"><i class="mdi mdi-spa me-1"></i>Layanan</button>
                                <button type="button" class="btn btn-outline-primary catalog-filter" data-filter="PACKAGE"><i class="mdi mdi-gift me-1"></i>Paket</button>
                                <button type="button" class="btn btn-outline-primary catalog-filter" data-filter="PRODUCT"><i class="mdi mdi-package-variant-closed me-1"></i>Produk</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TILES GRID CONTAINER --}}
                <div class="card-body p-3" style="max-height: 640px; overflow-y: auto;">
                    <div class="row g-2" id="catalog-grid">
                        @foreach($catalog as $key => $entry)
                        <div class="col-sm-6 col-md-4 col-xl-4 catalog-item" data-type="{{ $entry['type'] }}" data-search="{{ strtolower($entry['name'] . ' ' . $entry['code'] . ' ' . $entry['category']) }}">
                            <div class="card h-100 border p-2 tile-card shadow-sm" style="cursor: pointer; transition: all 0.15s ease-in-out;"
                                 onclick="addItemToCart('{{ $key }}', '{{ addslashes($entry['name']) }}', {{ (float) $entry['price'] }}, '{{ $entry['type'] }}', '{{ $entry['category'] }}')">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="badge {{ $entry['type'] === 'SERVICE' ? 'bg-info' : ($entry['type'] === 'PACKAGE' ? 'bg-primary' : 'bg-success') }} small">
                                        {{ $entry['type'] === 'SERVICE' ? 'Layanan' : ($entry['type'] === 'PACKAGE' ? 'Paket' : 'Produk') }}
                                    </span>
                                    <small class="text-muted fw-bold">{{ $entry['code'] }}</small>
                                </div>
                                <div class="fw-bold text-dark text-truncate mb-1" title="{{ $entry['name'] }}" style="font-size: 13px;">
                                    {{ $entry['name'] }}
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-1">
                                    <span class="text-success fw-bold" style="font-size: 13px;">
                                        Rp {{ number_format((float) $entry['price'], 0, ',', '.') }}
                                    </span>
                                    <button type="button" class="btn btn-xs btn-outline-success rounded-circle" style="width: 26px; height: 26px; padding: 0;">
                                        <i class="mdi mdi-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div id="catalog-empty" class="text-center py-5 text-muted d-none">
                        <i class="mdi mdi-package-variant-remove fs-1 d-block mb-2"></i>
                        Tidak ada item yang cocok dengan pencarian.
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: RINCIAN TAGIHAN / KERANJANG KASIR --}}
        <div class="col-lg-5 col-xl-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white pb-2 pt-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Cabang:</label>
                            <select name="branch_id" class="form-select form-select-sm" required>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) old('branch_id', optional($booking ?? null)->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Pelanggan:</label>
                            <select name="customer_id" class="form-select form-select-sm">
                                <option value="">Pelanggan Umum (Walk-in)</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected((string) old('customer_id', optional($booking ?? null)->customer_id) === (string) $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Gudang Stok:</label>
                            <select name="warehouse_id" class="form-select form-select-sm">
                                <option value="">Default Cabang</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Tanggal Transaksi:</label>
                            <input type="date" name="transaction_date" class="form-control form-control-sm" value="{{ old('transaction_date', now()->toDateString()) }}">
                        </div>
                    </div>
                </div>

                {{-- DAFTAR ITEM KERANJANG --}}
                <div class="card-body p-2" style="max-height: 280px; overflow-y: auto;">
                    <div id="cart-empty" class="text-center py-4 text-muted">
                        <i class="mdi mdi-cart-outline fs-1 d-block mb-1"></i>
                        Keranjang masih kosong.<br>Klik item dari katalog di sebelah kiri.
                    </div>
                    <div id="cart-items-container">
                        {{-- ROWS POPULATED VIA JAVASCRIPT --}}
                    </div>
                </div>

                {{-- SUMMARY & TOTAL --}}
                <div class="card-footer bg-light p-3 border-top">
                    <div class="row g-2 mb-2 small">
                        <div class="col-6 text-muted">Subtotal:</div>
                        <div class="col-6 text-end fw-bold" id="label-subtotal">Rp 0</div>

                        <div class="col-6 text-muted">Diskon Tambahan (Rp):</div>
                        <div class="col-6">
                            <input type="number" step="any" name="discount" id="input-discount" class="form-control form-control-sm text-end py-0" value="{{ old('discount', 0) }}">
                        </div>

                        <div class="col-6 text-muted">Pajak (PPN):</div>
                        <div class="col-6">
                            <input type="number" step="any" name="tax" id="input-tax" class="form-control form-control-sm text-end py-0" value="{{ old('tax', 0) }}">
                        </div>
                    </div>

                    {{-- GRAND TOTAL HIGHLIGHT --}}
                    <div class="p-2 rounded bg-dark text-white d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-bold">TOTAL BAYAR:</span>
                        <span class="fs-4 fw-bold text-success" id="label-grand-total">Rp 0</span>
                    </div>

                    {{-- METODE BAYAR & NOMINAL --}}
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted mb-1">Metode Bayar:</label>
                            <select name="payment_method_id" id="payment-method-select" class="form-select form-select-sm" required>
                                @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" data-type="{{ $method->method_type }}" @selected((string) old('payment_method_id') === (string) $method->id)>
                                    {{ $method->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted mb-1">Jumlah Diterima (Rp):</label>
                            <input type="number" step="any" name="paid_amount" id="input-paid-amount" class="form-control form-control-sm text-end fw-bold" value="{{ old('paid_amount', 0) }}">
                        </div>
                    </div>

                    {{-- TOMBOL CEPAT PECAHAN UANG TUNAI --}}
                    <div class="mb-2" id="quick-cash-container">
                        <small class="text-muted d-block mb-1" style="font-size: 11px;">Pilihan Cepat Nominal Tunai:</small>
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-xs btn-outline-secondary quick-cash-btn" data-val="EXACT">Uang Pas</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary quick-cash-btn" data-val="50000">50rb</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary quick-cash-btn" data-val="100000">100rb</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary quick-cash-btn" data-val="200000">200rb</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary quick-cash-btn" data-val="500000">500rb</button>
                        </div>
                    </div>

                    {{-- KEMBALIAN DISPLAY --}}
                    <div class="p-2 rounded mb-3 d-flex justify-content-between align-items-center" id="change-box" style="background-color: #e8f5e9;">
                        <span class="small fw-bold text-muted">Kembalian:</span>
                        <span class="fs-5 fw-bold text-success" id="label-change">Rp 0</span>
                    </div>

                    <div class="mb-3">
                        <textarea name="notes" rows="1" class="form-control form-control-sm" placeholder="Catatan transaksi (opsional)...">{{ old('notes', isset($booking) && $booking ? 'Tagihan reservasi booking #' . $booking->booking_number : '') }}</textarea>
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger py-1 small mb-2">{{ $errors->first() }}</div>
                    @endif

                    <button type="submit" id="btn-submit-pos" class="btn btn-success btn-lg w-100 fw-bold shadow-sm" disabled>
                        <i class="mdi mdi-check-circle-outline me-1"></i> SIMPAN &amp; PROSES TRANSAKSI
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
(function () {
    const catalogSearch = document.getElementById('catalog-search');
    const catalogGrid = document.getElementById('catalog-grid');
    const catalogEmpty = document.getElementById('catalog-empty');
    const filterButtons = document.querySelectorAll('.catalog-filter');
    const cartContainer = document.getElementById('cart-items-container');
    const cartEmpty = document.getElementById('cart-empty');
    const btnSubmit = document.getElementById('btn-submit-pos');

    const inputDiscount = document.getElementById('input-discount');
    const inputTax = document.getElementById('input-tax');
    const inputPaid = document.getElementById('input-paid-amount');
    const paymentMethodSelect = document.getElementById('payment-method-select');

    const labelSubtotal = document.getElementById('label-subtotal');
    const labelGrandTotal = document.getElementById('label-grand-total');
    const labelChange = document.getElementById('label-change');
    const changeBox = document.getElementById('change-box');

    const employees = @json($employees);
    const initialBookingItems = @json($bookingItems ?? []);

    let cart = []; // Array of { id, ref, name, price, qty, discount, employee_id, type, category }
    let currentTotal = 0;

    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toLocaleString('id-ID');
    }

    // 1. FILTER & SEARCH KATALOG
    function filterCatalog() {
        const query = catalogSearch.value.toLowerCase().trim();
        const activeFilter = document.querySelector('.catalog-filter.active').dataset.filter;
        let visibleCount = 0;

        document.querySelectorAll('.catalog-item').forEach(function (item) {
            const type = item.dataset.type;
            const searchStr = item.dataset.search;

            const matchType = (activeFilter === 'ALL' || type === activeFilter);
            const matchSearch = (!query || searchStr.includes(query));

            if (matchType && matchSearch) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        if (visibleCount === 0) {
            catalogEmpty.classList.remove('d-none');
        } else {
            catalogEmpty.classList.add('d-none');
        }
    }

    catalogSearch.addEventListener('input', filterCatalog);

    filterButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterCatalog();
        });
    });

    // 2. TAMBAH ITEM KE KERANJANG
    window.addItemToCart = function (ref, name, price, type, category, employeeId) {
        const existingIndex = cart.findIndex(item => item.ref === ref);

        if (existingIndex > -1) {
            cart[existingIndex].qty += 1;
        } else {
            cart.push({
                ref: ref,
                name: name,
                price: parseFloat(price) || 0,
                qty: 1,
                discount: 0,
                employee_id: employeeId || '',
                type: type,
                category: category || ''
            });
        }

        renderCart();
    };

    // 3. RENDER KERANJANG
    function renderCart() {
        if (cart.length === 0) {
            cartContainer.innerHTML = '';
            cartEmpty.classList.remove('d-none');
            btnSubmit.disabled = true;
            updateTotals();
            return;
        }

        cartEmpty.classList.add('d-none');
        btnSubmit.disabled = false;

        let html = '';
        cart.forEach(function (item, index) {
            const lineSubtotal = Math.max(0, (item.qty * item.price) - item.discount);

            let employeeOptions = '<option value="">- Pilih Terapis -</option>';
            employees.forEach(function (emp) {
                const selected = (emp.id == item.employee_id) ? 'selected' : '';
                employeeOptions += `<option value="${emp.id}" ${selected}>${emp.full_name}</option>`;
            });

            html += `
            <div class="card border mb-2 p-2 shadow-sm cart-item-card">
                <input type="hidden" name="items[${index}][ref]" value="${item.ref}">
                <input type="hidden" name="items[${index}][unit_price]" value="${item.price}">
                <input type="hidden" name="items[${index}][discount]" value="${item.discount}">

                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div>
                        <strong class="d-block text-dark" style="font-size: 13px;">${item.name}</strong>
                        <small class="text-muted">${formatRupiah(item.price)}</small>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeFromCart(${index})" title="Hapus">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>

                ${item.type === 'SERVICE' || item.type === 'PACKAGE' ? `
                <div class="mb-1">
                    <select name="items[${index}][employee_id]" class="form-select form-select-sm" onchange="updateItemEmployee(${index}, this.value)">
                        ${employeeOptions}
                    </select>
                </div>
                ` : `<input type="hidden" name="items[${index}][employee_id]" value="">`}

                <div class="d-flex justify-content-between align-items-center mt-1 pt-1 border-top border-light">
                    <div class="input-group input-group-sm" style="width: 110px;">
                        <button type="button" class="btn btn-outline-secondary" onclick="updateItemQty(${index}, -1)">-</button>
                        <input type="number" name="items[${index}][quantity]" class="form-control text-center p-0" min="0.01" step="any" value="${item.qty}" onchange="setItemQty(${index}, this.value)">
                        <button type="button" class="btn btn-outline-secondary" onclick="updateItemQty(${index}, 1)">+</button>
                    </div>
                    <span class="fw-bold text-success" style="font-size: 13px;">${formatRupiah(lineSubtotal)}</span>
                </div>
            </div>
            `;
        });

        cartContainer.innerHTML = html;
        updateTotals();
    }

    window.removeFromCart = function (index) {
        cart.splice(index, 1);
        renderCart();
    };

    window.updateItemQty = function (index, delta) {
        if (cart[index]) {
            cart[index].qty = Math.max(1, cart[index].qty + delta);
            renderCart();
        }
    };

    window.setItemQty = function (index, val) {
        if (cart[index]) {
            cart[index].qty = Math.max(0.01, parseFloat(val) || 1);
            renderCart();
        }
    };

    window.updateItemEmployee = function (index, empId) {
        if (cart[index]) {
            cart[index].employee_id = empId;
        }
    };

    // 4. HITUNG TOTAL & KEMBALIAN
    function updateTotals() {
        let subtotal = 0;
        cart.forEach(function (item) {
            subtotal += Math.max(0, (item.qty * item.price) - item.discount);
        });

        const discount = parseFloat(inputDiscount.value) || 0;
        const tax = parseFloat(inputTax.value) || 0;
        currentTotal = Math.max(0, subtotal - discount + tax);

        labelSubtotal.textContent = formatRupiah(subtotal);
        labelGrandTotal.textContent = formatRupiah(currentTotal);

        updateChange();
    }

    function updateChange() {
        const paid = parseFloat(inputPaid.value) || 0;
        const change = paid - currentTotal;

        if (paid <= 0) {
            labelChange.textContent = 'Rp 0';
            changeBox.style.backgroundColor = '#f8f9fa';
            labelChange.className = 'fs-5 fw-bold text-muted';
        } else if (change >= 0) {
            labelChange.textContent = formatRupiah(change);
            changeBox.style.backgroundColor = '#e8f5e9';
            labelChange.className = 'fs-5 fw-bold text-success';
        } else {
            labelChange.textContent = 'Kurang ' + formatRupiah(Math.abs(change));
            changeBox.style.backgroundColor = '#ffebee';
            labelChange.className = 'fs-5 fw-bold text-danger';
        }
    }

    inputDiscount.addEventListener('input', updateTotals);
    inputTax.addEventListener('input', updateTotals);
    inputPaid.addEventListener('input', updateChange);

    // QUICK CASH SHORTCUTS
    document.querySelectorAll('.quick-cash-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const val = this.dataset.val;
            if (val === 'EXACT') {
                inputPaid.value = currentTotal;
            } else {
                inputPaid.value = parseFloat(val);
            }
            updateChange();
        });
    });

    // Otomatis isi uang pas jika metode transfer/qris dipilih
    paymentMethodSelect.addEventListener('change', function () {
        const type = this.selectedOptions[0]?.dataset.type;
        if (type && type !== 'CASH') {
            inputPaid.value = currentTotal;
            updateChange();
        }
    });

    // 5. INISIALISASI ITEM DARI BOOKING (JIKA ADA)
    if (initialBookingItems && initialBookingItems.length > 0) {
        initialBookingItems.forEach(function (bItem) {
            // Find item in catalog to extract metadata
            const catEntry = @json($catalog)[bItem.ref];
            if (catEntry) {
                window.addItemToCart(bItem.ref, catEntry.name, bItem.price, catEntry.type, catEntry.category, bItem.employee_id);
            }
        });
    }

})();
</script>
<style>
    .tile-card:hover { border-color: #28a745 !important; transform: translateY(-2px); }
    .cart-item-card { border-radius: 6px; }
    .btn-xs { padding: 1px 6px; font-size: 11px; }
</style>
@endpush
