# Bug #04 — Input Numerik Tampil Default Angka 0

## Informasi

- **Jenis**: 🟡 Bug Minor
- **Status**: ✅ Selesai
- **File Terdampak**: `src/views/master-data/produk.php`
- **Lokasi**: Modal tambah produk

## Penyebab

Field `harga_beli`, `harga_jual`, `stok_minimum`
diinisialisasi dengan nilai `0`.

## Solusi

Ubah inisialisasi ketiga field dari `0` menjadi `""` (string kosong).

## Verifikasi

- [x] Buka modal tambah produk baru
- [x] Field harga_beli, harga_jual, stok_minimum harus tampil kosong
