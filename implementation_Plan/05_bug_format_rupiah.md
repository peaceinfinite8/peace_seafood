# Bug #05 — Tidak Ada Format Rupiah Real-time pada Input Harga

## Informasi

- **Jenis**: 🟡 Bug Minor
- **Status**: ✅ Selesai
- **File Terdampak**: `src/views/master-data/produk.php`

## Penyebab

Input harga bertipe `number` standar, tidak ada pemformatan titik ribuan.

## Solusi

- Ubah input harga dari tipe `number` ke tipe `text`
- Tambahkan event listener format titik ribuan dinamis saat mengetik

## Verifikasi

- [x] Ketik angka pada field harga — titik ribuan harus muncul otomatis
- [x] Contoh: `150000` harus tampil sebagai `150.000`
- [x] Nilai tersimpan ke database tanpa titik (angka murni)
