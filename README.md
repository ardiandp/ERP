# ERP DIAN MUSTIKA
### Spa, Wellness, Beauty Treatment & Salon Enterprise Management System

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tests](https://img.shields.io/badge/Tests-66%20Passed%20(385%20assertions)-brightgreen?style=for-the-badge)]()
[![Code Style](https://img.shields.io/badge/Pint-PSR--12%20Clean-success?style=for-the-badge)]()

**ERP Dian Mustika** adalah sistem informasi manajemen enterprise berbasis web modular untuk mengintegrasikan seluruh operasional klinik kecantikan, spa, salon, dan pusat relaksasi Dian Mustika secara multi-cabang.

---

## Fitur Utama

- **POS Kasir Touchscreen & Ramah Tablet**:
  - Split-screen layout: panel katalog di kiri dan keranjang belanja di kanan.
  - Filter kategori cepat (Produk Retail, Layanan Spa, Paket Treatment) dan pencarian instan.
  - Penugasan terapis per layanan dengan kalkulasi komisi otomatis.
  - Shortcut nominal tunai cepat (`[Uang Pas]`, `[50rb]`, `[100rb]`, `[200rb]`, `[500rb]`).
  - Perhitungan kembalian otomatis real-time.
  - Cetak Struk Kasir format termal.
- **Booking & Jadwal Treatment**:
  - Manajemen reservasi online/offline dan visual jadwal treatment terapis harian.
  - Tombol satu klik **"Proses ke Kasir / Buat Tagihan"** untuk konversi langsung reservasi menjadi penjualan POS tanpa input manual ulang.
- **Tutup Kasir Harian & Berita Acara Rekonsiliasi**:
  - Rekapitulasi omzet harian dan rincian metode penerimaan (Tunai vs Non-Tunai).
  - Cetak dokumen resmi **Berita Acara Tutup Kasir** dengan lembar rekonsiliasi kas fisik laci kasir (*cash drawer*).
- **Pusat Persetujuan Terpadu (Pending Approvals Center)**:
  - Layanan persetujuan 7 kategori: Purchase Order, Pengeluaran (Expense), Transfer Stok, Stock Opname, Lembur (Overtime), Mutasi Aset, dan Payroll.
- **Inventori & Kartu Stok Multi-Gudang**:
  - Pemantauan stok real-time per gudang dan per cabang dengan peringatan stok menipis.
  - Navigasi cepat **Kartu Stok** dengan kode warna mutasi (Masuk / Keluar).
  - Cetak **Surat Jalan Pengiriman Resmi (Delivery Order)** untuk transfer stok antar gudang/cabang.
- **Pembelian (Purchasing)**:
  - Siklus PO lengkap: Draft &rarr; Submitted &rarr; Approved &rarr; Received.
  - Cetak **Surat Pesanan PO resmi** dengan data rekening bank supplier dan tanda tangan otorisasi.
  - Penerimaan barang (Goods Receipt) dengan pembaruan otomatis kartu stok.
- **SDM & Payroll Terintegrasi**:
  - Manajemen data karyawan, keluarga, BPJS, penempatan cabang, absensi, dan jam lembur.
  - **Komisi Terapis Otomatis**: Layanan treatment yang diselesaikan di POS otomatis dikreditkan sebagai komisi terapis dan ditarik langsung ke payroll periode terkait.
  - Penyesuaian manual (bonus & potongan kasbon).
  - Cetak **Slip Gaji resmi berlogo Dian Mustika** dengan kode verifikasi unik.
- **Portal Layanan Mandiri Karyawan (Employee Self-Service / ESS)**:
  - Profil staf, riwayat absensi, serta akses dan cetak slip gaji pribadi (terisolasi secara aman).
- **Keuangan, Kas Operasional & Rekonsiliasi Bank**:
  - Kas kecil operasional cabang, pencatatan mutasi rekening bank, dan mesin rekonsiliasi otomatis antara transaksi bank dan invoice penjualan.
  - Manajemen tagihan berkala (listrik, internet, sewa gedung, BPJS).
- **18 Modul Laporan Eksekutif & Ekspor Data**:
  - Laporan analitis penjualan, inventori, keuangan, SDM, dan pelanggan.
  - Fitur **Cetak Laporan Format PDF** dan **Export CSV/Excel**.
- **Audit Trail & Keamanan**:
  - Log audit otomatis untuk setiap perubahan harga, pergerakan stok, payroll, dan transaksi finansial.

---

## Akun Demo & Kredensial

Kata sandi default untuk seluruh akun demo: `password`

| Peran (Role) | Email Login | Cakupan Akses |
| :--- | :--- | :--- |
| **Owner / Direktur** | `owner@dianmustika.test` | Akses penuh seluruh cabang, dashboard eksekutif, persetujuan tingkat tinggi, konsolidasi laporan. |
| **Administrator** | `admin@dianmustika.test` | Manajemen master data, user & role, operasional harian, audit log, persetujuan transaksi. |
| **Branch Manager** | `manager.cabang@dianmustika.test` | Supervisi operasional cabang, persetujuan lembur & transfer cabang, monitoring kasir. |
| **Kasir** | `kasir@dianmustika.test` | Point of Sale (POS), reservasi treatment, cetak struk, penutupan kasir harian (`/sales/closing`). |
| **Terapis (ESS)** | `terapis@dianmustika.test` | Portal Layanan Mandiri (`/portal`): Profil, jadwal kehadiran, dan slip gaji pribadi. |
| **Finance & Accounting** | `finance@dianmustika.test` | Kas operasional, mutasi bank, rekonsiliasi pembayaran, pencatatan pengeluaran. |
| **HRD & Payroll** | `hrd@dianmustika.test` | Manajemen staf, absensi, pengajuan lembur, perhitungan gaji berkala, penerbitan slip gaji. |

---

## Persyaratan Sistem

- PHP >= 8.3
- Ekstensi PHP: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`
- Composer >= 2.x
- MySQL >= 8.0 (atau MariaDB 10.4+)
- Web Server: Nginx / Apache / Laragon

---

## Petunjuk Instalasi

### 1. Clone Repositori & Install Dependencies
```bash
git clone <repository-url> ERPDM
cd ERPDM
composer install
```

### 2. Konfigurasi Lingkungan (.env)
Salin konfigurasi default dan atur koneksi database MySQL:
```bash
cp .env.example .env
php artisan key:generate
```
Sesuaikan parameter database di file `.env`:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erpdm
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Migrasi & Seeder Database Lengkap
Jalankan migrasi dan seluruh seeder demo (termasuk 48+ tabel master, transaksi, mutasi, akun peran, dan relasi integritas):
```bash
php artisan migrate --seed
```

### 4. Menjalankan Aplikasi
Buka virtual host Laragon (`http://erpdm.test`) atau jalankan server development bawaan:
```bash
php artisan serve
```
Akses aplikasi melalui browser di `http://localhost:8000` (atau `http://erpdm.test`).

---

## Pengujian Otomatis (Automated Testing)

Aplikasi dilengkapi pengujian unit dan fitur komprehensif (66 test suite, 385 assertions) yang mencakup seluruh lifecycle:
```bash
php artisan test
```

Uji spesifik lifecycle end-to-end (Booking &rarr; POS Kasir &rarr; Stok Berkurang &rarr; Komisi Terapis &rarr; Tutup Kasir &rarr; Payroll &rarr; Slip Gaji):
```bash
php artisan test --filter=EndToEndLifecycleTest
```

Pemeriksaan gaya kode (Laravel Pint):
```bash
vendor/bin/pint --dirty --format agent
```

---

## Dokumentasi Tambahan

- **[Panduan Operasional Pengguna (User Manual)](docs/USER_MANUAL.md)**: Panduan detail langkah demi langkah penggunaan seluruh modul sistem dalam Bahasa Indonesia.
- **[PRD Database Architecture](PRD/01_PRD_ERP_Dian_Mustika_Database_Architecture.md)**: Spesifikasi dan rancangan skema 48+ tabel database.
- **[PRD Development & Workflow](PRD/02_PRD_ERP_Dian_Mustika_Development.md)**: Spesifikasi arsitektur teknis, aturan bisnis, dan kriteria penerimaan.

---

&copy; 2026 Dian Mustika Spa & Wellness. Hak cipta dilindungi undang-undang.
