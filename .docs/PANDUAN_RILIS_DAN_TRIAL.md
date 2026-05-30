# Panduan Rilis & Trial

Panduan ringkas untuk go-live dan alur trial Peace Seafood.

---

## 1. Fitur Trial Dinamis (Uji Coba Gratis)

Untuk memikat para Bos Gudang agar tertarik menggunakan sistem ini, kita menerapkan strategi **"Gratis di Awal, Berbayar di Akhir"**. Anda selaku pemilik platform dapat menentukan durasi trial yang berbeda-beda untuk tiap Bos secara fleksibel.

### A. Alur Trial
1. Saat gudang baru dibuat, tentukan jumlah hari trial.
2. Simpan tanggal kedaluwarsa ke kolom `subscription_until` pada tabel `gudang`.
3. Jika masa trial habis, akses diblokir sampai langganan diperpanjang.

### B. Perilaku Saat Trial Habis
- Login dan request API untuk gudang tersebut harus dicek terhadap `subscription_until`.
- Jika expired, tampilkan lock screen dan tolak transaksi baru dengan kode `402`.

### C. Perpanjangan Langganan
Perbarui `subscription_until` ke tanggal baru di masa depan. Akses aktif kembali setelah nilai disimpan.

---

## 2. Checklist Go-Live

### Langkah 1: Backup database
```bash
mysqldump -u root -p peace_seafood > backup_dev_peace_seafood.sql
```

### Langkah 2: Bersihkan data uji coba
```bash
php database/clean_data.php
```

### Langkah 3: Set `.env` produksi
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://nama-domain-anda.com

# Database Produksi
DB_HOST=localhost
DB_NAME=peace_seafood_prod
DB_USER=user_db_produksi
DB_PASS=password_db_yang_kuat

# SMTP Gmail Gratis untuk Sistem
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=sistem.peaceseafood@gmail.com
MAIL_PASSWORD=app_password_dari_google_anda
MAIL_FROM_ADDRESS=sistem.peaceseafood@gmail.com
MAIL_FROM_NAME="Peace Seafood System"
```

### Langkah 4: Pastikan akun owner siap
Pastikan akun admin utama/owner sudah tersedia sebelum rilis.

---

## 3. Ringkasan

- Trial dikontrol lewat `subscription_until`.
- Go-live butuh backup, clean data, dan `.env` produksi yang benar.
- Rujukan implementasi tetap ada di backend auth dan middleware langganan.
