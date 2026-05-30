# 🚀 QUICK START - Peace Seafood (Port 8080)

**Port**: 8080  
**Status**: ✅ Ready  

---

## ⚡ CARA TERCEPAT MENJALANKAN

### **Windows**
```bash
# Double-click atau jalankan dari terminal
start-server.bat
```

### **Linux/Mac**
```bash
# Berikan permission execute
chmod +x start-server.sh

# Jalankan
./start-server.sh
```

### **Manual (Semua OS)**
```bash
php -S localhost:8080 -t public
```

---

## 🌐 AKSES APLIKASI

Setelah server berjalan, buka browser dan akses:

```
http://localhost:8080/
```

---

## 📋 CHECKLIST SEBELUM MULAI

- [ ] PHP 8.2+ terinstall
- [ ] MySQL/MariaDB berjalan
- [ ] Database `peace_seafood` sudah dibuat
- [ ] File `.env` sudah dikonfigurasi
- [ ] Port 8080 tidak digunakan aplikasi lain

---

## 🔧 SETUP DATABASE (Jika Belum)

```bash
# 1. Buat database
mysql -u root -p
CREATE DATABASE peace_seafood CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# 2. Import schema
mysql -u root -p peace_seafood < database/schema.sql

# 3. (Optional) Import seeder data
mysql -u root -p peace_seafood < database/seeders/seeder.sql
```

---

## 👤 LOGIN DEFAULT

Setelah import seeder:

**Super Admin:**
- Email: `superadmin@peace.com`
- Password: `password`

**Bos:**
- Email: `bos@peace.com`
- Password: `password`

**Admin:**
- Email: `admin@peace.com`
- Password: `password`

**Checker:**
- Email: `checker@peace.com`
- Password: `password`

---

## ❌ TROUBLESHOOTING

### Port 8080 sudah digunakan?

**Windows:**
```bash
# Cek proses
netstat -ano | findstr :8080

# Kill proses (ganti <PID> dengan nomor yang muncul)
taskkill /PID <PID> /F
```

**Linux/Mac:**
```bash
# Cek dan kill
lsof -ti:8080 | xargs kill -9
```

### Database connection error?

1. Pastikan MySQL berjalan
2. Cek kredensial di file `.env`
3. Pastikan database `peace_seafood` sudah dibuat

### 404 Not Found?

1. Pastikan menjalankan dari root directory project
2. Pastikan menggunakan flag `-t public`
3. Cek file `public/index.php` ada

---

## 📚 DOKUMENTASI LENGKAP

Lihat `PORT_8080_CONFIG.md` untuk dokumentasi lengkap konfigurasi port 8080.

---

**Happy Coding! 🎉**
