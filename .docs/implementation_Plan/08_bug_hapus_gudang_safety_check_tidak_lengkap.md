# Bug #08 — Hapus Gudang Belum Mengecek Semua Relasi

## Informasi

- **Jenis**: 🔴 Bug Kritis
- **Status**: ✅ Selesai
- **File Terdampak**: `src/controllers/SettingsController.php`, `database/schema.sql`
- **Fungsi**: `deleteGudang`

## Penyebab

Logika `deleteGudang` hanya memeriksa beberapa tabel relasi:
`users`, `stok_masuk`, `nota`, dan `titipan`.
Padahal skema database juga memiliki foreign key ke `supplier`, `pembeli`, `produk`, `settings`, `retur`, `hutang_piutang`, `biaya_operasional`, dan `stok_opname`.

Akibatnya, penghapusan gudang bisa gagal karena constraint database,
atau lebih buruk lagi, lolos sebagian dan meninggalkan data relasi yang tidak aman.

## Solusi

- Tambahkan pemeriksaan semua tabel yang memiliki FK ke `gudang`
- Jika masih ada data relasi, ubah status gudang menjadi nonaktif daripada hard delete
- Hapus hard delete hanya jika benar-benar tidak ada dependensi sama sekali

## Verifikasi

- [x] Coba hapus gudang yang masih punya supplier/produk/settings terkait
- [x] Sistem tidak boleh error SQL constraint
- [x] Gudang dengan data historis harus dinonaktifkan, bukan dihapus paksa
