# 👤 USER ACCOUNTS — Peace Seafood

> **URL Aplikasi**: `http://localhost/peace_seafood/`

---

## 🔑 Daftar Akun Login

| # | Role | Nama | Email | Password | Gudang |
|---|---|---|---|---|---|
| 1 | **Super Admin** | Super Admin | `superadmin@example.com` | `password` | Semua Gudang |
| 2 | **Bos** | Bos Gudang | `bos@example.com` | `password` | Semua Gudang |
| 3 | **Admin** | Admin Gudang A | `admin@example.com` | `password` | Gudang A - Pusat |
| 4 | **Checker** | Checker Gudang A | `checker@example.com` | `password` | Gudang A - Pusat |
| 5 | **Admin** | Admin Gudang B | `admin2@example.com` | `password` | Gudang B - Cabang |

> 💡 **Tip**: Di halaman login, klik kotak **Demo Credentials** untuk mengisi otomatis akun.

---

## 🏢 Daftar Gudang

| ID | Nama Gudang | Alamat | Kota |
|---|---|---|---|
| 1 | Gudang A - Pusat | Jl. Merdeka No. 1 | Jakarta |
| 2 | Gudang B - Cabang | Jl. Sudirman No. 5 | Jakarta |
| 3 | Gudang C - Satellite | Jl. Thamrin No. 10 | Tangerang |

---

## 🎯 Hak Akses per Role

### ⚡ SUPER ADMIN
- ✅ Kontrol penuh pengaturan & konfigurasi sistem
- ✅ Tambah/Ubah/Hapus User & Gudang (User/Gudang CRUD)
- ✅ Database backup otomatis/manual
- ✅ Ubah harga beli & jual produk
- ✅ Akses penuh ke semua modul sistem

### 👑 BOS
- ✅ Dashboard eksekutif finansial (Keuangan Masuk, Keuangan Keluar, Laba/Rugi, Top Produk)
- ✅ Approve / Reject retur
- ✅ Lihat semua laporan & stok semua gudang
- ✅ Export laporan (PDF / Excel)
- ❌ Mengubah settings, user, atau gudang (Locked to Super Admin)
- ❌ Menambah/mengubah data master (Locked to Super Admin/Admin)

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
