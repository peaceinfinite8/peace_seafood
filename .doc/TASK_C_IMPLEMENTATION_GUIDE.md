# Task C: Grace Period Alert & Notification System
## Implementation Guide

### 📋 Overview

Task C menambahkan sistem peringatan grace period dan notifikasi real-time untuk memantau masa aktif gudang tenant. Sistem ini mencakup:

1. **Grace Period Banner** - Banner peringatan di dashboard saat mendekati kadaluarsa
2. **Email Reminders** - Email otomatis H-7, H-3, H-1 sebelum expired
3. **Bell Icon Notifications** - Notifikasi real-time dengan polling setiap 60 detik
4. **Role-based Notifications** - Notifikasi berbeda untuk setiap role
5. **Daily Digest Email** - Ringkasan harian untuk SaaS Owner

---

## 🚀 Installation Steps

### 1. Run Database Migration

Jalankan migration untuk membuat tabel baru yang dibutuhkan:

```bash
# Masuk ke direktori database migrations
cd database/migrations

# Jalankan migration (pastikan MySQL sudah running)
mysql -u root -p peace_seafood < 20260605_add_grace_period_tables.sql
```

**Tabel yang dibuat:**
- `grace_email_log` - Log email reminder yang sudah terkirim
- Modifikasi `notifikasi` - Tambah kolom `action_url`

**Settings yang ditambahkan:**
- `grace_yellow_days` (default: 7)
- `grace_red_days` (default: 3)
- `grace_warning_day_1` (default: 7)
- `grace_warning_day_2` (default: 3)
- `grace_warning_day_3` (default: 1)
- `notification_email_reminder` (default: 1)
- `notification_digest` (default: 1)
- `notification_inapp` (default: 1)
- `digest_send_time` (default: '08:00')
- `timezone` (default: 'Asia/Jakarta')
- `platform_whatsapp` (default: '628123456789')

### 2. Setup Cron Job untuk Email Reminders

Tambahkan cron job untuk menjalankan pengecekan grace period setiap hari:

```bash
# Edit crontab
crontab -e

# Tambahkan baris berikut (jalankan setiap hari jam 8 pagi)
0 8 * * * cd /path/to/peace_seafood && php cli/check_grace_reminders.php >> /path/to/logs/grace_reminders.log 2>&1
```

**Atau test manual:**

```bash
cd /path/to/peace_seafood
php cli/check_grace_reminders.php
```

Output yang diharapkan:
```
==============================================
Grace Period Reminder Check
Started at: 2026-06-05 08:00:00
==============================================

✓ Email reminders enabled
Checking all active gudang subscriptions...

✅ SUCCESS
   Emails sent: 3
   In-app notifications created for: 3 gudang

==============================================
Completed at: 2026-06-05 08:00:15
==============================================
```

### 3. Konfigurasi Email (jika belum)

Pastikan email sudah dikonfigurasi di `.env`:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your-email@gmail.com
MAIL_PASS=your-app-password
MAIL_FROM=your-email@gmail.com
MAIL_FROM_NAME="Peace Seafood WMS"
```

---

## 🎯 Features

### 1. Grace Period Banner

**Lokasi:** Muncul di bawah navbar di semua halaman (untuk tenant yang mendekati expired)

**Warna Banner:**
- **Kuning** (#F59E0B): Sisa hari ≤ 7 dan > 3
- **Merah** (#EF4444): Sisa hari ≤ 3

**Behavior:**
- Tidak bisa di-dismiss (permanen)
- Tidak muncul untuk SaaS Owner
- Tidak muncul jika sisa hari > threshold kuning
- Tombol "Hubungi via WA" langsung ke WhatsApp platform

**API Endpoint:**
```
GET /api/grace-period/banner
Response: {
  "success": true,
  "data": {
    "show": true,
    "color": "red",
    "days": 2,
    "message": "Akses gudang akan terkunci dalam 2 hari",
    "whatsapp": "628123456789"
  }
}
```

### 2. Email Reminder System

**Trigger Points:**
- **H-7**: "Masa aktif gudang Anda tersisa 7 hari"
- **H-3**: "⚠️ 3 hari lagi — Gudang Anda akan terkunci"
- **H-1**: "🚨 Besok akses gudang Anda terkunci!"

**Anti-Spam Mechanism:**
- Satu email per trigger per gudang per hari
- Dicatat di tabel `grace_email_log`
- Cek duplikasi berdasarkan `id_gudang`, `trigger`, dan `DATE(sent_at)`

**Penerima:**
- User dengan role `bos` dari gudang yang bersangkutan
- Email diambil dari tabel `users`

### 3. Bell Icon Notifications

**Lokasi:** Navbar kanan (menggantikan notifikasi lama)

**Features:**
- **Polling**: Setiap 60 detik cek unread count
- **Badge**: Menampilkan jumlah notifikasi belum dibaca
- **Dropdown**: List notifikasi dengan icon per tipe
- **Mark as Read**: Klik notifikasi atau tombol "Tandai semua dibaca"
- **Action URL**: Redirect otomatis saat klik notifikasi
- **History**: Notifikasi yang sudah dibaca tetap tersimpan

**Disabled Saat:**
- SaaS Owner sedang mode impersonasi

**API Endpoints:**
```
GET /api/notifications/unread-count
Response: { "success": true, "data": { "count": 5 } }

GET /api/notifications?limit=50&offset=0
Response: { "success": true, "data": [...] }

POST /api/notifications/{id}/read
Response: { "success": true, "message": "Notifikasi ditandai sudah dibaca" }

POST /api/notifications/mark-all-read
Response: { "success": true, "message": "Semua notifikasi ditandai sudah dibaca" }
```

### 4. Notifikasi Per Role

**Grace Period Notifications:**

| Role | H-7 | H-3 | H-1 |
|------|-----|-----|-----|
| saas_owner | ❌ (digest) | ❌ (digest) | ❌ (digest) |
| bos | ✅ | ✅ | ✅ |
| super_admin | ❌ | ✅ | ✅ |
| admin | ❌ | ✅ | ✅ |
| financial_admin | ❌ | ✅ | ❌ |
| checker | ❌ | ✅ | ✅ |
| helper | ❌ | ✅ | ✅ |
| viewer | ❌ | ✅ | ✅ |

**Cara Membuat Notifikasi Baru:**

```php
use App\Services\Shared\NotificationService;

// Notifikasi untuk user tertentu
NotificationService::create(
    idUser: 123,
    tipe: 'grace_period',
    judul: 'Masa Aktif Tersisa 3 Hari',
    pesan: 'Gudang Anda akan terkunci dalam 3 hari',
    referenceId: null,
    referenceTipe: null,
    actionUrl: '/settings'
);

// Notifikasi untuk semua user dengan role tertentu di gudang
NotificationService::createForRole(
    idGudang: 5,
    role: 'admin',
    tipe: 'stok',
    judul: 'Stok Hampir Habis',
    pesan: 'Produk Ikan Salmon tinggal 5kg',
    referenceId: 42,
    referenceTipe: 'produk',
    actionUrl: '/stok'
);

// Notifikasi untuk semua user di gudang
NotificationService::createForGudang(
    idGudang: 5,
    tipe: 'payment',
    judul: 'Akses Dipulihkan',
    pesan: 'Pembayaran Anda telah disetujui',
    referenceId: null,
    referenceTipe: null,
    actionUrl: '/dashboard'
);
```

**Tipe Notifikasi:**
- `grace_period` - Peringatan masa aktif
- `payment` - Terkait pembayaran/perpanjangan
- `retur` - Retur baru atau status retur
- `stok` - Stok habis/hampir habis
- `user` - User baru ditambahkan
- `default` - Lainnya

### 5. Daily Digest Email

**Penerima:** SaaS Owner saja

**Konten:**
- Total tenant count
- Active tenant count
- Tenants expiring in 7 days
- Tenants expired today
- New signups today
- Payment requests pending
- Recent activities (10 terakhir)

**Konfigurasi:**
- Toggle ON/OFF: `settings.notification_digest`
- Jam kirim: `settings.digest_send_time` (default 08:00)

**Manual Trigger (untuk testing):**
```php
// Implementasi akan dibuat saat Task F (Platform Settings)
// atau bisa dipanggil manual via script PHP
```

---

## 🧪 Testing

### Test Grace Period Banner

1. Login sebagai `bos` atau role lain (bukan `saas_owner`)
2. Update `subscription_until` gudang ke 5 hari dari sekarang:
   ```sql
   UPDATE gudang SET subscription_until = DATE_ADD(NOW(), INTERVAL 5 DAYS) WHERE id = 1;
   ```
3. Refresh dashboard - banner merah harus muncul
4. Update ke 10 hari:
   ```sql
   UPDATE gudang SET subscription_until = DATE_ADD(NOW(), INTERVAL 10 DAYS) WHERE id = 1;
   ```
5. Refresh - banner kuning harus muncul

### Test Email Reminders

1. Set `subscription_until` ke H-7, H-3, atau H-1
2. Jalankan CLI:
   ```bash
   php cli/check_grace_reminders.php
   ```
3. Cek email masuk (atau `storage/logs/email_mock.log` jika `APP_ENV=local`)
4. Verifikasi log di tabel `grace_email_log`

### Test Bell Notifications

1. Login sebagai user biasa
2. Lihat bell icon di navbar
3. Badge harus update setiap 60 detik
4. Klik bell - dropdown muncul
5. Klik notifikasi - mark as read + redirect
6. Test "Tandai semua dibaca"

### Test Impersonation Mode

1. Login sebagai `saas_owner`
2. Impersonate tenant user
3. Bell icon harus disabled (tidak polling)

---

## 🔧 Troubleshooting

### Banner tidak muncul

**Cek:**
1. Apakah user memiliki `id_gudang`?
2. Apakah `subscription_until` sudah diset?
3. Apakah sisa hari masuk threshold?
4. Inspect console browser untuk error JavaScript

### Email tidak terkirim

**Cek:**
1. Konfigurasi SMTP di `.env`
2. Setting `notification_email_reminder` = 1
3. Log di `storage/logs/` atau error log server
4. Firewall memblokir port 587?

### Bell icon tidak polling

**Cek:**
1. Apakah `notification_inapp` = 1 di settings?
2. Inspect Network tab - ada request ke `/api/notifications/unread-count`?
3. Apakah user dalam mode impersonation?
4. Console error JavaScript?

### Cron job tidak jalan

**Cek:**
1. Apakah crontab sudah benar?
2. Path PHP executable benar?
3. Permission file `cli/check_grace_reminders.php` (harus executable)
4. Log cron di `/var/log/cron` atau custom log path

---

## 📝 Configuration Settings

Semua settings dapat diubah via:
1. Database: tabel `settings` (kolom `kunci`, `nilai`)
2. UI: Menu Settings (akan ada di Task F)

**Default Values:**

| Kunci | Nilai Default | Keterangan |
|-------|---------------|------------|
| `grace_yellow_days` | 7 | Threshold banner kuning |
| `grace_red_days` | 3 | Threshold banner merah |
| `grace_warning_day_1` | 7 | Kirim email H-7 |
| `grace_warning_day_2` | 3 | Kirim email H-3 |
| `grace_warning_day_3` | 1 | Kirim email H-1 |
| `notification_email_reminder` | 1 | Toggle email reminder |
| `notification_digest` | 1 | Toggle digest harian |
| `notification_inapp` | 1 | Toggle notifikasi in-app |
| `digest_send_time` | 08:00 | Jam kirim digest |
| `timezone` | Asia/Jakarta | Timezone sistem |
| `platform_whatsapp` | 628123456789 | Nomor WA support |

---

## ✅ Verification Checklist

Sebelum menganggap Task C selesai, pastikan:

- [ ] Migration berhasil dijalankan
- [ ] Grace banner muncul saat mendekati expired
- [ ] Email reminder terkirim (test manual via CLI)
- [ ] Bell icon menampilkan badge unread count
- [ ] Polling bekerja setiap 60 detik
- [ ] Dropdown notifikasi dapat dibuka dan ditutup
- [ ] Mark as read berfungsi
- [ ] Mark all as read berfungsi
- [ ] Notifikasi tetap tersimpan setelah dibaca
- [ ] Action URL redirect bekerja
- [ ] Impersonation mode disable polling
- [ ] Cron job dapat dijalankan manual
- [ ] CSS dengan prefix `gpa-` tidak konflik
- [ ] Alpine.js component mandiri (tidak nested)

---

## 🎓 Next Steps

Setelah Task C selesai, lanjut ke:
- **Task F**: Platform Settings Manager (panel konfigurasi untuk semua settings di atas)
- **Task A**: Lock Screen & Sistem Pembayaran Manual
- **Task B**: Onboarding Wizard Tenant Baru

---

## 📚 References

**Files Created:**
- `database/migrations/20260605_add_grace_period_tables.sql`
- `src/services/WMS/GracePeriodService.php`
- `src/services/Shared/NotificationService.php`
- `src/controllers/GracePeriodController.php`
- `src/views/partials/grace_banner.php`
- `src/views/partials/notification_bell.php`
- `src/views/emails/grace_reminder.php`
- `src/views/emails/daily_digest.php`
- `src/views/emails/suspend_notify.php`
- `cli/check_grace_reminders.php`

**Files Modified:**
- `routes/api.php`
- `src/views/layouts/app.php`
- `src/controllers/NotifikasiController.php`

---

*Dokumentasi dibuat: 5 Juni 2026*
*Task C - Peace Seafood SaaS WMS*
