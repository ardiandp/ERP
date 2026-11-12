@extends('layouts.app')
@section('title', 'Tambah Data PBB Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0 text-dark fw-bold"><i class="mdi mdi-plus-circle text-primary"></i> Tambah Data Pajak Bumi &amp; Bangunan (PBB)</h4>
                <small class="text-muted">Masukkan rincian SPPT PBB objek pajak baru</small>
            </div>
            <a href="{{ route('property-taxes.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading fw-bold mb-1"><i class="mdi mdi-alert-circle"></i> Terjadi Kesalahan Input:</h6>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <form action="{{ route('property-taxes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card border shadow-sm mb-3">
                <div class="card-header bg-light fw-bold text-dark border-bottom py-2">
                    <i class="mdi mdi-home-map-marker text-primary"></i> 1. Informasi Objek Pajak (OP)
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nop" class="form-label fw-bold small">Nomor Objek Pajak (NOP) <span class="text-danger">*</span></label>
                            <input type="text" name="nop" id="nop" class="form-control @error('nop') is-invalid @enderror" placeholder="Contoh: 32.73.010.001.001-0001.0" value="{{ old('nop') }}" required>
                            @error('nop')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Format standar 18 digit nomor objek pajak.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="tahun" class="form-label fw-bold small">Tahun Pajak <span class="text-danger">*</span></label>
                            <input type="number" name="tahun" id="tahun" class="form-control @error('tahun') is-invalid @enderror" min="1990" max="2100" value="{{ old('tahun', date('Y')) }}" required>
                            @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="alamat_op" class="form-label fw-bold small">Alamat Objek Pajak <span class="text-danger">*</span></label>
                            <input type="text" name="alamat_op" id="alamat_op" class="form-control @error('alamat_op') is-invalid @enderror" placeholder="Nama jalan, nomor bangunan, RT/RW" value="{{ old('alamat_op') }}" required>
                            @error('alamat_op')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="desa" class="form-label fw-bold small">Kelurahan / Desa</label>
                            <input type="text" name="desa" id="desa" class="form-control @error('desa') is-invalid @enderror" placeholder="Nama Kelurahan / Desa" value="{{ old('desa') }}">
                            @error('desa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="kecamatan" class="form-label fw-bold small">Kecamatan</label>
                            <input type="text" name="kecamatan" id="kecamatan" class="form-control @error('kecamatan') is-invalid @enderror" placeholder="Nama Kecamatan" value="{{ old('kecamatan') }}">
                            @error('kecamatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="kota_kab" class="form-label fw-bold small">Kota / Kabupaten</label>
                            <input type="text" name="kota_kab" id="kota_kab" class="form-control @error('kota_kab') is-invalid @enderror" placeholder="Contoh: Kota Jakarta Selatan, Kota Bogor" value="{{ old('kota_kab') }}">
                            @error('kota_kab')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="luas_bumi" class="form-label fw-bold small">Luas Bumi / Tanah (m²)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="luas_bumi" id="luas_bumi" class="form-control @error('luas_bumi') is-invalid @enderror" placeholder="0.00" value="{{ old('luas_bumi') }}">
                                <span class="input-group-text">m²</span>
                            </div>
                            @error('luas_bumi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="luas_bangunan" class="form-label fw-bold small">Luas Bangunan (m²)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="luas_bangunan" id="luas_bangunan" class="form-control @error('luas_bangunan') is-invalid @enderror" placeholder="0.00" value="{{ old('luas_bangunan') }}">
                                <span class="input-group-text">m²</span>
                            </div>
                            @error('luas_bangunan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="branch_id" class="form-label fw-bold small">Relasi Cabang Dian Mustika (Opsional)</label>
                            <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                <option value="">-- Bukan Aset Cabang Khusus / Non-Cabang --</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih cabang operasional jika properti ini digunakan oleh cabang.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="asset_id" class="form-label fw-bold small">Relasi Aset Tetap Terdaftar (Opsional)</label>
                            <select name="asset_id" id="asset_id" class="form-select @error('asset_id') is-invalid @enderror">
                                <option value="">-- Tidak Terhubung ke Master Aset Tertentu --</option>
                                @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" @selected(old('asset_id') == $asset->id)>{{ $asset->code }} &bull; {{ $asset->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih jika aset bangunan/tanah terdaftar di menu Asset Management.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border shadow-sm mb-3">
                <div class="card-header bg-light fw-bold text-dark border-bottom py-2">
                    <i class="mdi mdi-account-card-details text-primary"></i> 2. Informasi Wajib Pajak (WP)
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nama_wp" class="form-label fw-bold small">Nama Wajib Pajak (WP) <span class="text-danger">*</span></label>
                            <input type="text" name="nama_wp" id="nama_wp" class="form-control @error('nama_wp') is-invalid @enderror" placeholder="Nama perorangan atau badan usaha di SPPT" value="{{ old('nama_wp') }}" required>
                            @error('nama_wp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="npwp" class="form-label fw-bold small">Nomor Pokok Wajib Pajak (NPWP)</label>
                            <input type="text" name="npwp" id="npwp" class="form-control @error('npwp') is-invalid @enderror" placeholder="Contoh: 01.234.567.8-000.000" value="{{ old('npwp') }}">
                            @error('npwp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-light fw-bold text-dark border-bottom py-2">
                    <i class="mdi mdi-cash-register text-primary"></i> 3. Ketetapan Pajak &amp; Realisasi Pembayaran
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="pajak" class="form-label fw-bold small">Pajak Terutang (SPPT) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" name="pajak" id="pajak" class="form-control @error('pajak') is-invalid @enderror" placeholder="0" value="{{ old('pajak') }}" required oninput="calcStatus()">
                            </div>
                            @error('pajak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Total nominal ketetapan PBB terutang tahun ini.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="sppt_yg_dibayar" class="form-label fw-bold small">SPPT Yang Sudah Dibayar</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" name="sppt_yg_dibayar" id="sppt_yg_dibayar" class="form-control @error('sppt_yg_dibayar') is-invalid @enderror" placeholder="0" value="{{ old('sppt_yg_dibayar', 0) }}" oninput="calcStatus()">
                            </div>
                            @error('sppt_yg_dibayar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Nominal yang telah disetor ke bank / kas daerah.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="tgl_bayar" class="form-label fw-bold small">Tanggal Pembayaran</label>
                            <input type="date" name="tgl_bayar" id="tgl_bayar" class="form-control @error('tgl_bayar') is-invalid @enderror" value="{{ old('tgl_bayar') }}" onchange="calcStatus()">
                            @error('tgl_bayar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Tanggal transaksi pembayaran pada slip/STTS.</small>
                        </div>

                        <!-- Status Preview Box -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="small text-muted d-block">Status Pembayaran Otomatis:</span>
                                    <span id="badgeStatusPreview" class="badge bg-secondary fs-6">BELUM LUNAS</span>
                                </div>
                                <div class="text-end">
                                    <span class="small text-muted d-block">Sisa Tagihan / Tunggakan:</span>
                                    <h5 id="textSisaTagihan" class="fw-bold text-danger mb-0">Rp 0</h5>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="bukti_bayar" class="form-label fw-bold small">Unggah Bukti Bayar / STTS (Opsional)</label>
                            <input type="file" name="bukti_bayar" id="bukti_bayar" class="form-control @error('bukti_bayar') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                            @error('bukti_bayar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Format: JPG, PNG, atau PDF (Maks. 5 MB).</small>
                        </div>

                        <div class="col-md-6">
                            <label for="keterangan" class="form-label fw-bold small">Keterangan / Catatan</label>
                            <input type="text" name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Catatan tambahan terkait objek atau pembayaran" value="{{ old('keterangan') }}">
                            @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('property-taxes.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary px-4"><i class="mdi mdi-content-save"></i> Simpan Data PBB</button>
            </div>
        </form>
    </div>
</div>

<script>
function calcStatus() {
    const pajak = parseFloat(document.getElementById('pajak').value) || 0;
    const bayar = parseFloat(document.getElementById('sppt_yg_dibayar').value) || 0;
    const tgl = document.getElementById('tgl_bayar').value;
    const badge = document.getElementById('badgeStatusPreview');
    const sisa = document.getElementById('textSisaTagihan');

    const sisaVal = Math.max(0, pajak - bayar);
    sisa.innerText = 'Rp ' + sisaVal.toLocaleString('id-ID');

    if (bayar >= pajak && pajak > 0) {
        badge.className = 'badge bg-success fs-6';
        badge.innerHTML = '<i class="mdi mdi-check-circle"></i> LUNAS';
    } else if (bayar > 0 && bayar < pajak) {
        badge.className = 'badge bg-warning text-dark fs-6';
        badge.innerHTML = '<i class="mdi mdi-alert"></i> KURANG BAYAR';
    } else {
        badge.className = 'badge bg-danger fs-6';
        badge.innerHTML = '<i class="mdi mdi-close-circle"></i> BELUM LUNAS';
    }
}
document.addEventListener('DOMContentLoaded', calcStatus);
</script>
@endsection

