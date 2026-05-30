# Role System Notes

Dokumen ini merangkum target pembagian role untuk Peace Seafood.

## Target role split

| Role | Fokus |
|---|---|
| `super_admin` | Settings, user, gudang, backup, konfigurasi sistem |
| `bos` | Dashboard eksekutif, laporan, export, approve retur |
| `admin` | Operasional gudang, transaksi, master data |
| `checker` | Timbangan, stok masuk, input lapangan |

## Ringkas perubahan yang biasanya dibutuhkan

- Tambah `super_admin` ke enum role user.
- Pindahkan akses settings, user management, gudang management, backup, dan ubah harga ke `super_admin`.
- Biarkan `bos` sebagai role view-first dengan aksi terbatas.

## Catatan

- Ini adalah catatan desain, bukan source of truth hak akses runtime.
- Untuk implementasi aktual, rujuk `config/roles.php` dan middleware/controller terkait.
