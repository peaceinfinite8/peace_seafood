# 📖 PEACE SEAFOOD — PANDUAN AWAL UNTUK AGEN AI

> **File ini WAJIB dibaca pertama sebelum membaca file lain!**

---

## 🎯 Konteks Project

**Peace Seafood** adalah aplikasi manajemen gudang ikan berbasis web app modern dengan fitur lengkap untuk mengelola:
- Stok ikan dari multiple suppliers
- Penimbangan & susut
- Penjualan (retail & bulk)
- Penitipan dari gudang lain
- Retur barang
- Hutang/Piutang supplier & pembeli
- Laporan & export PDF/Excel
- Multi gudang dengan role berbeda

**Target User:** Bos gudang, Admin, Checker
**Target Server:** Localhost XAMPP (MySQL 8.0, PHP 8.2)
**Bahasa:** Campur Indonesia + Inggris
**Timezone:** WIB (Asia/Jakarta)

---

## 📚 Urutan Baca File (CRITICAL!)

Baca dalam urutan ini agar tidak ada asumsi yang salah:

### **STEP 1: Pemahaman Umum**
1. `PRD/00-index.md` — Daftar isi lengkap
2. `PRD/01-overview.md` — Konteks bisnis & fitur utama
3. `PRD/04-user-roles.md` — Role & permission

### **STEP 2: Teknis & Database**
4. `PRD/02-tech-stack.md` — Stack lengkap + versi
5. `PRD/05-database-schema.md` — Schema & relasi
6. `PRD/03-folder-structure.md` — Struktur project

### **STEP 3: Bisnis Logic**
7. `PRD/06-module-flows.md` — Alur setiap modul detail
8. `PRD/09-business-rules.md` — Rules & kalkulasi
9. `PRD/08-settings.md` — Opsi di Settings Bos

### **STEP 4: UI & Output**
10. `PRD/10-ui-guidelines.md` — Design system & style
11. `PRD/07-dashboard.md` — Dashboard tiap role
12. `PRD/11-exports-and-output.md` — Format PDF & Excel

### **STEP 5: API & Implementation**
13. `PRD/12-api-endpoints.md` — Endpoint lengkap
14. `PRD/13-seeding-and-initial-data.md` — Data initial & seeder

### **STEP 6: Security, Error, Notifikasi**
15. `PRD/15-security.md` — Keamanan wajib
16. `PRD/14-error-handling.md` — Handling error & validasi
17. `PRD/16-notifications.md` — Notifikasi system
18. `PRD/17-returns.md` — Alur retur

### **STEP 7: Schema & Stack**
19. `schema/` — JSON schema files (baca sambil implement)
20. `tech-stack-notes.md` — Dokumentasi dependency

---

## ⚠️ ATURAN WAJIB (Jangan Dilanggar!)

### **1. Tentang Multi Gudang**
- **Bos** bisa punya lebih dari 1 gudang → opsi di Settings
- Kalau aktif, Bos lihat **semua gudang sekaligus** di dashboard
- Admin/Checker terima **gudang spesifik** saja waktu onboarding
- **Database**: `gudang.id_bos` wajib ada

### **2. Tentang Harga**
- Harga ikan bisa berubah kapan saja
- **Wajib** ada history perubahan harga (tabel `harga_history`)
- Saat nota dibuat, simpan harga **current time** (bukan history)
- Fitur set harga ada di Master Data atau Modul Harga

### **3. Tentang Stok Minimum Alert**
- Ada setting untuk threshold stok minimum per jenis ikan
- Alert muncul kalau stok real < threshold
- Alert di dashboard & notifikasi in-app
- **Tidak** kirim WhatsApp (on hold)

### **4. Tentang Penitipan**
- **Alur**: Gudang A nitip ikan ke Gudang B dengan harga kesepakatan
- **Penjual**: Bisa Gudang A atau Gudang B
- **Komisi**: Opsi di Settings (potong langsung atau bayar terpisah)
- **Tidak dicatat** sebagai penjualan normal — tabel `titipan` terpisah

### **5. Tentang Retur**
- Ada alur retur stok **dan** retur piutang
- Retur stok → kurangi dari gudang penjual
- Retur piutang → adjust hutang/piutang
- Bisa partial atau full return
- Wajib ada reason/alasan retur

### **6. Tentang Dark Mode & CSS Variable**
- **Wajib** pakai CSS variable (`--color-primary`, `--bg-light`, dll)
- Toggle dark/light di navbar
- Preference tersimpan di localStorage

### **7. Tentang Error & Validasi**
- **Input validation**: Server-side WAJIB, client-side tambahan
- **Error response**: Harus konsisten (lihat `14-error-handling.md`)
- **Try-catch**: Wrap semua DB operations

### **8. Tentang Security**
- **JWT** untuk authentication, token di HttpOnly cookie
- **CORS** configured properly
- **Input sanitize** pakai prepared statement / parameterized query
- **File upload** validate MIME type + size limit
- **CSRF** protection pada form

### **9. Tentang PWA**
- Wajib ada `manifest.json`
- Service worker untuk offline capability
- Install button di navbar

---

## 📋 Hal yang TIDAK boleh Diasumsikan

❌ **Jangan asumsikan** structure folder, baca `03-folder-structure.md`
❌ **Jangan asumsikan** permission per role, baca `04-user-roles.md`
❌ **Jangan asumsikan** harga itu static, baca `06-module-flows.md`
❌ **Jangan asumsikan** semua bos punya 1 gudang, baca `08-settings.md`
❌ **Jangan asumsikan** format notifikasi, baca `16-notifications.md`
❌ **Jangan asumsikan** UI component, referensi `10-ui-guidelines.md`

---

## 🔗 File Lain yang Tersedia

```
├── schema/01-master.json      (Tables: users, gudang, supplier, pembeli, jenis_ikan, produk)
├── schema/02-stok.json        (Tables: stok_masuk, timbangan, susut)
├── schema/03-penjualan.json   (Tables: nota, nota_detail)
├── schema/04-penitipan.json   (Tables: titipan, titipan_penjualan)
├── schema/05-keuangan.json    (Tables: hutang_piutang, biaya_operasional)
├── schema/06-harga-history.json (Tables: harga_history)
└── tech-stack-notes.md                   (Dependency & library detail)
```

---

## ✅ Checklist Sebelum Mulai Coding

- [ ] Sudah baca dari `00-index.md` sampai `09-business-rules.md` minimum
- [ ] Paham struktur database & relasi
- [ ] Paham alur stok, penjualan, penitipan, retur
- [ ] Paham permission tiap role
- [ ] Paham settings yang bisa dikonfigurasi Bos
- [ ] Sudah siapkan XAMPP + MySQL 8.0 + PHP 8.2
- [ ] Sudah install dependency via Composer

---

## 🎯 Catatan Penting

- **Ini bukan code** — ini adalah blueprint/requirement
- **Ini comprehensive** — semua yang ada perlu diimplementasi
- **Ini detailed** — follow sesuai ini, jangan interpret sendiri
- **Multi gudang CRITICAL** — jangan skip, affect database dari awal

---

**Status:** ✅ Ready to Code
**Last Updated:** 2025
**Next Step:** Baca `PRD/00-index.md` → mulai coding

---
