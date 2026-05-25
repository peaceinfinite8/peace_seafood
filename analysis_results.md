# 📊 Laporan Analisis & Hasil Eksekusi Sistem WMS Peace Seafood

Dokumen ini menyajikan rangkuman menyeluruh dari seluruh perbaikan bug, penambahan fitur logistik inti, optimalisasi lapangan, serta penataan antarmuka utama (dashboard) untuk aplikasi Peace Seafood.

---

## ⚙️ TAHAP 1: Perbaikan Bug Runtime & Database

Berikut adalah rincian perbaikan kesalahan runtime kritis untuk menjamin kestabilan sistem:

| No | Modul | File Terkait | Baris Kode | Masalah | Rincian Perbaikan & Solusi |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **1** | **Generated Column** | [ReturService.php](file:///c:/xamppp/htdocs/peace_seafood/src/services/ReturService.php) | L62 - L89 | Menulis langsung ke `sisa_hutang` yang merupakan *Stored Generated Column* memicu MySQL Error. | Menghapus manipulasi langsung pada `sisa_hutang`. Pengurangan nilai retur diubah dengan memotong kolom `nominal` sehingga database melakukan perhitungan *generated* otomatis. Menyesuaikan kolom log pembayaran sesuai tabel `hutang_piutang_history`. |
| **2** | **Enum Status & Piutang** | [PenjualanService.php](file:///c:/xamppp/htdocs/peace_seafood/src/services/PenjualanService.php) | L280 - L311 | Menggunakan status `'cancelled'` yang di luar enum tabel `nota` (`'draft','final','cancel'`) dan `hutang_piutang`. | Mengubah status pembatalan nota menjadi `'cancel'`. Saat nota dibatalkan, semua riwayat pembayaran cicilan (`hutang_piutang_history`) dan piutang (`hutang_piutang`) otomatis dihapus secara terintegrasi untuk menjaga konsistensi keuangan. |
| **3** | **Biaya Operasional** | [KeuanganService.php](file:///c:/xamppp/htdocs/peace_seafood/src/services/KeuanganService.php) | L215 - L226 | Query insert mencoba mengisi kolom `nama_biaya` dan `id_user` yang tidak ada dalam schema. | Merefaktor kueri insert agar data terpetakan dengan presisi ke kolom aslinya di DB, yaitu `kategori`, `deskripsi`, dan `created_by`. |
| **4** | **Query Relasi Penitipan** | [PenitipanService.php](file:///c:/xamppp/htdocs/peace_seafood/src/services/PenitipanService.php) | L155 - L168 | Klausul JOIN menggunakan `t.id_supplier` padahal kolom tabel `titipan` bernama `id_pengirim`. | Mengubah klausa JOIN menjadi `JOIN supplier s ON t.id_pengirim = s.id`, melakukan `LEFT JOIN` pada produk, serta memetakan kolom riwayat penjualan titipan (`qty` dan `komisi_nominal`) agar query bebas dari query error. |

---

## 📦 TAHAP 2: Fungsionalitas Logistik & WMS Baru

Tabel pendukung baru telah berhasil dimigrasikan ke MySQL, dan service serta controller baru telah diimplementasikan:

### 1. Modul Stok Opname
* **Tabel Baru (`stok_opname_detail`)**:
  ```sql
  CREATE TABLE `stok_opname_detail` (
    `id`             INT NOT NULL AUTO_INCREMENT,
    `id_stok_opname` INT NOT NULL,
    `id_produk`      INT NOT NULL,
    `qty_sistem`     DECIMAL(10,2) NOT NULL,
    `qty_fisik`      DECIMAL(10,2) NOT NULL,
    `selisih`        DECIMAL(10,2) GENERATED ALWAYS AS (`qty_fisik` - `qty_sistem`) STORED,
    PRIMARY KEY (`id`)
  );
  ```
* **Service Baru**: [StokOpnameService.php](file:///c:/xamppp/htdocs/peace_seafood/src/services/StokOpnameService.php) (logika CRUD opname draft, penghitungan selisih otomatis, dan penyesuaian stok saat finalisasi).
* **Controller Baru**: [StokOpnameController.php](file:///c:/xamppp/htdocs/peace_seafood/src/controllers/StokOpnameController.php).
* **Proteksi Multi-Gudang**: Pada kueri UPDATE untuk sinkronisasi `qty_fisik` ke tabel produk wajib dikunci dengan WHERE clause `id_gudang = ?` dari sesi opname tersebut agar tidak memengaruhi stok produk di gudang lain.

### 2. Modul Transfer Antar Gudang
* **Tabel Baru (`stok_transfer`)**:
  ```sql
  CREATE TABLE `stok_transfer` (
    `id`                INT NOT NULL AUTO_INCREMENT,
    `gudang_asal_id`    INT NOT NULL,
    `gudang_tujuan_id`  INT NOT NULL,
    `id_produk`         INT NOT NULL,
    `qty`               DECIMAL(10,2) NOT NULL,
    `status`            ENUM('pending', 'sent', 'received') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (`id`)
  );
  ```
* **Service Baru**: [StokTransferService.php](file:///c:/xamppp/htdocs/peace_seafood/src/services/StokTransferService.php) (logika pemindahan stok atomik lewat `Database::beginTransaction()`).
* **Sinkronisasi Kode/SKU**: Ketika sistem menduplikasi produk baru secara otomatis ke gudang tujuan, **semua kolom atribut produk asal (SKU, nama, deskripsi, harga, satuan)** disalin secara identik ke gudang tujuan dengan stok awal 0 sebelum stok ditambahkan.

### 3. Audit Trail (Log Aktivitas)
* **Tabel Baru (`activity_log`)**:
  ```sql
  CREATE TABLE `activity_log` (
    `id`           INT NOT NULL AUTO_INCREMENT,
    `id_user`      INT NOT NULL,
    `action`       VARCHAR(50) NOT NULL,
    `table_name`   VARCHAR(50) NOT NULL,
    `record_id`    INT NOT NULL,
    `before_value` JSON NULL,
    `after_value`  JSON NULL,
    PRIMARY KEY (`id`)
  );
  ```
* **Helper Baru**: [ActivityLogHelper.php](file:///c:/xamppp/htdocs/peace_seafood/src/utils/ActivityLogHelper.php) (secara instan merekam snapshot sebelum dan sesudah mutasi data dalam format JSON). Terintegrasi penuh pada modul Penjualan, Keuangan, dan Stok.

---

## 📊 TAHAP 3: Optimalisasi Kerja Lapangan & Penerimaan Stok

### 1. Penyusutan Berat Otomatis & Auto-Jurnal Biaya
* **Lokasi**: [StokService.php](file:///c:/xamppp/htdocs/peace_seafood/src/services/StokService.php)
* **Logika**:
  - Di alur stok masuk, Checker menginput `berat_nota_supplier` (berat awal saat dibeli) ke dalam `qty`.
  - Di alur timbangan, Admin menginput `berat_riil_gudang` (berat saat masuk cold storage) ke dalam `qty_actual`.
  - Sistem otomatis menghitung selisih penyusutan (`susut = berat_nota_supplier - berat_riil_gudang`) dan persentasenya.
  - JIKA `susut > 0`, sistem **mengalikan kilogram susut tersebut dengan harga beli per kg produk**, dan **otomatis merekam nilai kerugian finansial** ke tabel `biaya_operasional` dengan kategori **'Penyusutan Stok'** sehingga keseimbangan keuangan tetap terjaga.

### 2. Ekspor PDF Nota & Surat Jalan Asli (Dompdf)
* **Lokasi**: [ExportService.php](file:///c:/xamppp/htdocs/peace_seafood/src/services/ExportService.php#L284-L405)
* **Tindakan**:
  - Mengintegrasikan library `Dompdf` untuk mengonversi HTML Invoice premium menjadi PDF beresolusi tinggi ukuran A4 potrait.
  - PDF dilengkapi dengan header formal perusahaan, kartu info pengiriman pembeli, rincian produk terformat cerdas (Format berat otomatis menjadi Ton/Kuintal/kg), informasi transfer rekening bank, riwayat cicilan, watermark pembayaran, serta kotak tanda tangan pengesahan fisik.
  - Diintegrasikan ke API route `GET /api/penjualan/{id}/pdf` lewat [PenjualanController.php](file:///c:/xamppp/htdocs/peace_seafood/src/controllers/PenjualanController.php#L112-L135).

---

## 🎨 TAHAP 4: Modul Dashboard Utama & Penataan Front-End

Komponen visual dan fungsionalitas front-end pada [dashboard.php](file:///c:/xamppp/htdocs/peace_seafood/src/views/pages/dashboard.php) telah diperbarui dengan estetika tinggi (*premium dark mode style*):

1. **Integrasi Grafik Dinamis Chart.js**:
   - **Line Chart**: Menyajikan "Penjualan 7 Hari Terakhir" secara responsif dengan ketebalan garis indigo/blue yang tajam, point hover radius, gradien latar belakang lembut, dan gridlines yang disamarkan (`rgba(255,255,255,0.04)`) agar berpadu sempurna dengan tema gelap.
   - **Doughnut Chart**: Menyajikan "Komposisi Stok per Jenis Ikan" dengan palet warna harmonis (Blue, Emerald, Amber, Rose, Cyan, Purple) and posisi legenda di bagian bawah.
2. **Widget Progress Bar Kapasitas Cold Storage**:
   - Widget visual baru di barisan stat cards atas dengan batas kapasitas **5.000 kg**.
   - Menampilkan bar transisi warna: jika kapasitas di bawah 80% berwarna **Cyan**, dan jika keterisian di atas 80% otomatis berubah menjadi **Merah** sebagai peringatan.
3. **Dynamic JWT Greeting Header**:
   - Mengambil identitas otentik dari JWT token di `localStorage`, menghasilkan teks salam murni dinamis dengan format uppercase role: `"Selamat datang, [Nama User] | [UPPERCASE_ROLE]"` (contoh: "Selamat datang, Admin Gudang A | ADMIN"). Semua placeholder teks dummy statis seperti `(blm jadi)` telah dihapus total dari file `dashboard.php`.
4. **Conditional Quick Actions (Pengaman Checker)**:
   - Tombol "Buat Nota" dan "Keuangan" **wajib disembunyikan** secara dinamis jika role user yang masuk adalah **CHECKER** (atau `checker`). Implementasi menggunakan pengecekan case-insensitive yang robust pada front-end (`dashboard.php`) dan penyesuaian penanganan role yang case-insensitive pada backend (`DashboardController.php`) demi menjamin kepatuhan hak akses.
5. **Mini-Table "Log Aktivitas Terkini" (Audit Trail)**:
   - Card tabel ringkas baru di bagian bawah dashboard (khusus untuk role Bos, Super Admin, dan Admin).
   - Menampilkan 5 log aktivitas mutasi real-time secara kronologis lengkap dengan label *action* berwarna (INSERT = Hijau, UPDATE = Kuning, DELETE = Gray) dan deskripsi ramah pengguna yang memetakan aktivitas operasional.

---

## 📈 TAHAP 5: Fungsionalitas Ekspor Laporan Real-Time (CSV & PDF)

Fitur ekspor data laporan yang dinamis dan berorientasi filter tanggal telah berhasil diimplementasikan sepenuhnya pada halaman Laporan:

### 1. Modifikasi Backend Service (`ExportService.php`)
- **Tugas Export CSV (`exportLaporanCsv`)**: Menambahkan method baru untuk mengarahkan data secara cerdas ke format CSV sesuai tab aktif (`stok`, `penjualan`, atau `keuangan`/`aging`). Format file dikonfigurasi dengan pemisahan `;` (semicolon) and UTF-8 BOM agar kompatibel penuh saat dibuka langsung di Microsoft Excel. Menambahkan filter rentang tanggal `hp.created_at` secara robust pada export keuangan.
- **Tugas Export PDF (`exportLaporanPdf`)**: Menambahkan method untuk me-render data terpilih dari service Stok, Penjualan, atau Keuangan ke format HTML lalu dikonversi menjadi file PDF berkualitas tinggi (landscape A4) menggunakan library `Dompdf`.
- **Desain Layout Premium (`generateReportHtml`)**: Memperbarui template HTML-to-PDF dengan styling profesional yang bersih:
  - Header berkelas dengan detail identitas perusahaan *Peace Seafood*.
  - Pemformatan angka rupiah, kilogram (kg), dan status badge yang rapi.
  - **Tabel Rekapitulasi Total**: Menghitung and menampilkan baris total kumulatif nominal (Total Rekapitulasi Stok, Total Penjualan Final, atau Total Piutang/Hutang Outstanding) di bagian paling bawah tabel secara dinamis.

### 2. Penyesuaian Backend Controller & Routing (`LaporanController.php` & `routes/api.php`)
- **Controller Refactoring**: Metode `exportExcel` dan `exportPdf` dimodifikasi agar menerima parameter `tab` (alias dari `tipe`), `dari`, dan `sampai` secara dinamis. Semua request dibalut dalam blok try-catch yang aman untuk menghindari crash runtime.
- **Token Query Parameter Fallback**: Memperbarui `JWT::getFromRequest()` agar secara otomatis memeriksa parameter `?token=...` jika header Authorization atau HTTP cookie tidak terkirim. Hal ini mutlak diperlukan agar proses download via `window.location` terotentikasi sempurna.
- **Registrasi Endpoint**: Mendaftarkan rute `GET` baru di `routes/api.php` agar dapat dipanggil langsung oleh browser:
  - `GET /api/laporan/export-csv` (memetakan ke `exportExcel`)
  - `GET /api/laporan/export-pdf` (memetakan ke `exportPdf`)
  - Ditambah *backward-compatibility* rute standard `/api/laporan/export/excel` dan `/api/laporan/export/pdf` sebagai method `GET`.

### 3. Integrasi Front-End (`src/views/laporan/index.php`)
- **Trigger Aksi Real-Time**: Mengubah fungsi event handler `exportCsv()` dan `exportPdf()` berbasis Alpine.js untuk membaca input filter `filters.dari`, `filters.sampai`, `activeTab`, dan `localStorage.getItem('token')` saat tombol diklik.
- **Auto-Download**: Mengarahkan halaman browser menggunakan `window.location.href` ke endpoint API tujuan. Proses unduhan berjalan secara instan di latar belakang tanpa mengganggu jalannya aplikasi.

