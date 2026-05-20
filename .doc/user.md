# 👤 USER ACCOUNTS — Peace Seafood

> **URL Aplikasi**: `http://localhost/peace_seafood/`

---

## 🔑 Daftar Akun Login

| # | Role | Nama | Email | Password | Gudang |
|---|---|---|---|---|---|
| 1 | **Bos** | Bos Gudang | `bos@example.com` | `bos123` | Semua Gudang |
| 2 | **Admin** | Admin Gudang A | `admin@example.com` | `admin123` | Gudang A - Pusat |
| 3 | **Checker** | Checker Gudang A | `checker@example.com` | `checker123` | Gudang A - Pusat |
| 4 | **Admin** | Admin Gudang B | `admin2@example.com` | `admin2` | Gudang B - Cabang |

> 💡 **Tip**: Di halaman login, klik kotak **Demo Credentials** untuk mengisi otomatis akun Bos.

---

## 🏢 Daftar Gudang

| ID | Nama Gudang | Alamat | Kota |
|---|---|---|---|
| 1 | Gudang A - Pusat | Jl. Merdeka No. 1 | Jakarta |
| 2 | Gudang B - Cabang | Jl. Sudirman No. 5 | Jakarta |
| 3 | Gudang C - Satellite | Jl. Thamrin No. 10 | Tangerang |

---

## 🎯 Hak Akses per Role

### 👑 BOS
- ✅ Lihat semua gudang (tidak terbatas satu gudang)
- ✅ Approve / Reject retur
- ✅ Kelola settings & konfigurasi sistem
- ✅ Kelola user & gudang
- ✅ Export laporan (PDF / Excel)
- ✅ Ubah harga produk
- ✅ Akses semua modul

### 🖥️ ADMIN
- ✅ Input & kelola stok masuk
- ✅ Buat nota penjualan (draft & finalize)
- ✅ Kelola penitipan (terima & catat penjualan)
- ✅ Input retur stok & retur piutang
- ✅ Kelola keuangan (hutang/piutang, biaya operasional)
- ✅ Kelola master data (supplier, pembeli, produk)
- ✅ Lihat laporan
- ❌ Approve retur (hanya Bos)
- ❌ Kelola settings & user

### 📋 CHECKER
- ✅ Input stok masuk
- ✅ Input timbangan & susut
- ✅ Input retur stok
- ✅ Lihat stok & laporan
- ❌ Buat nota penjualan
- ❌ Kelola penitipan / keuangan
- ❌ Kelola master data

---

## 🔐 Informasi Keamanan

- **Metode Auth**: JWT Token (disimpan di `localStorage`)
- **Token Expiry**: 24 jam (86400 detik)
- **Password Hashing**: bcrypt (PHP `password_hash`)
- **Login field**: `email` (support juga login dengan `name`)

---

## 🌐 URL Penting

| Halaman | URL |
|---|---|
| Login | `http://localhost/peace_seafood/` |
| Dashboard | `http://localhost/peace_seafood/dashboard` |
| Stok | `http://localhost/peace_seafood/stok` |
| Penjualan | `http://localhost/peace_seafood/penjualan` |
| Penitipan | `http://localhost/peace_seafood/penitipan` |
| Retur | `http://localhost/peace_seafood/retur` |
| Keuangan | `http://localhost/peace_seafood/keuangan` |
| Laporan | `http://localhost/peace_seafood/laporan` |
| Master Data | `http://localhost/peace_seafood/master-data` |
| Settings | `http://localhost/peace_seafood/settings` |
| API Base | `http://localhost/peace_seafood/api/` |

---

*Dokumen ini berisi credential untuk environment **local/development** saja.*
*Jangan gunakan password ini di production!*
