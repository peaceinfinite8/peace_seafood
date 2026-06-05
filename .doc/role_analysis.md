# 🎭 Analisis Role System — Peace Seafood

## Masalah: Role "Bos" Sekarang = Super Admin

Kamu benar. Role **Bos** di sistem ini sebenarnya adalah **Super Admin** karena punya kemampuan:

### Apa yang Bos BISA lakukan sekarang (di code):

| Kemampuan | Termasuk Bos? | Termasuk Super Admin? |
|-----------|:---:|:---:|
| 🔧 Kelola Settings & Konfigurasi sistem | ❌ Bukan urusan Bos | ✅ Super Admin |
| 👥 CRUD User (tambah/edit/hapus akun) | ❌ Bukan urusan Bos | ✅ Super Admin |
| 🏢 CRUD Gudang (tambah/edit gudang) | ❌ Bukan urusan Bos | ✅ Super Admin |
| 💾 Backup Database | ❌ Bukan urusan Bos | ✅ Super Admin |
| ✅ Approve/Reject Retur | ✅ Bos bisa | ✅ Bisa juga |
| 📊 Lihat semua laporan & export | ✅ Bos butuh | ✅ Bisa juga |
| 👁️ Lihat stok, penjualan, keuangan | ✅ Bos butuh | ✅ Bisa juga |

> [!IMPORTANT]  
> **Kesimpulan**: Role "Bos" saat ini itu **campuran** antara owner bisnis + system administrator. Di dunia nyata, Bos di gudang seafood **tidak akan** mengurus kelola user, settings teknis, atau backup database.

---

## Apa yang Bos Sebenarnya Butuhkan

Berdasarkan deskripsi kamu, Bos cuma mau:

```
📊 Dashboard Executive
├── 💰 Keuangan Masuk (penjualan cash + pembayaran piutang)
├── 💸 Keuangan Keluar (beli stok + biaya operasional + bayar hutang)  
├── 📦 Cek Stok (berapa stok sekarang per produk/gudang)
├── 📈 Keuntungan (total penjualan - total pembelian - biaya)
├── 📉 Kerugian (retur, susut, piutang macet)
├── 🛒 Penjualan Hari Ini (total nota hari ini)
├── 🏆 Produk Terlaris (ranking produk paling banyak terjual)
└── ✅ Approve Retur (satu-satunya aksi, bukan cuma lihat)
```

Semua ini **READ-ONLY** kecuali approve retur. Bos tidak perlu bisa:
- ❌ Tambah/edit/hapus user
- ❌ Tambah/edit gudang  
- ❌ Ubah settings/konfigurasi
- ❌ Backup database
- ❌ Input data apapun

---

## Rekomendasi: Pecah Jadi 4 Role

| Role | Deskripsi | Contoh User |
|------|-----------|-------------|
| 🔑 **Super Admin** | Kelola sistem, user, gudang, settings, backup | IT support / pemilik yg melek teknis |
| 👑 **Bos** | Lihat laporan & keuangan, approve retur, view-only | Pak Bos / Owner |
| 🖥️ **Admin** | Operasional gudang: stok, nota, keuangan, master data | Staff administrasi gudang |
| 📋 **Checker** | Timbang barang, input stok masuk | Pekerja di lapangan |

### Perbandingan Permission Baru:

```
                        Super Admin    Bos        Admin      Checker
                        ───────────    ────       ─────      ───────
Dashboard               ✅ Full        ✅ Full    ✅ Basic    ✅ Stok only
Stok View               ✅             ✅         ✅          ✅
Stok Input/Edit         ✅             ❌         ✅          ❌
Timbangan               ✅             ❌         ❌          ✅
Penjualan View          ✅             ✅         ✅          ❌
Penjualan Create/Edit   ✅             ❌         ✅          ❌
Penitipan View          ✅             ✅         ✅          ❌
Penitipan Create        ✅             ❌         ✅          ❌
Retur View              ✅             ✅         ✅          ❌
Retur Create            ✅             ❌         ✅          ✅
Retur Approve           ✅             ✅         ❌          ❌
Keuangan View           ✅             ✅         ✅          ❌
Keuangan Create/Bayar   ✅             ❌         ✅          ❌
Laporan & Export        ✅             ✅         ✅ (limited) ❌
Master Data CRUD        ✅             ❌         ✅          ❌
──── SUPER ADMIN ONLY ────
Settings                ✅             ❌         ❌          ❌
User Management         ✅             ❌         ❌          ❌
Gudang Management       ✅             ❌         ❌          ❌
Backup Database         ✅             ❌         ❌          ❌
Ubah Harga Produk       ✅             ❌         ❌          ❌
```

---

## Apa yang Perlu Diubah di Code

### 1. Database — Tambah enum value di tabel `users`

```sql
-- Sebelum
`role` ENUM('bos','admin','checker') NOT NULL

-- Sesudah  
`role` ENUM('super_admin','bos','admin','checker') NOT NULL
```

### 2. Config — Update [roles.php](file:///c:/xamppp/htdocs/peace_seafood/config/roles.php)

```php
return [
    'super_admin' => [
        'dashboard.*',
        'stok.*',
        'penjualan.*',
        'penitipan.*',
        'retur.*',
        'keuangan.*',
        'laporan.*',
        'master_data.*',
        'settings.*',       // ← pindah dari bos
        'user.*',           // ← pindah dari bos
        'gudang.*',         // ← pindah dari bos  
        'export.*',
        'notifikasi.*',
        'backup.*',         // ← pindah dari bos
        'harga.*',          // ← ubah harga produk
    ],
    'bos' => [
        'dashboard.*',
        'stok.view',         // lihat saja
        'penjualan.view',    // lihat saja
        'penitipan.view',    // lihat saja
        'retur.view',
        'retur.approve',     // satu-satunya aksi write
        'keuangan.view',     // lihat saja
        'laporan.*',         // full akses laporan
        'export.*',          // bisa export
        'notifikasi.*',
    ],
    'admin' => [
        // ... sama seperti sekarang
    ],
    'checker' => [
        // ... sama seperti sekarang
    ],
];
```

### 3. Controllers — Ganti semua cek `$user['role'] === 'bos'` di [SettingsController.php](file:///c:/xamppp/htdocs/peace_seafood/src/controllers/SettingsController.php)

```diff
- if ($user['role'] !== 'bos') Response::forbidden(...)
+ if ($user['role'] !== 'super_admin') Response::forbidden(...)
```

Ini ada di **6 tempat** di SettingsController:
- Line 73 (update setting)
- Line 112 (store user)  
- Line 134 (update user)
- Line 147 (delete user)
- Line 172 (store gudang)
- Line 192 (update gudang)
- Line 210 (backup)

### 4. Dashboard — Tambah Fitur Khusus Bos

Dashboard Bos perlu ditambahkan data yang belum ada:

| Data yang Dibutuhkan Bos | Status Sekarang |
|--------------------------|:---:|
| Keuangan masuk (total penjualan cash + terima piutang) | ❌ Belum ada |
| Keuangan keluar (beli stok + biaya operasional + bayar hutang) | ❌ Belum ada |
| Keuntungan/Kerugian (Laba Rugi) | ❌ Belum ada |
| Produk terlaris | ❌ Belum ada |
| Penjualan hari ini | ✅ Sudah ada |
| Cek stok | ✅ Sudah ada |
| Total hutang/piutang | ✅ Sudah ada |

---

## Ringkasan

> [!IMPORTANT]
> **Ya, role "Bos" saat ini memang Super Admin.** Perlu dipecah menjadi 2 role terpisah agar sesuai kebutuhan nyata di gudang. Bos cukup **lihat-lihat + approve retur saja**, semua manajemen teknis ada di Super Admin.

Mau saya langsung implementasi perubahan role ini?
