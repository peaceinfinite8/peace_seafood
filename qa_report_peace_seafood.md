# QA Report - Peace Seafood

**Tanggal uji:** 2026-05-23
**Lingkup:** `/stok`, `/stok/masuk`, `/stok/timbangan`, `/penjualan`, `/keuangan`, `/master-data`, `/laporan`, `/penitipan`, `/retur`, dan akses role `super_admin`, `bos`, `admin`, `checker`.

## 1. Ringkasan Eksekutif

Secara umum aplikasi bisa dibuka dan halaman inti berhasil dirender di browser. Setelah QA berjalan, dua blocker backend yang membuat halaman admin gagal tampil berhasil diperbaiki: endpoint rekening bank yang sempat 500 karena schema drift, dan halaman retur yang sempat 403/500 karena middleware gudang serta helper pagination yang belum sinkron.

Masih ada beberapa issue UI/runtime yang tidak fatal tetapi cukup luas, terutama error Alpine pada tema dan error chart di dashboard. Itu membuat pengalaman pakai belum bersih, walau alur utama sudah berjalan.

## 2. Tabel Akses per Role

| Halaman           | Super Admin | Bos | Admin | Checker |
| ----------------- | ----------: | --: | ----: | ------: |
| Dashboard         |          ✅ |  👁 |    ✅ |      ✅ |
| Stok & Inventory  |          ✅ |  👁 |    ✅ |      ✅ |
| Input Stok Masuk  |          ✅ |  ❌ |    ✅ |      ❌ |
| Timbangan & Susut |          ✅ |  ❌ |    ❌ |      ✅ |
| Penjualan         |          ✅ |  👁 |    ✅ |      ❌ |
| Keuangan          |          ✅ |  👁 |    ✅ |      ❌ |
| Master Data       |          ✅ |  👁 |    ✅ |      ❌ |
| Laporan           |          ✅ |  👁 |    👁 |      👁 |
| Penitipan         |          ✅ |  👁 |    ✅ |      ❌ |
| Retur             |          ✅ |  👁 |    ✅ |      ✅ |

Keterangan singkat: `👁` berarti halaman bisa dilihat tetapi fungsinya terbatas, `❌` berarti tidak semestinya diakses untuk role itu, dan `✅` berarti akses penuh sesuai role model yang diuji.

## 3. Hasil Pengujian per Halaman

### Dashboard

- Halaman terbuka dan menampilkan ringkasan metrik, quick actions, dan daftar data terbaru.
- Ditemukan error runtime global di dashboard: `theme is not defined`, `stats is not defined`, dan `Chart with ID '0' must be destroyed before the canvas ... can be reused`.
- Dampak: dashboard tetap tampil, tetapi console penuh error dan beberapa widget berpotensi tidak stabil.

### Stok & Inventory

- Halaman terbuka dan menampilkan filter dasar: pencarian produk, dropdown jenis, dan status stok.
- Tombol `Input Stok` dan `Timbangan` tampil.
- Tabel data stok terespons, tetapi pada DB aktif pengujian ini data produk sangat terbatas/0 sehingga pengujian autocomplete dan highlight pencarian tidak bisa divalidasi penuh.

### Input Stok Masuk

- Halaman terbuka.
- Field `Penanggung Jawab` terisi otomatis dengan user login.
- Dropdown supplier terisi.
- Dropdown produk terlihat, tetapi pada DB aktif saat uji tidak ada produk yang tersedia sehingga alur input penuh tidak bisa divalidasi sampai submit final.
- Konversi qty dan total kalkulasi tampil, namun belum diuji end-to-end sampai antrian timbangan karena data produk kosong.

### Timbangan & Susut

- Halaman terbuka dan daftar stok pending tampil di sisi kiri.
- Item pending terlihat beserta berat dan harga.
- Struktur halaman sesuai alur proses timbangan, tetapi klik detail item belum divalidasi penuh karena daftar pending sangat bergantung pada data operasional.

### Penjualan

- Halaman terbuka dan filter pencarian nota tampil.
- Tombol `Buat Nota` tampil.
- Fungsionalitas tampilan dasar normal, tanpa crash runtime yang berarti pada halaman ini.

### Keuangan

- Halaman terbuka dan ringkasan hutang, piutang, dan tagihan jatuh tempo tampil.
- Filter `Semua / Hutang / Piutang` berjalan di UI.
- Tabel data tampil normal dengan state kosong bila tidak ada transaksi.

### Master Data

- Halaman terbuka dan kartu Supplier, Pembeli, Jenis Ikan, dan Produk tampil.
- Pada DB aktif pengujian ini semua jumlah data masih 0, sehingga CRUD penuh tidak diuji sampai submit.
- Tidak ditemukan crash runtime pada halaman.

### Laporan

- Halaman terbuka dan filter tanggal tampil.
- Tombol export Excel/PDF terlihat.
- Tabel laporan tampil dalam state kosong tanpa error fatal.

### Penitipan

- Halaman terbuka dan filter status tampil.
- Tombol `Terima Titipan` terlihat.
- Data masih dalam state loading/empty saat pengujian, namun page tidak crash.

### Retur

- Awalnya halaman ini gagal memuat data karena kombinasi 403/500.
- Setelah perbaikan middleware gudang dan helper pagination, endpoint `GET /api/retur` sudah kembali `200 OK`.
- Pada browser halaman masih perlu pemantauan ulang untuk memastikan state loading berubah ke tabel kosong/data, tetapi blocker backend utamanya sudah hilang.

## 4. Daftar Bug & Issue

### Severity Tinggi

- Dashboard memunculkan error Alpine/Chart.js di console setiap kali dibuka: `theme is not defined`, `stats is not defined`, dan error reuse chart canvas.
- Error ini tidak selalu mematikan halaman, tetapi menunjukkan state UI global belum stabil.

### Severity Sedang

- Halaman `Retur` sempat 403 karena `WarehouseMiddleware` hanya memperlakukan `bos` sebagai akses gudang penuh, sementara `super_admin` ikut diblokir ketika `id_gudang` kosong.
- Halaman `Retur` juga sempat 500 karena `ReturController` memanggil `Helper::getPaginationParams()` yang belum ada dan format return-nya tidak cocok.
- Endpoint `GET /api/settings/bank-accounts` sempat 500 karena tabel `bank_account` belum aman di schema aktif; ini sudah diperbaiki dengan guard schema.

### Severity Rendah

- Beberapa halaman masih sangat bergantung pada seed data. Pada DB aktif pengujian ini, produk/master data masih kosong sehingga alur input stok dan sebagian UI turunan tidak bisa diuji penuh.
- Loading state pada beberapa page tidak langsung berubah ke tabel data ketika data kosong, sehingga UX terasa seperti masih memuat.

## 5. Rekomendasi Perbaikan

- Stabilkan state Alpine global di layout, terutama definisi `theme`, `stats`, dan lifecycle dashboard chart.
- Tambahkan pengamanan konsisten untuk controller yang memakai helper pagination agar tidak muncul fatal saat helper berubah nama.
- Tambahkan smoke test untuk endpoint inti: `/api/settings`, `/api/settings/bank-accounts`, `/api/retur`, `/api/stok`, dan `/api/penjualan`.
- Pastikan seed data minimal tersedia untuk produk, supplier, dan retur sehingga QA UI bisa dijalankan end-to-end tanpa hambatan data kosong.
- Rapikan loading state pada page yang memakai fetch async supaya state kosong dibedakan dengan state loading.

## Catatan QA

- Bug blocker yang saya temukan dan perbaiki selama QA: `bank_account` endpoint 500, `retur` 403/500, dan penyesuaian akses `super_admin` pada middleware gudang.
- Sisa issue paling penting yang masih terbuka adalah error Alpine/Chart di dashboard.
