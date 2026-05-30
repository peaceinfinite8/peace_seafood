# Bug #07 — Tombol Hapus Jenis Ikan Tidak Tersambung ke Backend

## Informasi

- **Jenis**: 🟠 Incomplete
- **Status**: ✅ Selesai
- **File Terdampak**: `src/views/master-data/jenis-ikan.php`, `routes/api.php`, `src/controllers/MasterDataController.php`
- **Fungsi**: `deleteJenis`

## Penyebab

UI Jenis Ikan sudah menampilkan tombol hapus dan memanggil endpoint
`DELETE /api/master/jenis-ikan/{id}`, tetapi route tersebut tidak ada di `routes/api.php`
dan controller juga tidak memiliki method delete/destroy untuk jenis ikan.

## Solusi

- Tambahkan route `DELETE /master/jenis-ikan/{id}` di `routes/api.php`
- Tambahkan method delete/destroy di `MasterDataController`
- Gunakan soft delete (`is_active = 0`) agar konsisten dengan supplier dan pembeli

## Verifikasi

- [x] Klik hapus pada jenis ikan dari halaman Master Data
- [x] Data harus dinonaktifkan atau terhapus tanpa error 404
- [x] List jenis ikan harus refresh setelah delete
