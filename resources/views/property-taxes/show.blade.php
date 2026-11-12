@extends('layouts.app')
@section('title', 'Detail PBB - ' . $tax->nop)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0 text-dark fw-bold"><i class="mdi mdi-home-map-marker text-primary"></i> Detail Pajak Bumi &amp; Bangunan (PBB)</h4>
                <small class="text-muted">NOP: <strong class="font-monospace text-primary">{{ $tax->nop }}</strong> &bull; Tahun Pajak: <strong>{{ $tax->tahun }}</strong></small>
            </div>
            <div class="d-flex gap-1">
                <a href="{{ route('property-taxes.print', $tax) }}" target="_blank" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="mdi mdi-printer"></i> Cetak Dokumen
                </a>
                <a href="{{ route('property-taxes.edit', $tax) }}" class="btn btn-outline-primary btn-sm shadow-sm">
                    <i class="mdi mdi-pencil"></i> Ubah
                </a>
                <a href="{{ route('property-taxes.index') }}" class="btn btn-outline-dark btn-sm shadow-sm">
                    <i class="mdi mdi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Status & Financial Summary Banner -->
        <div class="card border shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="row align-items-center text-center text-md-start">
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Status Pembayaran</small>
                        <div class="mt-1">
                            @if($tax->status_bayar === 'LUNAS')
                            <span class="badge bg-success fs-6"><i class="mdi mdi-check-circle"></i> LUNAS</span>
                            @elseif($tax->status_bayar === 'KURANG_BAYAR')
                            <span class="badge bg-warning text-dark fs-6"><i class="mdi mdi-alert"></i> KURANG BAYAR</span>
                            @else
                            <span class="badge bg-danger fs-6"><i class="mdi mdi-close-circle"></i> BELUM LUNAS</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Pajak Terutang (SPPT)</small>
                        <h4 class="mb-0 fw-bold text-dark mt-1">Rp {{ number_format($tax->pajak ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">SPPT Yang Sudah Dibayar</small>
                        <h4 class="mb-0 fw-bold text-success mt-1">Rp {{ number_format($tax->sppt_yg_dibayar ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Sisa Tunggakan</small>
                        <h4 class="mb-0 fw-bold {{ $tax->sisa_pajak > 0 ? 'text-danger' : 'text-muted' }} mt-1">Rp {{ number_format($tax->sisa_pajak, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Left Column: Objek Pajak -->
            <div class="col-md-7">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-light fw-bold text-dark border-bottom py-2">
                        <i class="mdi mdi-home-city text-primary"></i> 1. Informasi Objek Pajak (OP)
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th style="width: 35%;" class="text-muted small">Nomor Objek Pajak (NOP):</th>
                                <td class="fw-bold font-monospace text-primary fs-6">{{ $tax->nop }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted small">Tahun Pajak:</th>
                                <td><span class="badge bg-secondary font-monospace">{{ $tax->tahun }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted small">Alamat Objek Pajak:</th>
                                <td class="fw-semibold text-dark">{{ $tax->alamat_op }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted small">Kelurahan / Desa:</th>
                                <td>{{ $tax->desa ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted small">Kecamatan:</th>
                                <td>{{ $tax->kecamatan ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted small">Kota / Kabupaten:</th>
                                <td>{{ $tax->kota_kab ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted small">Luas Bumi / Tanah:</th>
                                <td><strong>{{ $tax->luas_bumi ? number_format($tax->luas_bumi, 2, ',', '.') : '-' }}</strong> m²</td>
                            </tr>
                            <tr>
                                <th class="text-muted small">Luas Bangunan:</th>
                                <td><strong>{{ $tax->luas_bangunan ? number_format($tax->luas_bangunan, 2, ',', '.') : '-' }}</strong> m²</td>
                            </tr>
                            <tr>
                                <th class="text-muted small">Cabang Dian Mustika:</th>
                                <td>
                                    @if($tax->branch)
                                    <span class="badge bg-light text-dark border"><i class="mdi mdi-source-branch"></i> {{ $tax->branch->name }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted small">Aset Terdaftar:</th>
                                <td>
                                    @if($tax->asset)
                                    <span class="badge bg-light text-dark border"><i class="mdi mdi-desktop-classic"></i> {{ $tax->asset->code }} &bull; {{ $tax->asset->name }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Wajib Pajak & Payment Details -->
            <div class="col-md-5">
                <div class="card border shadow-sm mb-3">
                    <div class="card-header bg-light fw-bold text-dark border-bottom py-2">
                        <i class="mdi mdi-account-circle text-primary"></i> 2. Informasi Wajib Pajak (WP)
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th style="width: 40%;" class="text-muted small">Nama WP:</th>
                                <td class="fw-bold text-dark">{{ $tax->nama_wp }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted small">NPWP:</th>
                                <td class="font-monospace">{{ $tax->npwp ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card border shadow-sm mb-3">
                    <div class="card-header bg-light fw-bold text-dark border-bottom py-2">
                        <i class="mdi mdi-receipt text-primary"></i> 3. Pembayaran &amp; Bukti Setor
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th style="width: 45%;" class="text-muted small">Tanggal Pembayaran:</th>
                                <td>
                                    @if($tax->tgl_bayar)
                                    <span class="fw-semibold text-dark">{{ $tax->tgl_bayar->translatedFormat('d F Y') }}</span>
                                    @else
                                    <span class="text-danger small">Belum dibayarkan</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted small">Bukti Bayar / STTS:</th>
                                <td>
                                    @if($tax->bukti_bayar)
                                    <a href="{{ asset('storage/' . $tax->bukti_bayar) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="mdi mdi-download"></i> Unduh Berkas
                                    </a>
                                    @else
                                    <span class="text-muted small">Tidak ada lampiran</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted small">Catatan / Keterangan:</th>
                                <td>{{ $tax->keterangan ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card border shadow-sm">
                    <div class="card-header bg-light fw-bold text-dark border-bottom py-2">
                        <i class="mdi mdi-history text-primary"></i> 4. Informasi Audit Sistem
                    </div>
                    <div class="card-body small">
                        <div class="text-muted">Dicatat oleh: <strong>{{ $tax->creator?->name ?? 'Sistem' }}</strong></div>
                        <div class="text-muted">Tanggal Input: {{ $tax->created_at ? $tax->created_at->format('d/m/Y H:i') : '-' }}</div>
                        <div class="text-muted">Terakhir Diperbarui: {{ $tax->updated_at ? $tax->updated_at->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <form action="{{ route('property-taxes.destroy', $tax) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data PBB NOP {{ $tax->nop }}?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="mdi mdi-delete"></i> Hapus Rekam PBB Ini
                </button>
            </form>
            <a href="{{ route('property-taxes.index') }}" class="btn btn-secondary btn-sm">
                <i class="mdi mdi-arrow-left"></i> Kembali ke Daftar PBB
            </a>
        </div>
    </div>
</div>
@endsection

