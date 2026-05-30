# Bug #02 — Validasi id_gudang Gagal (Multi-Role)

## Informasi

- **Jenis**: 🔴 Bug Kritis
- **Status**: ✅ Selesai
- **File Terdampak**: `src/controllers/MasterDataController.php`
- **Fungsi**: `produkStore`

## Role yang Ada di Sistem

| Role          | Punya id_gudang      |
| ------------- | -------------------- |
| `super_admin` | ❌ Tidak             |
| `bos`         | ❌ Kemungkinan tidak |
| `admin`       | ✅ Punya             |
| `checker`     | ✅ Punya             |

## Penyebab

Validasi `id_gudang is required` bersifat hard tanpa fallback
untuk role yang tidak berafiliasi ke gudang (super_admin, bos).

## Solusi

Tambahkan logika kondisional di fungsi `produkStore`:

1. Jika role adalah `super_admin` atau `bos`:
   - Skip validasi id_gudang
   - Auto-assign ke gudang aktif pertama dari database
2. Jika role adalah `admin` atau `checker`:
   - Tetap wajib punya id_gudang dari afiliasinya

## Verifikasi

- [x] Login sebagai `super_admin` → tambah produk → harus berhasil
- [x] Login sebagai `bos` → tambah produk → harus berhasil
- [x] Login sebagai `admin` → tambah produk → harus berhasil
- [x] Login sebagai `checker` → tambah produk → harus berhasil
