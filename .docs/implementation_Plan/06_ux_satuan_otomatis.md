# UX #06 — Dropdown Satuan Harusnya Otomatis (Bukan Manual)

## Informasi

- **Jenis**: 🔵 Catatan UX
- **Status**: ✅ Selesai
- **File Terdampak**: `src/views/master-data/produk.php`

## Penyebab

Dropdown satuan masih tampil di UI dan bisa diubah manual,
padahal semua produk ikan menggunakan satuan `kg`.

## Solusi

- Sembunyikan dropdown satuan dari UI (hidden)
- Hardcode nilai satuan ke `kg`

## Verifikasi

- [x] Dropdown satuan tidak terlihat di UI
- [x] Satuan tersimpan sebagai `kg` di database
