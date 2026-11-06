@extends('layouts.app')
@section('title', 'Pengaturan Sistem (Settings)')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Pengaturan Sistem (Settings)</h4>
        <span class="text-muted small">Konfigurasi parameter global aplikasi ERP Dian Mustika</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            <ul class="nav nav-tabs customtab mb-3" id="settingTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#tab-general" role="tab">
                        <i class="mdi mdi-domain me-1"></i> Profil &amp; Umum
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pos-tab" data-bs-toggle="tab" href="#tab-pos" role="tab">
                        <i class="mdi mdi-point-of-sale me-1"></i> POS &amp; Kasir
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="inventory-tab" data-bs-toggle="tab" href="#tab-inventory" role="tab">
                        <i class="mdi mdi-package-variant me-1"></i> Inventori
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="attendance-tab" data-bs-toggle="tab" href="#tab-attendance" role="tab">
                        <i class="mdi mdi-calendar-clock me-1"></i> Kehadiran &amp; HR
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="settingTabsContent">
                {{-- TAB GENERAL --}}
                <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Aplikasi</label>
                            <input type="text" name="app_name" class="form-control" value="{{ old('app_name', $settings['app_name']) }}" required>
                            <div class="form-text">Nama sistem yang tampil pada header dan title.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Legal Perusahaan</label>
                            <input type="text" name="company_legal_name" class="form-control" value="{{ old('company_legal_name', $settings['company_legal_name']) }}">
                            <div class="form-text">Nama badan usaha resmi (PT/CV).</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Telepon / Hotline</label>
                            <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $settings['company_phone']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Perusahaan</label>
                            <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $settings['company_email']) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Kantor Pusat</label>
                            <textarea name="company_address" class="form-control" rows="2">{{ old('company_address', $settings['company_address']) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- TAB POS --}}
                <div class="tab-pane fade" id="tab-pos" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Header Struk Pembayaran</label>
                            <input type="text" name="pos_receipt_header" class="form-control" value="{{ old('pos_receipt_header', $settings['pos_receipt_header']) }}">
                            <div class="form-text">Teks slogan atau nama toko di baris atas struk kasir.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tarif Pajak Bawaan (PPN %)</label>
                            <div class="input-group">
                                <input type="number" step="0.1" name="pos_default_tax" class="form-control" value="{{ old('pos_default_tax', $settings['pos_default_tax']) }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Persentase PPN default untuk transaksi POS kasir.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pesan Footer Struk Kasir</label>
                            <textarea name="pos_receipt_footer" class="form-control" rows="3">{{ old('pos_receipt_footer', $settings['pos_receipt_footer']) }}</textarea>
                            <div class="form-text">Ucapan terima kasih atau informasi garansi/kebijakan salon.</div>
                        </div>
                    </div>
                </div>

                {{-- TAB INVENTORI --}}
                <div class="tab-pane fade" id="tab-inventory" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Batas Peringatan Stok Minimum (Low Stock Threshold)</label>
                            <div class="input-group">
                                <input type="number" name="inventory_low_stock_threshold" class="form-control" value="{{ old('inventory_low_stock_threshold', $settings['inventory_low_stock_threshold']) }}">
                                <span class="input-group-text">Item / Unit</span>
                            </div>
                            <div class="form-text">Produk dengan stok di bawah nilai ini akan memicu peringatan restock pada dashboard.</div>
                        </div>
                    </div>
                </div>

                {{-- TAB ATTENDANCE --}}
                <div class="tab-pane fade" id="tab-attendance" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Jam Mulai Kerja Standar</label>
                            <input type="time" name="attendance_work_start" class="form-control" value="{{ old('attendance_work_start', $settings['attendance_work_start']) }}">
                            <div class="form-text">Jam masuk kerja standar untuk kalkulasi keterlambatan.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Toleransi Keterlambatan (Grace Period)</label>
                            <div class="input-group">
                                <input type="number" name="attendance_grace_period_minutes" class="form-control" value="{{ old('attendance_grace_period_minutes', $settings['attendance_grace_period_minutes']) }}">
                                <span class="input-group-text">Menit</span>
                            </div>
                            <div class="form-text">Toleransi keterlambatan check-in tanpa potongan kehadiran.</div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="mdi mdi-content-save me-1"></i> Simpan Perubahan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

