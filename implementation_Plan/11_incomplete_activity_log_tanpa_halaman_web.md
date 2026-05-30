# Incomplete #11 — Activity Log Belum Punya Halaman Web

## Informasi

- **Jenis**: 🟠 Incomplete
- **Status**: ✅ Selesai
- **File Terdampak**: `routes/web.php`, `src/controllers/ActivityLogController.php`

## Penyebab

Backend audit trail sudah ada di API, tetapi tidak ada halaman web untuk menampilkan log aktivitas di dalam aplikasi.

## Solusi

- Tambahkan halaman daftar activity log
- Tambahkan route web dan menu yang sesuai
- Sediakan filter sederhana seperti limit, user, atau tanggal jika diperlukan

## Verifikasi

- [x] Activity log bisa diakses dari UI
- [x] Daftar log tampil sesuai permission
- [x] Detail log bisa dibaca tanpa memanggil API manual
