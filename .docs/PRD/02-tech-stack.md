# 🛠️ TECH STACK — Peace Seafood

---

## 🔧 Complete Technology Stack

### **BACKEND**

| Layer | Technology | Version | Purpose |
|---|---|---|---|
| **Language** | PHP | 8.2+ | Server-side logic |
| **Database** | MySQL | 8.0 | Data storage |
| **Dependency Manager** | Composer | Latest | PHP package management |
| **Framework** | Native PHP + OOP | - | Lightweight, full control |
| **HTTP Client** | cURL / Guzzle | 7.x | API requests |
| **Authentication** | JWT (Firebase/DependentLib) | 5.x | Token-based auth |
| **PDF Export** | DomPDF | 2.x | Generate PDF |
| **Excel Export** | PhpSpreadsheet | 1.x | Generate Excel |
| **Env Config** | PHP dotenv | 5.x | .env file support |
| **Validation** | Built-in + Custom | - | Input validation |

### **FRONTEND**

| Layer | Technology | Version | Purpose |
|---|---|---|---|
| **Framework** | Tailwind CSS | 3.x | Utility-first CSS |
| **Interactivity** | Alpine.js | 3.x | Lightweight DOM manipulation |
| **Charts** | Chart.js | 4.x | Data visualization |
| **HTTP Client** | Axios | 1.x | AJAX requests |
| **Icons** | Lucide Icons | Latest | SVG icons |
| **Dark Mode** | CSS Variable + Alpine | - | Theme switching |
| **PWA** | Service Worker | - | Offline support |
| **Date Picker** | Flatpickr / Native | 4.x | Date selection |
| **Toastify** | Izitoast / AlertifyJS | - | Toast notifications |

### **DEPLOYMENT & HOSTING**

| Layer | Technology | Version | Purpose |
|---|---|---|---|
| **Local Dev** | XAMPP / WAMP | Latest | PHP 8.2 + MySQL 8.0 |
| **Web Server** | Apache | 2.4+ | HTTP server |
| **File Server** | Local filesystem | - | File storage |

---

## 📦 Backend Dependencies (Composer)

### **Core Dependencies**

```json
{
  "require": {
    "php": ">=8.2",
    "firebase/jwt": "^5.10",
    "firebase/php-jwt": "^6.10",
    "dompdf/dompdf": "^2.0",
    "phpoffice/phpspreadsheet": "^1.29",
    "vlucas/phpdotenv": "^5.5",
    "guzzlehttp/guzzle": "^7.5",
    "monolog/monolog": "^3.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^9.5"
  }
}
```

### **Library Detail**

**firebase/jwt** (Authentication)
- Generate & verify JWT token
- HttpOnly cookie storage
- Token refresh mechanism

**dompdf/dompdf** (PDF Export)
- Convert HTML to PDF
- Support custom styling
- Margin & page setup

**phpoffice/phpspreadsheet** (Excel Export)
- Create Excel dengan format
- Multiple sheets support
- Auto-calculation formulas

**vlucas/phpdotenv** (Environment)
- Load .env variables
- Konfigurasi database, API key, dll

**guzzlehttp/guzzle** (HTTP Client)
- Make HTTP requests
- Handle response/error
- Connection pooling

**monolog/monolog** (Logging)
- Log errors & important events
- File & channel support
- Error tracking

---

## 🎨 Frontend Libraries

### **CSS & Styling**

```html
<!-- Tailwind CSS -->
<link href="https://cdn.tailwindcss.com" rel="stylesheet">

<!-- Custom CSS Variable System -->
<link href="assets/css/variables.css" rel="stylesheet">
<link href="assets/css/dark-mode.css" rel="stylesheet">
```

### **JavaScript Libraries**

```html
<!-- Alpine.js (3.x) -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- Axios (HTTP Client) -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!-- Chart.js (4.x) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.x.x/dist/chart.min.js"></script>

<!-- Lucide Icons -->
<script src="https://cdn.jsdelivr.net/npm/lucide@latest"></script>

<!-- Flatpickr (Date Picker) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Izitoast (Toast Notification) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/izitoast.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/izitoast.min.js"></script>
```

---

## 🗂️ Project Structure

```
peace-seafood/
├── public/
│   ├── index.php              # Entry point
│   ├── css/
│   │   ├── variables.css      # CSS variable system
│   │   ├── dark-mode.css      # Dark mode styles
│   │   └── custom.css         # Custom styles
│   ├── js/
│   │   ├── api-client.js      # Axios setup
│   │   ├── auth.js            # Auth logic
│   │   ├── dashboard.js       # Dashboard functions
│   │   └── utils.js           # Helper functions
│   ├── assets/
│   │   ├── logo.svg
│   │   ├── favicon.ico
│   │   └── images/
│   └── manifest.json          # PWA manifest
│
├── src/
│   ├── controllers/           # Business logic
│   │   ├── AuthController.php
│   │   ├── StokController.php
│   │   ├── PenjualanController.php
│   │   ├── PenitipanController.php
│   │   ├── ReturController.php
│   │   ├── KeuanganController.php
│   │   ├── LaporanController.php
│   │   └── SettingsController.php
│   │
│   ├── models/                # Database models
│   │   ├── User.php
│   │   ├── Gudang.php
│   │   ├── Supplier.php
│   │   ├── Pembeli.php
│   │   ├── JenisIkan.php
│   │   ├── Produk.php
│   │   ├── StokMasuk.php
│   │   ├── Timbangan.php
│   │   ├── Nota.php
│   │   ├── Titipan.php
│   │   ├── Retur.php
│   │   ├── HutangPiutang.php
│   │   ├── HargaHistory.php
│   │   └── BiayaOperasional.php
│   │
│   ├── middleware/            # Middleware
│   │   ├── AuthMiddleware.php
│   │   ├── RoleMiddleware.php
│   │   └── WarehouseMiddleware.php
│   │
│   ├── services/              # Business service
│   │   ├── AuthService.php
│   │   ├── StokService.php
│   │   ├── PenjualanService.php
│   │   ├── PenitipanService.php
│   │   ├── ReturService.php
│   │   ├── KeuanganService.php
│   │   ├── ExportService.php
│   │   └── NotificationService.php
│   │
│   ├── utils/                 # Utility functions
│   │   ├── Database.php
│   │   ├── JWT.php
│   │   ├── Validator.php
│   │   ├── Logger.php
│   │   └── Helper.php
│   │
│   └── views/                 # HTML templates
│       ├── layouts/
│       ├── dashboard/
│       ├── stok/
│       ├── penjualan/
│       ├── penitipan/
│       ├── retur/
│       ├── keuangan/
│       ├── laporan/
│       ├── master-data/
│       ├── settings/
│       └── auth/
│
├── routes/
│   └── api.php                # API routing
│
├── config/
│   ├── database.php           # Database config
│   ├── app.php                # App config
│   └── constants.php          # Constants
│
├── storage/
│   ├── logs/                  # Log files
│   ├── uploads/               # User uploads
│   └── exports/               # Export files (PDF, Excel)
│
├── tests/                     # Unit tests
│
├── .env.example               # Environment template
├── composer.json              # Dependencies
├── .htaccess                  # URL rewrite
├── package.json               # Frontend dependencies (optional)
└── README.md                  # Documentation
```

---

## 🔐 Environment Variables (.env)

```ini
# App Config
APP_NAME=Peace Seafood
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=Asia/Jakarta

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=peace_seafood
DB_USER=root
DB_PASSWORD=

# JWT
JWT_SECRET=your-secret-key-change-this
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600

# File Upload
UPLOAD_MAX_SIZE=5242880  # 5MB
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf

# Export
EXPORT_MAX_ROWS=10000

# Log
LOG_CHANNEL=single
LOG_LEVEL=debug

# Cache
CACHE_ENABLED=false
CACHE_DRIVER=file
```

---

## 📊 Database Connection

```php
<?php
// config/database.php

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? 3306;
$database = $_ENV['DB_NAME'] ?? 'peace_seafood';
$user = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

$dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

return $pdo;
?>
```

---

## 📱 Frontend Setup

### **CSS Variable System**

```css
/* assets/css/variables.css */

:root {
  /* Primary Colors */
  --color-primary: #2563eb;
  --color-secondary: #64748b;
  --color-success: #10b981;
  --color-warning: #f59e0b;
  --color-danger: #ef4444;
  
  /* Backgrounds */
  --bg-light: #ffffff;
  --bg-gray: #f8fafc;
  --text-dark: #1e293b;
  --text-light: #64748b;
  
  /* Borders */
  --border-color: #e2e8f0;
  --border-radius: 0.5rem;
}

/* Dark Mode */
[data-theme="dark"] {
  --bg-light: #1e293b;
  --bg-gray: #0f172a;
  --text-dark: #f1f5f9;
  --text-light: #cbd5e1;
  --border-color: #334155;
}
```

### **Alpine.js Dark Mode**

```html
<div x-data="{ 
    theme: localStorage.getItem('theme') || 'light'
}" 
x-init="
    $watch('theme', val => {
        localStorage.setItem('theme', val);
        document.documentElement.setAttribute('data-theme', val);
    })
"
x-bind:data-theme="theme">
    
    <!-- Toggle Button -->
    <button @click="theme = theme === 'light' ? 'dark' : 'light'">
        <i :class="theme === 'light' ? 'lucide-moon' : 'lucide-sun'"></i>
    </button>
</div>
```

---

## 🚀 Installation & Setup

### **Prerequisites**
- XAMPP / WAMP with PHP 8.2 & MySQL 8.0
- Composer installed globally

### **Installation Steps**

```bash
# 1. Clone repository
git clone <repo-url>
cd peace-seafood

# 2. Install dependencies
composer install

# 3. Setup .env
cp .env.example .env
# Edit .env dengan database credentials

# 4. Create database
mysql -u root -p < database/schema.sql

# 5. Seed initial data
php cli/seeder.php

# 6. Start XAMPP Apache + MySQL

# 7. Open http://localhost/peace-seafood
```

---

## ✅ Tech Stack Summary

- **Fast**: Native PHP, minimal overhead
- **Secure**: JWT, prepared statements, input validation
- **Modern**: Tailwind CSS, Alpine.js, Chart.js
- **Scalable**: OOP structure, service layer
- **Lightweight**: No heavy framework, full control
- **Offline**: PWA support
- **Export**: PDF & Excel built-in

---

**Next**: Baca `03-folder-structure.md` untuk file organization →

