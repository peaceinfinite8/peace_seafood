# 🔧 KONFIGURASI PORT 8080 — Peace Seafood

**Date**: May 30, 2026  
**Status**: ✅ Configured  
**Port**: 8080  

---

## 📋 RINGKASAN PERUBAHAN

Semua konfigurasi project Peace Seafood telah diubah untuk menggunakan port **8080** secara konsisten di seluruh aplikasi.

---

## 🔄 FILE YANG DIUBAH

### **1. Configuration Files**

#### **config/app.php**
```php
// BEFORE
'url' => $_ENV['APP_URL'] ?? 'http://localhost',
'cors' => [
    'origin' => $_ENV['CORS_ORIGIN'] ?? 'http://localhost',
],

// AFTER
'url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
'cors' => [
    'origin' => $_ENV['CORS_ORIGIN'] ?? 'http://localhost:8080',
],
```

#### **.env.example**
```env
# BEFORE
APP_URL=http://localhost
CORS_ORIGIN=http://localhost

# AFTER
APP_URL=http://localhost:8080
CORS_ORIGIN=http://localhost:8080
```

---

### **2. Utility Files**

#### **src/utils/FileUpload.php**
```php
// BEFORE
$base = $_ENV['APP_URL'] ?? 'http://localhost';

// AFTER
$base = $_ENV['APP_URL'] ?? 'http://localhost:8080';
```

---

### **3. Controller Files**

#### **src/controllers/AuthController.php**
```php
// BEFORE
$resetLink = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/peace_seafood/reset-password?token=" . $token;

// AFTER
$resetLink = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost:8080') . "/peace_seafood/reset-password?token=" . $token;
```

---

## 🚀 CARA MENJALANKAN APLIKASI DI PORT 8080

### **Opsi 1: PHP Built-in Server (Recommended untuk Development)**

```bash
# Dari root directory project
php -S localhost:8080 -t public

# Atau dengan host 0.0.0.0 untuk akses dari network
php -S 0.0.0.0:8080 -t public
```

**Akses aplikasi di**: `http://localhost:8080/`

---

### **Opsi 2: XAMPP Apache (Custom Port)**

#### **Langkah 1: Edit httpd.conf**
```apache
# File: C:\xampp\apache\conf\httpd.conf

# Ubah port default dari 80 ke 8080
Listen 8080

# Ubah ServerName
ServerName localhost:8080
```

#### **Langkah 2: Edit httpd-vhosts.conf (Optional)**
```apache
# File: C:\xampp\apache\conf\extra\httpd-vhosts.conf

<VirtualHost *:8080>
    DocumentRoot "C:/xampp/htdocs/peace_seafood/public"
    ServerName localhost
    
    <Directory "C:/xampp/htdocs/peace_seafood/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### **Langkah 3: Restart Apache**
```bash
# Dari XAMPP Control Panel
# Stop Apache → Start Apache
```

**Akses aplikasi di**: `http://localhost:8080/peace_seafood/`

---

### **Opsi 3: Nginx (Custom Port)**

#### **nginx.conf**
```nginx
server {
    listen 8080;
    server_name localhost;
    root /path/to/peace_seafood/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**Akses aplikasi di**: `http://localhost:8080/`

---

## 🔧 KONFIGURASI ENVIRONMENT

### **File .env (Buat dari .env.example)**

```bash
# Copy .env.example ke .env
cp .env.example .env
```

### **Isi .env untuk Port 8080**

```env
# Application
APP_NAME=Peace Seafood
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_TIMEZONE=Asia/Jakarta

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=peace_seafood
DB_USER=root
DB_PASSWORD=

# JWT
JWT_SECRET=change-this-to-a-random-secret-key-min-32-chars
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600

# Upload
UPLOAD_MAX_SIZE=5242880
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf

# Export
EXPORT_MAX_ROWS=10000

# Logging
LOG_CHANNEL=single
LOG_LEVEL=debug

# Session
SESSION_TIMEOUT_MINUTES=30

# CORS
CORS_ORIGIN=http://localhost:8080
```

---

## ✅ VERIFIKASI KONFIGURASI

### **1. Cek Server Berjalan**

```bash
# Test dengan curl
curl http://localhost:8080/

# Atau buka di browser
# http://localhost:8080/
```

### **2. Cek API Endpoint**

```bash
# Test API endpoint
curl http://localhost:8080/api/auth/profile

# Expected: 401 Unauthorized (karena belum login)
```

### **3. Cek CORS**

```bash
# Test CORS header
curl -H "Origin: http://localhost:8080" \
     -H "Access-Control-Request-Method: POST" \
     -H "Access-Control-Request-Headers: X-Requested-With" \
     -X OPTIONS \
     http://localhost:8080/api/auth/login -v
```

---

## 🔍 TROUBLESHOOTING

### **Problem 1: Port 8080 Already in Use**

```bash
# Windows: Cek proses yang menggunakan port 8080
netstat -ano | findstr :8080

# Kill process by PID
taskkill /PID <PID> /F

# Linux/Mac: Cek dan kill
lsof -ti:8080 | xargs kill -9
```

### **Problem 2: CORS Error**

**Symptom**: Browser console shows CORS error

**Solution**:
1. Pastikan `CORS_ORIGIN` di `.env` sesuai dengan URL yang diakses
2. Restart server setelah mengubah `.env`
3. Clear browser cache

### **Problem 3: 404 Not Found**

**Symptom**: Semua route return 404

**Solution**:
1. Pastikan `.htaccess` ada di folder `public/`
2. Pastikan Apache `mod_rewrite` enabled
3. Cek `DocumentRoot` mengarah ke folder `public/`

### **Problem 4: Database Connection Failed**

**Symptom**: Error "Database connection failed"

**Solution**:
1. Pastikan MySQL berjalan di port 3306
2. Cek kredensial database di `.env`
3. Pastikan database `peace_seafood` sudah dibuat

---

## 📱 URL APLIKASI SETELAH KONFIGURASI

### **Frontend Pages**
```
Login:        http://localhost:8080/
Dashboard:    http://localhost:8080/dashboard
Stok:         http://localhost:8080/stok
Penjualan:    http://localhost:8080/penjualan
Penitipan:    http://localhost:8080/penitipan
Retur:        http://localhost:8080/retur
Keuangan:     http://localhost:8080/keuangan
Laporan:      http://localhost:8080/laporan
Master Data:  http://localhost:8080/master-data
Settings:     http://localhost:8080/settings
```

### **API Endpoints**
```
Base URL:     http://localhost:8080/api
Auth:         http://localhost:8080/api/auth/login
Dashboard:    http://localhost:8080/api/dashboard
Stok:         http://localhost:8080/api/stok
Penjualan:    http://localhost:8080/api/penjualan
```

---

## 🔐 KEAMANAN

### **Development (Port 8080)**
- ✅ CORS enabled untuk `http://localhost:8080`
- ✅ Debug mode enabled
- ✅ Detailed error messages
- ⚠️ **JANGAN** gunakan di production

### **Production (Port 80/443)**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
CORS_ORIGIN=https://yourdomain.com
```

---

## 📊 PERBANDINGAN KONFIGURASI

| Aspek | Port 80 (Default) | Port 8080 (Sekarang) |
|-------|-------------------|----------------------|
| **URL** | `http://localhost/` | `http://localhost:8080/` |
| **XAMPP** | Default Apache | Custom config required |
| **PHP Server** | `php -S localhost:80` | `php -S localhost:8080` |
| **Konflik** | Sering dengan IIS/Skype | Jarang konflik |
| **Development** | Standard | ✅ Recommended |
| **Production** | ✅ Standard | Custom setup |

---

## 🎯 CHECKLIST KONFIGURASI

### **Setup Awal**
- [x] Update `config/app.php` dengan port 8080
- [x] Update `.env.example` dengan port 8080
- [x] Update `src/utils/FileUpload.php` dengan port 8080
- [x] Update `src/controllers/AuthController.php` dengan port 8080
- [ ] Copy `.env.example` ke `.env`
- [ ] Update `.env` sesuai environment lokal
- [ ] Setup database `peace_seafood`
- [ ] Run migrations

### **Testing**
- [ ] Test akses `http://localhost:8080/`
- [ ] Test login functionality
- [ ] Test API endpoints
- [ ] Test file upload
- [ ] Test CORS headers
- [ ] Test password reset link

### **Deployment**
- [ ] Update production `.env` dengan domain dan port yang sesuai
- [ ] Update CORS origin untuk production
- [ ] Disable debug mode di production
- [ ] Setup HTTPS untuk production

---

## 📞 SUPPORT

### **Jika Mengalami Masalah:**

1. **Cek log error**:
   ```bash
   # PHP built-in server
   # Error akan muncul di terminal
   
   # Apache
   tail -f C:\xampp\apache\logs\error.log
   
   # Application log
   tail -f storage/logs/app.log
   ```

2. **Cek konfigurasi**:
   ```bash
   # Verify .env loaded
   php -r "var_dump(getenv('APP_URL'));"
   
   # Verify port listening
   netstat -an | findstr :8080
   ```

3. **Clear cache**:
   ```bash
   # Clear browser cache
   # Restart server
   # Clear PHP opcache (if enabled)
   ```

---

## 📚 DOKUMENTASI TERKAIT

- `README.md` - Project overview
- `.docs/PRD/02-tech-stack.md` - Technical stack details
- `.docs/guides/tech-stack-notes.md` - Development notes
- `MIGRATION_PLAN.md` - Database migration guide
- `MERGE_REPAIR.md` - Issue resolution guide

---

## ✅ STATUS KONFIGURASI

**Port Configuration**: ✅ **COMPLETE**  
**Files Updated**: 4 files  
**Testing Required**: Yes  
**Production Ready**: After testing  

---

**Konfigurasi Selesai**: May 30, 2026  
**Port**: 8080  
**Status**: ✅ **READY FOR TESTING**

---

*Semua konfigurasi telah diubah untuk menggunakan port 8080. Silakan test aplikasi dengan menjalankan `php -S localhost:8080 -t public` dari root directory.*