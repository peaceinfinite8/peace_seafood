# Bug #01 — Tampilan Kartu Rekening Bank (Light Mode vs Dark Mode)

## Informasi

- **Jenis**: 🔴 Bug Kritis
- **Status**: ✅ Selesai
- **File Terdampak**: `src/views/settings/index.php`
- **Lokasi**: Baris 196–229

## Penyebab

Kartu inaktif menggunakan `var(--bg-light)` sebagai background,
namun kelas teks tetap putih (`text-white`, `text-slate-100`).
Akibatnya teks tidak terbaca di Light Mode (putih di atas putih).

## Solusi

- Kartu AKTIF:
  - Background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%)
  - Border: #3b82f6
  - Teks: putih

- Kartu INAKTIF:
  - Background: linear-gradient(135deg, #334155 0%, #1e293b 100%)
  - Border: var(--border-color)
  - Opacity: 0.55

## Verifikasi

- [x] Buka Pengaturan > Rekening Bank
- [x] Tambah rekening dengan status Nonaktif
- [x] Cek di Light Mode — teks harus terbaca jelas
- [x] Cek di Dark Mode — tampilan harus konsisten
