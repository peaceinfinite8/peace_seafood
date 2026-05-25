# Peace Seafood

Peace Seafood adalah web application berbasis PHP untuk manajemen gudang dan operasional seafood. Aplikasi ini mencakup stok, penjualan, penitipan, retur, keuangan, master data, laporan, dan pengaturan sistem.

## Ringkasan

- Backend PHP 8.2+
- Autentikasi JWT
- Akses berbasis role
- Dashboard operasional
- Export laporan PDF dan Excel
- Dibuat untuk environment lokal XAMPP dan MySQL

## Fitur Utama

### Operasional Gudang

- Stok dan inventory
- Input stok masuk
- Timbangan dan susut
- History stok

### Penjualan

- Daftar nota penjualan
- Buat nota penjualan
- Finalize atau cancel nota

### Penitipan

- Terima titipan baru
- Catat penjualan titipan
- Settlement titipan

### Retur

- Retur stok
- Retur piutang
- Approve dan reject retur

### Keuangan

- Hutang dan piutang
- Pembayaran
- Biaya operasional

### Master Data

- Supplier
- Pembeli
- Jenis ikan
- Produk
- Riwayat harga

### Laporan dan Settings

- Laporan stok, penjualan, dan keuangan
- Export PDF dan Excel
- Manajemen user dan gudang
- Pengaturan aplikasi

## Teknologi

- PHP 8.2+
- MySQL
- Composer
- Firebase JWT
- Dompdf
- PhpSpreadsheet
- Monolog
- Chart.js di frontend

## Persyaratan Sistem

- PHP 8.2 atau lebih baru
- MySQL/MariaDB
- Composer
- XAMPP atau web server setara
- Ekstensi PHP yang umum dibutuhkan untuk project PHP modern, seperti PDO MySQL, mbstring, openssl, fileinfo, dan zip

## Dev Tools (Static Analysis)

- PHPStan config: `phpstan.neon.dist` (bootstrap: `phpstan-bootstrap.php`)
- Rector config: `rector.php`

Jalankan (butuh PHP 8.2+ sesuai `composer.json`):

`php vendor/bin/phpstan analyse -c phpstan.neon.dist`

`php vendor/bin/rector process`

## Instalasi Lokal

1. Clone atau salin project ke folder web server, misalnya `c:\xampp\htdocs\peace_seafood`.
2. Jalankan instalasi dependency:

	```bash
	composer install
	```

3. Salin file `.env.example` menjadi `.env`.
4. Sesuaikan konfigurasi database dan URL aplikasi di file `.env`.
5. Buat database MySQL bernama `peace_seafood` atau sesuaikan dengan `DB_NAME`.
6. Import file database berikut bila tersedia di environment kamu:
	- `database/schema.sql`
	- `database/seeders/seeder.sql`
7. Pastikan Apache dan MySQL di XAMPP sudah berjalan.
8. Buka aplikasi di browser:

	`http://localhost:8080/peace_seafood/`

   Jika ingin mengelola database, buka phpMyAdmin di:

	`http://localhost/phpmyadmin/`

## Contoh Konfigurasi .env

```env
APP_NAME=Peace Seafood
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=Asia/Jakarta

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=peace_seafood
DB_USER=root
DB_PASSWORD=

JWT_SECRET=change-this-to-a-random-secret-key-min-32-chars
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600

UPLOAD_MAX_SIZE=5242880
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf

EXPORT_MAX_ROWS=10000

LOG_CHANNEL=single
LOG_LEVEL=debug

SESSION_TIMEOUT_MINUTES=30
CORS_ORIGIN=http://localhost
```

## Akses Aplikasi

| Halaman | URL |
|---|---|
| Login | http://localhost:8080/peace_seafood/ |
| Dashboard | http://localhost:8080/peace_seafood/dashboard |
| Stok | http://localhost:8080/peace_seafood/stok |
| Penjualan | http://localhost:8080/peace_seafood/penjualan |
| Penitipan | http://localhost:8080/peace_seafood/penitipan |
| Retur | http://localhost:8080/peace_seafood/retur |
| Keuangan | http://localhost:8080/peace_seafood/keuangan |
| Laporan | http://localhost:8080/peace_seafood/laporan |
| Master Data | http://localhost:8080/peace_seafood/master-data |
| Settings | http://localhost:8080/peace_seafood/settings |
| API Base | http://localhost:8080/peace_seafood/api/ |

## Akun Demo Lokal

Gunakan akun berikut untuk environment development lokal.

| Role | Email | Password |
|---|---|---|
| Bos | bos@example.com | bos123 |
| Admin Gudang A | admin@example.com | admin123 |
| Checker Gudang A | checker@example.com | checker123 |
| Admin Gudang B | admin2@example.com | admin2 |

## Role dan Hak Akses

### Bos

- Akses semua gudang
- Approve dan reject retur
- Kelola settings, user, dan gudang
- Export laporan
- Ubah harga produk

### Admin

- Input dan kelola stok masuk
- Buat nota penjualan
- Kelola penitipan
- Kelola retur
- Kelola keuangan
- Kelola master data
- Lihat laporan

### Checker

- Input stok masuk
- Input timbangan dan susut
- Input retur stok
- Lihat stok dan laporan

## Struktur Folder

```text
config/        Konfigurasi aplikasi, database, dan role
database/      Schema dan seed data
docs/          Dokumentasi tambahan
public/        Entry point, aset CSS, JS, dan icon
routes/        Routing web dan API
src/           Controllers, models, services, middleware, utils, views
storage/       Cache, export, log, dan upload
vendor/        Dependency dari Composer
```

## Autentikasi dan Keamanan

- Login menggunakan JWT
- Token disimpan di localStorage
- Akses endpoint dilindungi middleware auth dan role
- Security headers dasar diaktifkan pada entry point aplikasi

## Endpoint API Utama

- Auth: `/api/auth/login`, `/api/auth/logout`, `/api/auth/profile`
- Dashboard: `/api/dashboard`
- Stok: `/api/stok`, `/api/stok/masuk`, `/api/stok/timbang`, `/api/stok/history`
- Penjualan: `/api/penjualan`
- Penitipan: `/api/penitipan`
- Retur: `/api/retur`
- Keuangan: `/api/keuangan/hutang-piutang`, `/api/keuangan/bayar`, `/api/keuangan/biaya`
- Laporan: `/api/laporan/stok`, `/api/laporan/penjualan`, `/api/laporan/keuangan`
- Master data: `/api/master/supplier`, `/api/master/pembeli`, `/api/master/jenis-ikan`, `/api/master/produk`, `/api/master/harga`
- Settings: `/api/settings`, `/api/settings/users`, `/api/settings/gudang`

## Catatan Pengembangan

- Jalankan project melalui document root yang mengarah ke folder `public/` atau gunakan konfigurasi rewrite yang sesuai.
- Pastikan nilai `APP_URL` sesuai dengan URL akses lokal tanpa port, misalnya `http://localhost`.
- Ubah `JWT_SECRET` sebelum dipakai di environment non-lokal.
- Jika perlu akses database, gunakan phpMyAdmin di `http://localhost/phpmyadmin/`.
- Dokumentasi halaman dan endpoint dapat dilihat di `docs/pages_map.json`.

## Lisensi

Project ini ditujukan untuk kebutuhan internal dan development. Sesuaikan lisensi sesuai kebijakan pemilik project.
