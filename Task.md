# 📝 Task — Peace Seafood SaaS WMS

# Versi: 2.1 | Diperbarui: 1 Juni 2026

Dokumen ini melacak seluruh rencana pengerjaan sistem secara dinamis.
Selalu diperbarui setiap sesi pengembangan selesai.

---

## ⚠️ ATURAN WAJIB CODING AGENT

Sebelum menulis satu baris kode pun, baca dan patuhi aturan berikut:

1. **DILARANG** mengubah skema tabel existing (`gudang`, `users`, `transaksi`, dll)
2. **DILARANG** mengubah alur JWT middleware & autentikasi yang sudah berjalan
3. **DILARANG** membuat instance Axios baru — selalu gunakan `apiClient` yang sudah ada
4. Semua CSS baru wajib menggunakan prefix `gpa-` untuk menghindari konflik
5. Semua komponen Alpine.js baru harus `x-data` mandiri — tidak boleh nested ke `x-data` existing
6. Semua logika bisnis wajib ditulis di `src/services/` — bukan di Controller
7. Setiap perubahan skema DB wajib disertai file migrasi di `database/migrations/`
8. Setiap service method wajib dibungkus `try-catch` agar error tidak crash halaman
9. Kerjakan selalu dalam urutan: **Migration → Service → Endpoint API → Komponen UI → Include ke Layout**

---

## 📋 URUTAN PRIORITAS PENGERJAAN

```
Prioritas 1 → Task C (Grace Period Alert)
Prioritas 2 → Task F (Platform Settings Manager)
Prioritas 3 → Task A (Lock Screen & Pembayaran)
Prioritas 4 → Task B (Onboarding Wizard)
Prioritas 5 → Task D (Centralized Log)
Prioritas 6 → Task E (Automated Testing)
Prioritas 7 → Task H (Redesign Template Email HTML)
```

---

## TASK A — Lock Screen & Sistem Pembayaran Manual

> Menyempurnakan lock screen yang sudah ada + menambahkan alur pembayaran manual via bukti transfer/QRIS dengan sistem Magic Link approval.

### A1. Perbaikan Lock Screen

- `[x]` Sembunyikan sidebar sepenuhnya saat status tenant locked/suspended
- `[x]` Hanya tampilkan: header minimal (logo + tombol logout) + konten lock screen
- `[x]` Pastikan semua route API tetap menolak request dengan `402` saat locked (sudah ada di middleware — verifikasi saja tidak ada yang terlewat)

### A2. Form Pembayaran di Lock Screen

Tampilan lock screen saat tenant expired:

```
🔒 Masa Aktif Anda Telah Habis

Silakan lakukan pembayaran:

[Rekening bank dari settings]
[Foto QRIS dari settings]
[Nominal sewa dari settings]
[Instruksi pembayaran dari settings]

[ 📎 Upload Bukti Pembayaran ]
      (format & ukuran dari settings)

[ Kirim untuk Diverifikasi ]
```

- `[x]` Bangun form upload bukti pembayaran di lock screen
- `[x]` Semua teks, nominal, rekening, foto QRIS diambil dari tabel `settings` — tidak ada yang hardcode
- `[x]` Validasi file: format dan ukuran maksimal baca dari `settings`
- `[x]` Setelah submit → tampilkan pesan menunggu verifikasi (teks dari `settings`)
- `[x]` Simpan bukti pembayaran ke `storage/uploads/payment_proofs/`
- `[x]` Generate token Magic Link unik satu kali pakai dengan expiry dari `settings` (default 24 jam)
- `[x]` Catat request pembayaran ke tabel `payment_requests` (baru)

### A3. Notifikasi Email ke SaaS Owner (Magic Link)

Email yang diterima SaaS Owner setelah tenant submit bukti:

```
─────────────────────────────────────
  🐟 PEACE SEAFOOD — PERMINTAAN BAYAR
─────────────────────────────────────
  Gudang   : [Nama Gudang]
  Pemilik  : [Nama Bos]
  Nominal  : Rp [nominal dari settings]
  Tanggal  : [timestamp WIB]

  [Foto Bukti Pembayaran]

  [ ✅ APPROVE & BUKA AKSES GUDANG ]

  Tombol hanya bisa dipakai sekali.
  Kadaluarsa dalam [X] jam.
─────────────────────────────────────
```

- `[x]` Kirim email ke SaaS Owner dengan foto bukti + tombol Magic Link
- `[x]` Magic Link berisi token unik → arahkan ke `GET /api/saas/approve-payment?token=xxx`
- `[x]` Token satu kali pakai — setelah diklik langsung hangus
- `[x]` Token expired → tampilkan halaman error, arahkan approve manual dari dashboard
- `[x]` Setelah approve → perpanjang `subscription_until` +X hari (dari `settings`)
- `[x]` Catat ke audit log platform

### A4. Recovery Popup Setelah Akses Dipulihkan

Berlaku untuk: approve via Magic Link MAUPUN perpanjang manual oleh SaaS Owner dari dashboard.

```
┌────────────────────────────────────┐
│   ✅ Akses Gudang Dipulihkan       │
│                                    │
│   Masa aktif Anda diperpanjang     │
│   hingga [Tanggal]                 │
│   ([X] hari ke depan)              │
│                                    │
│         [ Mulai Bekerja ]          │
└────────────────────────────────────┘
```

- `[x]` Popup muncul sekali saja per sesi pemulihan (flag di session)
- `[x]` Berlaku untuk semua role dalam gudang tersebut
- `[x]` Animasi fade-in smooth via Alpine.js `x-transition`

### A5. File Baru Task A

```
src/services/SaaS/PaymentApprovalService.php
src/views/WMS/partials/lock_screen_payment_form.php
src/views/WMS/partials/recovery_popup.php
src/views/emails/payment_request_owner.php
database/migrations/add_payment_requests_table.sql
database/migrations/add_magic_link_tokens_table.sql
```

### A6. File Dimodifikasi Task A

```
src/views/WMS/lock_screen.php   → sembunyikan sidebar, tambahkan form pembayaran
routes/api.php                  → tambah GET /api/saas/approve-payment
src/views/WMS/layout.php        → include recovery_popup.php
```

---

## TASK B — Onboarding Wizard Tenant Baru

> Wizard langkah-demi-langkah untuk Bos yang login pertama kali, memastikan setup awal gudang selesai sebelum mulai operasional.

### B1. Trigger Wizard

- `[x]` Deteksi login pertama kali Bos berdasarkan flag `is_first_login` di tabel `users`
- `[x]` Redirect otomatis ke halaman onboarding sebelum masuk dashboard

### B2. Langkah-Langkah Wizard

**Step 1 — Ganti Password Default**

- `[x]` Form ganti password wajib diisi (tidak bisa skip)
- `[x]` Validasi: min 8 karakter, kombinasi huruf & angka

**Step 2 — Profil Gudang**

- `[x]` Form: nama gudang, alamat, nomor telepon gudang
- `[x]` Data disimpan ke tabel `gudang`

**Step 3 — Pilih Jenis Ikan Bawaan**

- `[x]` Tampilkan daftar jenis ikan default (multi-select)
- `[x]` Data disimpan ke master produk gudang

**Step 4 — Selesai**

- `[x]` Set `subscription_until` = H+14 (baca durasi dari `settings`)
- `[x]` Set `is_first_login` = false
- `[x]` Redirect ke dashboard dengan pesan selamat datang

### B3. File Baru Task B

```
src/controllers/Shared/OnboardingController.php
src/services/Shared/OnboardingService.php
src/views/Shared/onboarding/
  ├── step_1_password.php
  ├── step_2_profil.php
  ├── step_3_produk.php
  └── step_4_selesai.php
database/migrations/add_is_first_login_column.sql
```

---

## TASK C — Grace Period Alert & Sistem Notifikasi

> Sistem peringatan bertahap sebelum akses dikunci + bell icon notifikasi real-time per role.

### C1. Banner Peringatan di Dashboard WMS

- `[x]` Buat komponen `grace_banner.php` sebagai Alpine.js component mandiri (bukan nested ke x-data existing)
- `[x]` Include di `src/views/WMS/layout.php` tepat di bawah navbar (hanya 1 baris include)
- `[x]` Hitung sisa hari dari `subscription_until` vs `NOW()` dalam timezone dari `settings` (default `Asia/Jakarta`)
- `[x]` Sisa > threshold kuning (`settings.grace_yellow_days`, default 7) → tidak tampil
- `[x]` Sisa ≤ threshold kuning & > threshold merah → banner **KUNING** `#F59E0B`
- `[x]` Sisa ≤ threshold merah (`settings.grace_red_days`, default 3) → banner **MERAH** `#EF4444`
- `[x]` Banner permanen — tidak bisa di-dismiss
- `[x]` Animasi masuk: `fade-in` + `slide-down` via Alpine.js `x-transition`
- `[x]` Tombol "Hubungi via WA" ambil nomor dari `settings.platform_whatsapp`

Tampilan banner:

```
┌──────────────────────────────────────────────────────────────┐
│ 🚨  Akses gudang akan terkunci dalam [X] hari               │
│     Hubungi admin untuk perpanjangan masa aktif              │
│                                      [ Hubungi via WA → ]   │
└──────────────────────────────────────────────────────────────┘
```

Spec visual:

- Background: warna solid opacity 15% + border kiri 4px warna penuh
- Border radius: `8px` | Padding: `12px 20px`
- Semua class CSS prefix `gpa-`

### C2. Email Reminder Otomatis ke Tenant

- `[x]` Jalankan pengecekan harian (via cron atau saat ada request masuk)
- `[x]` Kirim email pada H-7, H-3, H-1 (threshold baca dari `settings`)
- `[x]` Cek tabel `grace_email_log` sebelum kirim — **satu trigger satu email satu hari**
- `[x]` Catat ke `grace_email_log` setelah kirim

| Trigger | Subject                                      |
| ------- | -------------------------------------------- |
| H-7     | `Masa aktif gudang Anda tersisa 7 hari`      |
| H-3     | `⚠️ 3 hari lagi — Gudang Anda akan terkunci` |
| H-1     | `🚨 Besok akses gudang Anda terkunci!`       |

### C3. Bell Icon Notifikasi Real-Time

- `[x]` Tambahkan bell icon di navbar (slot yang sudah ada, tidak geser layout)
- `[x]` Badge angka update otomatis setiap 60 detik via polling ringan (endpoint return `{ unread_count: int }` saja)
- `[x]` Polling menggunakan `apiClient` yang sudah ada — bukan instance baru
- `[x]` Klik bell → dropdown notifikasi terbuka, data penuh di-load on-demand
- `[x]` Notifikasi yang sudah dibaca → tetap tersimpan sebagai riwayat (tidak hilang)
- `[x]` Saat SaaS Owner sedang mode impersonasi → polling **dinonaktifkan**

### C4. Notifikasi Per Role

**`saas_owner`**

- `[x]` Tenant perpanjang subscription (via webhook maupun Magic Link)
- `[x]` Tenant baru didaftarkan
- `[x]` Tenant H-7, H-3, H-1 akan expired (digest — bukan satuan)
- `[x]` Tenant di-suspend manual

**`bos`**

- `[x]` Pembayaran disetujui & akses dipulihkan
- `[x]` H-7, H-3, H-1 expired
- `[x]` Karyawan baru ditambahkan ke gudangnya

**`super_admin` & `admin`**

- `[x]` H-3, H-1 expired
- `[x]` Retur baru diajukan
- `[x]` Stok produk hampir habis

**`financial_admin`**

- `[x]` H-3 expired
- `[x]` Transaksi penjualan baru dibuat
- `[x]` Laporan siap diexport

**`checker`**

- `[x]` H-3 expired
- `[x]` Draft nota disetujui atau ditolak admin

**`helper` & `viewer`**

- `[x]` H-3, H-1 expired saja

### C5. Email Digest Harian ke SaaS Owner

- `[x]` Satu email per hari berisi ringkasan semua aktivitas tenant
- `[x]` Jam kirim baca dari `settings.digest_send_time` (default 08:00 WIB)
- `[x]` Toggle aktif/nonaktif dari `settings`

### C6. Notifikasi Saat Tenant Di-Suspend

- `[x]` Saat SaaS Owner suspend tenant → Bos gudang dapat notifikasi in-app + email bahwa akses ditangguhkan

### C7. File Baru Task C

```
src/services/WMS/GracePeriodService.php
src/services/Shared/NotificationService.php
src/views/WMS/partials/grace_banner.php
src/views/partials/notification_bell.php
src/views/emails/grace_reminder.php
src/views/emails/digest_owner.php
src/views/emails/suspend_notify.php
database/migrations/add_grace_email_log_table.sql
database/migrations/add_notifications_table.sql
```

### C8. File Dimodifikasi Task C

```
src/views/WMS/layout.php     → include grace_banner + notification_bell
src/views/SaaS/layout.php    → include notification_bell (versi saas_owner)
routes/api.php               → tambah endpoint polling + load notifikasi
```

---

## TASK D — Integrasi Log Operasional Tenant Terpusat

> Seluruh aktivitas transaksi semua tenant tampil terpusat di dashboard SaaS Owner.

- `[x]` Hubungkan mutasi aktivitas log seluruh tenant ke halaman `/saas/logs`
- `[x]` Filter berdasarkan: tenant, tanggal, jenis aktivitas, role pelaku
- `[x]` Pagination hasil log
- `[x]` Export log ke CSV

---

## TASK E — Automated Testing

> Suite integration test untuk memvalidasi alur autentikasi dan keamanan route.

- `[x]` Test alur login → JWT token → akses halaman sesuai role
- `[x]` Test route guard server-side untuk setiap role (8 role)
- `[x]` Test middleware `subscription_until` expired → return 402
- `[x]` Test Magic Link: valid, expired, sudah dipakai
- `[x]` Test webhook pembayaran: idempotent (order_id sama tidak dobel proses)

---

## TASK F — Platform Settings Manager

> Panel konfigurasi lengkap untuk SaaS Owner. Tidak perlu buka kode lagi untuk perubahan operasional apapun.

**Halaman:** `/saas/settings`
**Akses:** Role `saas_owner` only
**Layout:** 4 tab horizontal

---

### Tab 1 — 🏢 Platform

**Identitas:**

- `[x]` Nama platform (tampil di email & UI)
- `[x]` Logo platform (upload, preview langsung)
- `[x]` Warna tema utama (color picker, update CSS variable)
- `[x]` Nomor WhatsApp bisnis (untuk tombol CTA di banner & email)

**Pesan Kustom Lock Screen:**

- `[x]` Judul lock screen
- `[x]` Teks pesan utama
- `[x]` Instruksi pembayaran (step by step)
- `[x]` Pesan setelah tenant submit bukti bayar

---

### Tab 2 — 📧 Email & Notifikasi

**Konfigurasi SMTP:**

- `[x]` Email pengirim (Gmail)
- `[x]` App Password Gmail (disimpan terenkripsi, tampil `••••`, ada toggle show/hide)
- `[x]` Nama pengirim (tampil di inbox penerima)
- `[x]` Footer email (alamat bisnis, disclaimer)
- `[x]` Tombol **"Test Kirim Email"** → kirim email percobaan ke Gmail SaaS Owner
  - `APP_ENV=local` → tulis ke `storage/logs/email_mock.log`
  - `APP_ENV=production` → kirim sungguhan

**Toggle Notifikasi:**

- `[x]` Email reminder ke tenant (ON/OFF) — auto-save saat toggle
- `[x]` Email digest harian ke SaaS Owner (ON/OFF) — auto-save
- `[x]` Notifikasi in-app bell icon (ON/OFF) — auto-save
- `[x]` Jam kirim digest harian (time picker, default 08:00)

---

### Tab 3 — 💳 Pembayaran

**Rekening Bank:**

- `[x]` Nama bank
- `[x]` Nama pemilik rekening
- `[x]` Nomor rekening

**QRIS:**

- `[x]` Upload foto QRIS (drag & drop atau klik)
- `[x]` Preview foto QR yang sedang aktif

**Harga & Kebijakan Upload:**

- `[x]` Nominal sewa per periode (Rp)
- `[x]` Maksimal ukuran file upload bukti (MB, min 1 max 10)
- `[x]` Format file yang diizinkan (checkbox: JPG, PNG, PDF)
- `[x]` Durasi kadaluarsa Magic Link (jam, default 24)

---

### Tab 4 — ⚙️ Sistem

**Durasi:**

- `[x]` Durasi trial tenant baru (hari, default 14)
- `[x]` Durasi perpanjangan per pembayaran (hari, default 30)
- `[x]` Hari peringatan pertama — H-? (default 7)
- `[x]` Hari peringatan kedua — H-? (default 3)
- `[x]` Hari peringatan ketiga — H-? (default 1)
- `[x]` Threshold banner kuning (hari, default 7)
- `[x]` Threshold banner merah (hari, default 3)

**Lokalisasi:**

- `[x]` Zona waktu (dropdown, default Asia/Jakarta)
- `[x]` Bahasa sistem (dropdown, default Indonesia)

**Maintenance Mode:**

- `[x]` Toggle ON/OFF maintenance mode — auto-save saat toggle
- `[x]` Pesan maintenance (tampil ke semua tenant saat mode aktif)
- `[x]` Saat maintenance ON → semua tenant lihat halaman maintenance, bukan lock screen

---

### F. Behaviour & UX Settings Panel

- `[x]` Tombol **"Simpan Perubahan"** di pojok kanan bawah setiap tab
- `[x]` Loading spinner saat proses simpan
- `[x]` Toast notification setelah simpan: `✅ Pengaturan berhasil disimpan`
- `[x]` Toggle (notifikasi, maintenance) langsung auto-save tanpa klik simpan
- `[x]` Upload gambar: preview langsung setelah pilih, validasi sebelum upload
- `[x]` Progress bar saat upload gambar
- `[x]` Responsive: 2 kolom di desktop, 1 kolom di mobile

### F. File Baru Task F

```
src/controllers/SaaS/PlatformSettingsController.php
src/services/SaaS/PlatformSettingsService.php
src/views/SaaS/settings/
  ├── index.php              (layout tab utama)
  ├── tab_platform.php
  ├── tab_email.php
  ├── tab_pembayaran.php
  └── tab_sistem.php
database/migrations/add_platform_settings_keys.sql
```

### F. Kunci Settings di Tabel `settings`

```
platform_name
platform_logo
platform_color
platform_whatsapp

mail_user
mail_pass              ← enkripsi sebelum simpan
mail_from_name
mail_footer
digest_send_time

payment_bank_name
payment_bank_holder
payment_bank_number
payment_qris_image
payment_monthly_price
payment_max_upload_mb
payment_allowed_formats
payment_magic_link_expiry_hours

grace_warning_day_1    ← default 7
grace_warning_day_2    ← default 3
grace_warning_day_3    ← default 1
grace_yellow_days      ← default 7
grace_red_days         ← default 3
trial_duration_days    ← default 14
renewal_duration_days  ← default 30

timezone               ← default Asia/Jakarta
language               ← default id

maintenance_mode       ← default 0
maintenance_message

notification_email_reminder  ← default 1
notification_digest          ← default 1
notification_inapp           ← default 1
```

### F. File Dimodifikasi Task F

```
routes/web.php     → tambah GET /saas/settings
routes/api.php     → tambah POST /api/saas/settings/save
                      tambah POST /api/saas/settings/test-email
                      tambah POST /api/saas/settings/upload
src/views/SaaS/sidebar.php → tambah menu Settings
```

---

## TASK G — Laporan Billing (Deferred)

> Halaman laporan riwayat pembayaran semua tenant untuk SaaS Owner.
> **Status: Ditunda — dikerjakan setelah Task A–F selesai.**

- `[x]` Riwayat semua pembayaran per tenant
- `[x]` Filter: bulan, tahun, status pembayaran
- `[x]` Export ke CSV/Excel

---

## TASK H — Redesign Template Email HTML

> Redesign seluruh template email dengan tampilan modern, responsif, dan konsisten.
> **Status: Selesai**

### H1. Spesifikasi Template

- `[x]` Target penerima: Bos Tenant (pemilik bisnis), tone formal dan profesional
- `[x]` Desain visual: background gradasi diagonal `#0ea5e9` → `#0284c7` → `#0f172a`
- `[x]` Card: putih, ditengah (centered), max-width 600px, border-radius 16px
- `[x]` Kompatibilitas: semua style menggunakan inline CSS agar didukung seluruh email client
- `[x]` Logo: diambil dari `settings.mail_logo` dengan fallback teks "Peace Seafood WMS"
- `[x]` CTA button: dengan gradasi `#38bdf8` → `#0ea5e9` dan teks putih bold

### H2. Struktur Template & Jenis Email

-- `[x]` Sediakan template dasar (base layout) di `src/views/emails/` yang bisa di-extend oleh jenis email lainnya
-- `[x]` Implementasi template untuk jenis-jenis email berikut:

- `grace_reminder` (reminder H-7, H-3, dan H-1)
- `magic_link` approval
- `daily_digest` untuk SaaS Owner
- `test_email` untuk uji coba SMTP
- `suspend_notify` untuk pemberitahuan suspend tenant

### H3. Integrasi & Perbaikan Encoding

- `[x]` Tambahkan `$mail->CharSet = PHPMailer::CHARSET_UTF8` di `src/utils/Email.php` untuk memastikan subjek dan konten mendukung UTF-8

### H4. File Baru Task H

```
src/views/emails/base_layout.php
src/views/emails/grace_reminder.php
src/views/emails/magic_link_approval.php
src/views/emails/daily_digest.php
src/views/emails/test_email.php
src/views/emails/suspend_notify.php
```

### H5. File Dimodifikasi Task H

```
src/utils/Email.php → set CHARSET_UTF8 & integrasikan pemanggilan HTML layout baru
```

---

## 2. Ruang Lingkup Pengerjaan (Scope)

- **Target Area:** Seluruh direktori proyek `c:\xamppp\htdocs\peace_seafood`
- **Komponen Dibekukan (Jangan Disentuh):**
  - Skema tabel: `gudang`, `users`, `transaksi`, `stok`
  - Alur JWT middleware & autentikasi dasar
- **Stack:** PHP 8.x Custom MVC, MySQL, Alpine.js, Axios, CSS Variables
