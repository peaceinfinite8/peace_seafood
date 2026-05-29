# Incomplete #09 — Stok Opname Belum Punya Halaman Web

## Informasi

- **Jenis**: 🟠 Incomplete
- **Status**: ✅ Selesai
- **File Terdampak**: `routes/web.php`, `src/controllers/StokOpnameController.php`

## Penyebab

Controller dan route API untuk stok opname sudah tersedia,
tetapi tidak ada route web dan tidak ada view di `src/views/` untuk membuka modul ini dari aplikasi.

## Solusi

- Tambahkan halaman web untuk daftar opname, detail, create draft, dan finalize
- Tambahkan menu/navigasi yang mengarah ke halaman tersebut
- Pastikan alur CRUD bisa dilakukan dari UI tanpa perlu memanggil API manual

## Verifikasi

- [x] Modul stok opname muncul di navigasi aplikasi
- [x] User bisa melihat daftar opname dari UI
- [x] User bisa membuat sesi opname dari UI
- [x] User bisa membuka detail dan finalize dari UI
