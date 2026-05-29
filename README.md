# Peace Seafood — README untuk Programmer

Ini README singkat yang menjelaskan tujuan project, alur pendaftaran `SAAS_OWNER`, dan langkah teknis untuk programmer agar fitur pendaftaran boss (pemilik usaha) bekerja otomatis seperti yang Anda inginkan.

## Tujuan proyek
Project ini adalah aplikasi bisnis (SaaS) untuk kelola operasi seafood: stok, penjualan, penitipan, retur, laporan, dan keuangan. Tujuan update ini: menambahkan alur pendaftaran bisnis baru di mana `SAAS_OWNER` (akun yang memiliki hak approve) menerima permintaan pendaftaran dan sistem otomatis membuat credential login untuk boss lalu mengirimkannya lewat email (Gmail).

## Inti alur yang diminta
1. Owner (admin SaaS) akan menerima email boss yang ingin mendaftar.
2. Admin memasukkan email boss tersebut ke entri/record `SAAS_OWNER` di sistem.
3. Saat email boss di-input, sistem otomatis:
   - Membuat akun user untuk boss (generate password aman),
   - Menyimpan hash password di tabel `users`/`saas_owners`,
   - Mengirim email ke alamat boss berisi kredensial login (email + password) dan instruksi awal.
4. Boss dapat login menggunakan kredensial yang dikirim.

## Rekomendasi implementasi (tempat menaruh kode)
- Model / migration: buat tabel baru `saas_owners` atau gunakan `users` dengan kolom `role = owner`.
  - Lihat folder migrasi: [database/migrations](database/migrations)
  - Contoh migrasi baru: `database/migrations/20260529_create_saas_owners.sql` (buat jika belum ada).

- Service: implementasikan logika pembuatan akun dan pengiriman email di [src/services](src/services), contoh `SaasService.php` yang memanggil `AuthService` / `UserService` dan [src/utils/Email.php](src/utils/Email.php).

- Controller & route: buat controller `SaasController` dan tambahkan route di [routes/web.php](routes/web.php) atau [routes/api.php](routes/api.php) untuk endpoint admin meng-input email boss.

## Detail teknis langkah demi langkah
1. Migration: tambah tabel `saas_owners` (id, email, created_by, created_at, status).
2. Model: buat `src/models/SaasOwner.php` untuk akses DB.
3. Service - `SaasService::createOwner($email, $creatorId)`:
   - Validasi email (format, uniqueness).
   - Generate password kuat (contoh: random 12+ chars) dan hash dengan `password_hash()`.
   - Buat record user (atau saas_owner) di DB dan set role `owner`.
   - Kirim email via `Email::send($to, $subject, $body)` dengan kredensial SMTP/Gmail.
   - Log event di activity log.

4. Email: gunakan SMTP Gmail (disarankan App Password) atau service mail (SendGrid/Mailgun). Simpan kredensial di `.env`:

```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=youremail@gmail.com
MAIL_PASS=app-password-or-token
MAIL_FROM=youremail@gmail.com
MAIL_FROM_NAME="Peace Seafood"
```

5. UI / Admin flow:
   - Halaman admin untuk input email boss, tombol `Create owner`.
   - Setelah submit, panggil endpoint yang memicu `SaasService::createOwner`.

6. Email content (saran):
   - Subjek: `Akun Anda untuk Peace Seafood (nama_toko)`
   - Isi: link login, username (email), password sementara, instruksi ganti password setelah login, dan kontak support.

7. Keamanan & operasional:
   - Jangan kirim password plain kecuali memang diperlukan; alternatif: kirim link aktivasi ber-OTP yang mengarahkan boss set password.
   - Jika tetap kirim password, gunakan App Password Gmail, serta simpan hanya hash password di DB.
   - Catat dan limitasi percobaan pembuatan akun untuk mencegah spam.

## File & lokasi kerja utama (quick links)
- Routes: [routes/web.php](routes/web.php) atau [routes/api.php](routes/api.php)
- Controllers: [src/controllers](src/controllers)
- Services: [src/services](src/services)
- Utils Email: [src/utils/Email.php](src/utils/Email.php)
- Models: [src/models](src/models)
- Migrations: [database/migrations](database/migrations)

## Contoh pseudocode (di `SaasService`)
```php
function createOwner(string $email, int $creatorId) {
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Invalid email');
  // generate password
  $plain = bin2hex(random_bytes(8)); // 16 chars
  $hash = password_hash($plain, PASSWORD_DEFAULT);
  // create user record (example using DB helper)
  $userId = DB::insert('users', [
    'email' => $email,
    'password' => $hash,
    'role' => 'owner',
    'created_by' => $creatorId,
  ]);
  // send email
  $body = "Akun Anda: $email\nPassword sementara: $plain\nSilakan ganti password setelah login.";
  Email::send($email, 'Akun Peace Seafood', $body);
  return $userId;
}
```

> Catatan: pseudocode di atas sederhana untuk kejelasan. Untuk produksi gunakan link aktivasi, token yang kedaluwarsa, dan jangan kirim password plaintext kecuali memang aturan internal mengizinkan.

## Cara test manual cepat
1. Pastikan `MAIL_*` di `.env` terisi dan `composer install` sudah dijalankan.
2. Jalankan migrasi: lihat `database/run_setup.php` atau jalankan query SQL di `database/migrations`.
3. Panggil endpoint admin untuk create owner (curl / Postman).

Contoh curl:
```bash
curl -X POST "http://localhost/saas/create-owner" -d "email=boss@example.com" -u admin:adminpassword
```

## Checklist sebelum produksi
- Gunakan `App Password` untuk Gmail atau provider SMTP yang terverifikasi.
- Terapkan rate-limit dan verifikasi input.
- Pastikan audit log mencatat siapa yang membuat owner baru.
- Uji alur aktivasi akun dan reset password.

---
Butuh saya implementasikan endpoint dan `SaasService` secara langsung sekarang? Saya bisa buat kerangka file (migration, model, service, controller, route) jika Anda mau — beri konfirmasi dan saya kerjakan.
