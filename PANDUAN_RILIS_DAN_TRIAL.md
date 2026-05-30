# PANDUAN RILIS PRODUKSI & SISTEM TRIAL (UJI COBA GRATIS)
## PEACE SEAFOOD — PLATFORM SaaS MULTI-TENANT

Dokumen ini adalah panduan resmi bagi Anda selaku **SaaS Owner / Developer** saat ingin mempublikasikan aplikasi Peace Seafood ke server produksi serta cara mengelola fitur **Trial (Uji Coba Gratis)** secara dinamis untuk para Bos Gudang.

---

## 1. Fitur Trial Dinamis (Uji Coba Gratis)

Untuk memikat para Bos Gudang agar tertarik menggunakan sistem ini, kita menerapkan strategi **"Gratis di Awal, Berbayar di Akhir"**. Anda selaku pemilik platform dapat menentukan durasi trial yang berbeda-beda untuk tiap Bos secara fleksibel.

### A. Cara Kerja Teknis di Database
Masa trial pada dasarnya adalah "Masa Sewa Gratis" yang tanggal kedaluwarsanya dihitung otomatis saat Anda mendaftarkan gudang baru.
1. Saat Anda mendaftarkan Gudang baru untuk seorang Bos, Anda memasukkan jumlah hari trial (misalnya: `7`, `14`, atau `30` hari).
2. Sistem backend akan otomatis menghitung tanggal kedaluwarsa sewa dan menyimpannya di kolom `subscription_until` pada tabel `gudang`:
   $$\text{subscription\_until} = \text{Tanggal Hari Ini} + \text{Jumlah Hari Trial}$$

### B. Alur Kunci Dashboard (Saat Trial Habis)
Sistem backend akan memeriksa tanggal `subscription_until` setiap kali pengguna dari gudang tersebut melakukan login atau request API.
* **Selama Masa Trial Aktif**: Semua fitur (kasir, timbangan, keuangan, stok) terbuka penuh tanpa batasan.
* **Begitu Masa Trial Habis (Melewati Tanggal)**:
  * Pengguna (Bos maupun Karyawan) yang mencoba masuk akan langsung dihadang oleh **Layar Kunci Premium (Lock Screen UI)**.
  * Layar kunci berdesain elegan dengan warna merah/emas premium yang bertuliskan:
    > 🔒 **Masa Uji Coba Gratis Anda Telah Berakhir!**
    > Halo Bos **[Nama Bos]**, terima kasih telah mencoba Peace Seafood di gudang **[Nama Gudang]**.
    > Untuk melanjutkan pengelolaan gudang secara profesional dan mengaktifkan akses penuh kembali, silakan lakukan perpanjangan sewa dengan menghubungi Developer kami:
    > 💬 **Hubungi WhatsApp Developer: [Nomor WhatsApp Anda]**
  * Seluruh fungsi API backend untuk gudang tersebut akan otomatis menolak transaksi baru (kembalian kode `402 Payment Required`) demi keamanan.

### C. Cara Memperpanjang / Mengaktifkan Langganan
Jika Bos tersebut puas dengan masa trial dan memutuskan untuk membayar sewa bulanan/tahunan:
1. Anda selaku **SaaS Owner** masuk ke database atau Super Dashboard Anda.
2. Anda cukup memperbarui kolom `subscription_until` pada gudang tersebut ke tanggal baru di masa depan (misal ditambah 30 hari atau 365 hari).
3. Akun gudang Bos tersebut akan otomatis **aktif kembali secara instan** tanpa perlu melakukan instalasi ulang atau kehilangan data lama mereka!

---

## 2. Checklist Langkah demi Langkah Sebelum Publish (Go-Live)

Ikuti langkah-langkah berikut ketika Anda sudah siap merilis aplikasi Peace Seafood ini ke server hosting/produksi agar sistem bersih dari data uji coba:

### Langkah 1: Backup Database Pengembangan
Sebelum mengosongkan apa pun, buat salinan database lokal Anda sebagai cadangan:
```bash
# Ekspor database lokal Anda untuk cadangan aman
mysqldump -u root -p peace_seafood > backup_dev_peace_seafood.sql
```

### Langkah 2: Bersihkan Data Uji Coba (Database Wipe)
Untuk membersihkan semua data transaksi, supplier dummy, produk dummy, dan user testing, jalankan script pembersih bawaan di terminal server Anda:
```bash
php database/clean_data.php
```
*Script ini akan mengosongkan semua tabel transaksi tetapi **tetap menyisakan struktur database** yang bersih dan siap diisi oleh pengguna baru.*

### Langkah 3: Konfigurasi File `.env` Produksi
Ubah file konfigurasi `.env` di server produksi Anda agar aman dari peretas:
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

### Langkah 4: Daftarkan Diri Anda Sebagai `saas_owner`
Masukkan akun Anda sendiri di tabel `users` dengan role `saas_owner` langsung melalui database. Akun inilah yang nantinya Anda gunakan untuk mengelola seluruh gudang dan masa trial para Bos.

---

## 3. Ringkasan Keunggulan Bisnis SaaS Anda
Dengan alur trial dinamis ini, Anda memiliki senjata pemasaran yang sangat ampuh:
1. **Daya Tarik Tinggi**: Bos tidak takut mencoba karena gratis di awal tanpa komitmen kartu kredit.
2. **Ketergantungan Data**: Setelah Bos mengisi data ikan, supplier, dan pelanggan mereka selama masa trial, mereka akan merasa "sayang" jika data tersebut hilang saat trial habis. Hal ini mendorong mereka untuk **pasti membayar sewa** demi mempertahankan data penting bisnis mereka.
3. **Kontrol Penuh di Tangan Anda**: Anda bebas memberikan trial 3 hari untuk yang ragu-ragu, atau 30 hari untuk pelanggan spesial, semuanya bisa Anda atur secara dinamis!
