# PANDUAN OPERASIONAL SISTEM ERP DIAN MUSTIKA
## Dian Mustika Spa, Salon & Beauty Treatment Management System
**Versi:** 1.0 &bull; **Status:** Production & Demonstration Ready &bull; **Platform:** Laravel 11 / PHP 8.3 / MySQL

---

## 1. PENDAHULUAN & ARSITEKTUR SISTEM

Sistem **ERP Dian Mustika** adalah solusi Enterprise Resource Planning modular berbasis web yang dirancang khusus untuk memenuhi standar operasional multi-cabang klinik kecantikan, spa, salon, dan pusat relaksasi Dian Mustika.

Sistem mengintegrasikan seluruh lini bisnis dalam satu database terpadu:
```text
  [ BOOKING & JADWAL ] ──────► [ POS KASIR ] ──────► [ TUTUP KASIR ]
           │                          │                      │
           ▼                          ▼                      ▼
   [ TERAPIS ASSIGN ]        [ PENGURANGAN STOK ]    [ REKONSILIASI KAS ]
           │                          │                      │
           ▼                          ▼                      ▼
  [ KOMISI OTOMATIS ]        [ KARTU STOK REALTIME]  [ MUTASI & ARUS KAS ]
           │                                                 │
           ▼                                                 ▼
   [ PAYROLL HRD ] ◄──────────────────────────────── [ KEUANGAN & BANK ]
           │
           ▼
  [ SLIP GAJI & ESS ]
```

### Keunggulan Sistem:
1. **Multi-Cabang & Multi-Gudang Terisolasi**: Pengaturan otorisasi per cabang dengan konsolidasi otomatis di level Kantor Pusat (Owner).
2. **Katalog Terpadu**: Mendukung penjualan Produk Retail, Jasa Layanan Treatment (dengan penugasan Terapis), dan Paket Treatment.
3. **Atomic Inventory Ledger**: Setiap pergerakan stok barang tercatat pada kartu stok dengan mutasi formal (`SALE`, `PURCHASE_RECEIPT`, `TRANSFER_IN`, `TRANSFER_OUT`, `OPNAME_IN`, `OPNAME_OUT`).
4. **Perhitungan Komisi Terapis Otomatis**: Layanan yang diselesaikan di POS langsung menghasilkan komisi terapis dan tersinkronisasi ke modul Payroll periode berjalan.
5. **Touchscreen POS**: Antarmuka kasir cepat dan ramah tablet/layar sentuh lengkap dengan tombol nominal tunai cepat dan kalkulasi kembalian instan.
6. **Dokumen Cetak Formal**: Cetak PO Supplier, Surat Jalan Transfer Stok, Struk Kasir, Berita Acara Tutup Kasir, Slip Gaji Karyawan, dan Laporan Eksekutif.

---

## 2. KREDENSIAL PENGGUNA & HAK AKSES DEMO

Seluruh akun demo di bawah ini telah dikonfigurasi dan aktif.  
**Kata Sandi (Default untuk semua akun):** `password`

| Peran (Role) | Email Login | Hak Akses Utama |
| :--- | :--- | :--- |
| **Owner / Direktur** | `owner@dianmustika.test` | Akses penuh seluruh cabang, dashboard eksekutif, laporan konsolidasi, approval tingkat tinggi, konfigurasi sistem. |
| **Administrator** | `admin@dianmustika.test` | Akses penuh manajemen data master, SDM, keuangan, inventori, pembelian, audit log, dan persetujuan. |
| **Branch Manager** | `manager.cabang@dianmustika.test` | Mengelola operasional cabang tertentu, persetujuan lembur & transfer cabang, monitoring penjualan harian. |
| **Kasir (Cashier)** | `kasir@dianmustika.test` | Transaksi POS, reservasi booking, pencatatan pembayaran, cetak struk kasir, dan penutupan kasir harian (`/sales/closing`). |
| **Terapis / Karyawan (ESS)** | `terapis@dianmustika.test` | Portal Layanan Mandiri Karyawan (`/portal`): Melihat profil, jadwal shift, riwayat absensi, dan unduh slip gaji pribadi. |
| **Finance & Accounting** | `finance@dianmustika.test` | Kas operasional, mutasi rekening bank, rekonsiliasi pembayaran, pembayaran tagihan, pencatatan pengeluaran. |
| **HRD & Payroll Staff** | `hrd@dianmustika.test` | Data karyawan, absensi, lembur, konfigurasi gaji, pembuatan payroll berkala, dan penerbitan slip gaji. |

---

## 3. PANDUAN MODUL POS KASIR (TOUCH & TABLET FRIENDLY)

Antarmuka Kasir dirancang dengan tata letak split-screen responsif untuk kecepatan layanan di kasir:

### Langkah Operasional Kasir:
1. Masuk ke menu **Sales & POS** &rarr; **Point of Sale (POS)** (`/sales/create`).
2. **Pilih Cabang & Gudang Asal**:
   - Sistem secara otomatis memilih cabang tempat kasir bertugas dan gudang retail aktif.
3. **Pilih / Daftarkan Pelanggan**:
   - Pilih nama pelanggan terdaftar pada dropdown pencarian, atau biarkan kosong untuk *Walk-in Guest*.
4. **Pilih Item Belanja**:
   - Gunakan tombol filter kategori di sisi atas (**Semua**, **Produk Retail**, **Layanan Spa**, **Paket Treatment**).
   - Gunakan kotak pencarian instan nama atau SKU produk/layanan.
   - Klik kartu item atau tombol **+ Tambah** untuk memasukkan ke keranjang kasir.
5. **Penugasan Terapis (Khusus Layanan Treatment)**:
   - Pada baris layanan spa/treatment di keranjang, pilih nama **Terapis Bertugas**. Sistem otomatis mencatat hak komisi terapis terkait saat transaksi disimpan.
6. **Diskon & Penyesuaian**:
   - Masukkan nominal diskon transaksi jika pelanggan memiliki voucher promosi.
7. **Pilih Metode Pembayaran & Pembayaran Cepat**:
   - Pilih metode bayar: **Tunai**, **Transfer Bank**, **QRIS**, atau **Kartu Debit/Kredit**.
   - Untuk pembayaran Tunai, gunakan tombol shortcut nominal cepat:
     - `[Uang Pas]`
     - `[Rp 50.000]`
     - `[Rp 100.000]`
     - `[Rp 200.000]`
     - `[Rp 500.000]`
   - Kotak **Kembalian** akan menghitung sisa kembalian uang secara real-time.
8. **Selesaikan Transaksi & Cetak Struk**:
   - Klik tombol hijau **Simpan & Selesaikan Transaksi**.
   - Sistem akan memproses pengurangan stok produk di gudang secara atomic.
   - Klik **Cetak Struk Kasir** (`/sales/{id}/print`) untuk mencetak struk termal.

---

## 4. PANDUAN MODUL RESERVASI & JADWAL TREATMENT

### Langkah Alur Booking ke Kasir:
1. Buka menu **Treatment & Booking** &rarr; **Daftar Booking** (`/bookings`).
2. Klik **Tambah Booking**:
   - Masukkan nama customer, layanan yang dipilih, terapis yang diinginkan, tanggal dan jam treatment.
   - Klik **Simpan Reservasi**.
3. **Saat Pelanggan Datang (Check-in)**:
   - Buka jadwal treatment harian (`/bookings/schedule`).
   - Ubah status reservasi menjadi `CHECKED_IN` atau `IN_TREATMENT`.
4. **Saat Treatment Selesai & Pembayaran Kasir**:
   - Buka detail booking terkait (`/bookings/{id}`).
   - Klik tombol **"Proses ke Kasir / Buat Tagihan"**.
   - Sistem akan langsung membuka layar POS kasir dengan pelanggan, cabang, dan layanan treatment yang sudah terisi otomatis tanpa perlu kasir menginput ulang.
   - Kasir dapat menambahkan produk retail perawatan rumah yang dibeli pelanggan, lalu memproses pembayaran hingga selesai.
   - Status booking otomatis diperbarui menjadi `COMPLETED`.

---

## 5. PANDUAN TUTUP KASIR HARIAN & BERITA ACARA KAS

Setiap akhir shift kasir, kasir wajib melakukan proses penutupan kas harian untuk pertanggungjawaban fisik uang tunai.

### Langkah Tutup Kasir:
1. Buka menu **Sales & POS** &rarr; **Tutup Kasir Harian** (`/sales/closing`).
2. Sistem menyajikan:
   - Total Omzet Penjualan Hari Ini.
   - Jumlah Transaksi / Invoice Selesai.
   - Total Item Terjual.
   - Rekapitulasi Rincian Metode Penerimaan (Tunai vs Non-Tunai).
   - Tabel Daftar Invoice Transaksi Harian.
3. Klik tombol **"Cetak Berita Acara Tutup Kasir"** (`/sales/closing/print`).
4. Dokumen resmi Berita Acara memuat:
   - Ringkasan total penerimaan sistem.
   - Lembar hitung fisik uang di laci kas (*Cash Drawer physical count*).
   - Perhitungan selisih kas fisik vs catatan sistem (+/-).
   - Kolom tanda tangan resmi Kasir Bertugas dan Supervisor / Branch Manager.

---

## 6. PUSAT PERSETUJUAN (PENDING APPROVALS CENTER)

Seluruh pengajuan transaksi yang memerlukan otorisasi manajemen dikumpulkan pada satu layar terpadu di menu **System** &rarr; **Pusat Persetujuan** (`/approvals`).

Kategori yang didukung:
1. **Purchase Order (PO)**: Persetujuan pesanan barang ke supplier sebelum dikirim.
2. **Pengeluaran Kas (Expense)**: Persetujuan biaya operasional kas kecil.
3. **Transfer Stok Antar Gudang**: Verifikasi perpindahan fisik barang.
4. **Stock Opname**: Persetujuan penyesuaian selisih stok fisik vs sistem.
5. **Pengajuan Lembur (Overtime)**: Validasi jam lembur sebelum masuk perhitungan gaji.
6. **Mutasi Aset**: Pemindahan peralatan dan inventaris aset tetap.
7. **Payroll Karyawan**: Pengesahan final daftar gaji bulanan sebelum penerbitan slip.

Setiap item menyediakan tombol **Setujui (Approve)** dan **Tolak (Reject)** disertai catatan alasan yang tercatat permanen pada Audit Trail.

---

## 7. PANDUAN INVENTORI & KARTU STOK REAL-TIME

1. **Memantau Stok Gudang**:
   - Buka menu **Inventory** &rarr; **Stok Gudang** (`/inventory/stock`).
   - Terdapat indikator peringatan stok menipis jika kuantitas berada di bawah ambang batas minimum.
2. **Melihat Kartu Stok**:
   - Klik tautan **"Kartu Stok"** di baris produk yang diinginkan untuk langsung membuka riwayat mutasi produk tersebut (`/inventory/movements?product_id={id}`).
   - Riwayat mutasi dilengkapi penanda warna:
     - <span style="color: green; font-weight: bold;">[+] Hijau</span> untuk penambahan barang (Pembelian, Transfer Masuk, Opname Plus).
     - <span style="color: red; font-weight: bold;">[-] Merah</span> untuk pengurangan barang (Penjualan Kasir, Transfer Keluar, Opname Minus).
3. **Transfer Stok Antar Cabang / Gudang**:
   - Buka **Inventory** &rarr; **Transfer Stok** (`/stock-transfers`).
   - Buat pengajuan transfer dari gudang asal ke gudang tujuan.
   - Setelah disetujui, cetak **Surat Jalan Pengiriman Resmi** (`/stock-transfers/{id}/print`) yang memuat rincian barang, nama driver, nomor kendaraan, dan kolom tanda tangan pengirim serta penerima.

---

## 8. PANDUAN PEMBELIAN (PURCHASING)

1. **Surat Pesanan / Purchase Order (PO)**:
   - Buka menu **Purchasing** &rarr; **Purchase Order** (`/purchase-orders`).
   - Buat pesanan baru ke supplier rekanan.
   - Setelah diapprove oleh Manager/Owner, klik **"Cetak Surat Pesanan (PO)"** (`/purchase-orders/{id}/print`). Surat pesanan resmi memuat data rekening bank supplier, alamat cabang pengiriman, dan kolom otorisasi pembelian.
2. **Penerimaan Barang (Goods Receipt)**:
   - Buka menu **Purchasing** &rarr; **Penerimaan Barang** (`/goods-receipts`).
   - Pilih nomor PO terkait dan catat kuantitas aktual barang yang diterima.
   - Sistem secara otomatis menambah kuantitas stok di gudang tujuan dan mencatat kartu stok `PURCHASE_RECEIPT`.

---

## 9. PANDUAN SDM & PAYROLL TERINTEGRASI

### Alur Payroll Bulanan:
1. **Pencatatan Absensi & Lembur**:
   - Catat shift dan kehadiran staf pada menu **Employee** &rarr; **Absensi** (`/attendances`).
   - Setujui pengajuan lembur di menu **Lembur** (`/overtimes`).
2. **Kompilasi Komisi Terapis**:
   - Seluruh layanan treatment yang diselesaikan oleh terapis di kasir POS otomatis tercatat di menu **Komisi** (`/commissions`).
3. **Pembuatan Periode Payroll**:
   - Buka menu **Payroll** &rarr; **Periode Payroll** (`/payroll-periods`).
   - Buat periode baru (misalnya periode bulan berjalan) dan klik **Generate Payroll**.
   - Sistem secara otomatis menghitung:
     $$\text{Gaji Bersih} = \text{Gaji Pokok} + \text{Tunjangan} + \text{Upah Lembur} + \text{Komisi Treatment} - \text{Potongan BPJS/Kasbon}$$
4. **Penyesuaian Manual (Adjustment)**:
   - Jika diperlukan bonus khusus atau koreksi, admin HR dapat menambahkan *Payroll Adjustment* sebelum status disetujui.
5. **Approval & Penerbitan Slip Gaji**:
   - Setujui payroll pada layar persetujuan.
   - Terbitkan slip gaji (`/payslips`).
   - Klik **"Cetak Slip Gaji"** (`/payslips/{id}/print`) untuk mencetak dokumen slip gaji resmi berlogo Dian Mustika lengkap dengan kode verifikasi integritas.

---

## 10. PORTAL LAYANAN MANDIRI KARYAWAN (EMPLOYEE SELF-SERVICE)

Setiap staf/terapis dapat masuk menggunakan akun masing-masing untuk mengakses portal pribadi:
- **Profil Karyawan** (`/portal/profile`): Memeriksa data pribadi, penempatan, nomor rekening, dan status kepegawaian.
- **Absensi Saya** (`/portal/attendance`): Melihat jadwal shift dan rekapitulasi kehadiran harian.
- **Slip Gaji Saya** (`/portal/payslips`): Melihat rincian gaji, komisi per treatment, dan mengunduh slip gaji resmi pribadi. Sistem secara ketat mengisolasi data sehingga staf tidak dapat melihat slip gaji staf lain.

---

## 11. MODUL KEUANGAN & MUTASI BANK

1. **Kas & Arus Kas**:
   - Pantau saldo kas operasional di menu **Finance** &rarr; **Kas & Bank** (`/finance`).
2. **Pencatatan Pengeluaran (Expense)**:
   - Catat biaya operasional (listrik, internet, konsumsi, perlengkapan salon) pada menu **Pengeluaran** (`/expenses`).
3. **Mutasi Bank & Rekonsiliasi**:
   - Input atau sinkronkan mutasi rekening bank pada menu **Mutasi Bank** (`/bank-mutations`).
   - Buka **Rekonsiliasi Bank** (`/bank-reconciliations`) untuk mencocokkan mutasi debit/kredit bank dengan transaksi pembayaran POS atau tagihan vendor.
4. **Pengelolaan Tagihan Berkala (Bills)**:
   - Konfigurasikan template tagihan berulang (sewa gedung tahunan, internet bulanan, iuran kebersihan) pada menu **Tagihan** (`/bills`).

---

## 12. LAPORAN EKSEKUTIF & EKSPOR DATA

Akses menu **Reports** (`/reports`) untuk mengakses 18 modul laporan analitis terpadu:

### Daftar Laporan yang Didukung:
1. **Penjualan Harian** (`sales_daily`)
2. **Penjualan per Cabang** (`sales_by_branch`)
3. **Penjualan per Kasir** (`sales_by_cashier`)
4. **Penjualan per Metode Bayar** (`payment_method`)
5. **Penjualan Produk Retail** (`product_sales`)
6. **Penjualan Jasa Layanan** (`service_sales`)
7. **Stok Saat Ini** (`inventory_stock`)
8. **Stok Menipis (Peringatan Reorder)** (`low_stock`)
9. **Arus Kas Masuk & Keluar** (`finance_cashflow`)
10. **Pengeluaran per Kategori** (`expense_by_category`)
11. **Tagihan Belum Lunas** (`bills_outstanding`)
12. **Jumlah & Demografi Karyawan** (`employee_count`)
13. **Rekapitulasi Kehadiran** (`attendance_summary`)
14. **Rekapitulasi Lembur** (`overtime_summary`)
15. **Rekapitulasi Payroll & Gaji** (`payroll_summary`)
16. **Rekapitulasi Komisi Terapis** (`commission_summary`)
17. **Daftar Pelanggan Teratas** (`top_customers`)
18. **Daftar Pelanggan Baru** (`new_customers`)

### Opsi Output Laporan:
- **Cetak / Simpan PDF**: Menampilkan layout laporan eksekutif formal berlogo Dian Mustika siap cetak atau simpan ke PDF.
- **Export CSV**: Mengunduh data mentah ke format CSV/Excel untuk pengolahan data lebih lanjut.

---

## 13. AUDIT LOG & KEAMANAN

Setiap perubahan penting dalam sistem (perubahan harga produk, transaksi penjualan, pembatalan invoice, persetujuan payroll, penerimaan barang, modifikasi hak akses) tercatat secara otomatis pada menu **System** &rarr; **Audit Log** (`/audit-logs`).

Audit log merekam:
- Identitas pengguna yang melakukan aksi.
- Alamat IP dan waktu transaksi.
- Entity dan ID data yang diubah.
- Rekam jejak data sebelum (*old values*) dan sesudah (*new values*).

---

*Dokumen ini merupakan panduan operasional resmi untuk seluruh staf, kasir, terapis, manajemen cabang, dan direksi Dian Mustika.*
