# 📁 STRUKTUR FOLDER — Peace Seafood

---

## 🏗️ Root Directory Structure

```
peace-seafood/
├── public/                    # Public accessible files (webroot)
├── src/                       # Application source code
├── database/                  # Database files (schema, migrations)
├── storage/                   # Storage (logs, uploads, exports)
├── config/                    # Configuration files
├── routes/                    # Route definitions
├── tests/                     # Test files
├── cli/                       # CLI commands & seeder
├── docs/                      # Documentation
├── .env.example               # Environment template
├── .gitignore                 # Git ignore
├── .htaccess                  # Apache rewrite rules
├── composer.json              # PHP dependencies
├── composer.lock              # Locked versions
└── README.md                  # Project readme
```

---

## 📂 Detailed Folder Structure

### **1. public/ — Web Root**

```
public/
├── index.php                  # Entry point (router)
├── .htaccess                  # URL rewrite (Apache)
├── css/
│   ├── variables.css          # CSS variable system
│   ├── dark-mode.css          # Dark mode styles
│   ├── tailwind.css           # Tailwind (CDN or compiled)
│   └── custom.css             # Custom additional styles
├── js/
│   ├── api-client.js          # Axios setup & interceptor
│   ├── auth.js                # Auth & JWT handling
│   ├── dashboard.js           # Dashboard logic
│   ├── chart-config.js        # Chart.js setup
│   └── utils.js               # Utility functions
├── assets/
│   ├── logo.svg
│   ├── logo-dark.svg
│   ├── favicon.ico
│   ├── apple-touch-icon.png
│   └── images/
│       └── placeholder.jpg
└── manifest.json              # PWA manifest
```

**Rules:**
- `public/` adalah document root untuk web server
- Hanya CSS, JS, images, dan manifest yang accessible langsung
- Backend code di `src/` tidak bisa diakses dari browser
- Semua request diroute melalui `index.php` via `.htaccess`

---

### **2. src/ — Application Source**

```
src/
├── controllers/               # Business logic handlers
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── StokController.php
│   ├── PenjualanController.php
│   ├── PenitipanController.php
│   ├── ReturController.php
│   ├── KeuanganController.php
│   ├── LaporanController.php
│   ├── MasterDataController.php
│   └── SettingsController.php
│
├── models/                    # Database models
│   ├── Model.php              # Base model class
│   ├── User.php
│   ├── Gudang.php
│   ├── Supplier.php
│   ├── Pembeli.php
│   ├── JenisIkan.php
│   ├── Produk.php
│   ├── StokMasuk.php
│   ├── Timbangan.php
│   ├── Nota.php
│   ├── NotaDetail.php
│   ├── Titipan.php
│   ├── TitipanPenjualan.php
│   ├── Retur.php
│   ├── HutangPiutang.php
│   ├── BiayaOperasional.php
│   ├── HargaHistory.php
│   └── Notifikasi.php
│
├── middleware/                # Request middleware
│   ├── Middleware.php         # Base middleware
│   ├── AuthMiddleware.php     # Check JWT token
│   ├── RoleMiddleware.php     # Check role permission
│   ├── WarehouseMiddleware.php # Check warehouse access
│   └── CorsMiddleware.php     # CORS headers
│
├── services/                  # Business logic layer
│   ├── AuthService.php
│   ├── StokService.php
│   ├── PenjualanService.php
│   ├── PenitipanService.php
│   ├── ReturService.php
│   ├── KeuanganService.php
│   ├── LaporanService.php
│   ├── ExportService.php
│   ├── NotificationService.php
│   └── SettingsService.php
│
├── utils/                     # Utility & helper
│   ├── Database.php           # PDO wrapper
│   ├── JWT.php                # JWT logic
│   ├── Response.php           # Standard response format
│   ├── Validator.php          # Input validation
│   ├── Logger.php             # Logging
│   ├── Helper.php             # General helper functions
│   ├── Formatter.php          # Data formatting
│   ├── FileUpload.php         # File upload handler
│   └── Cache.php              # Simple caching
│
└── views/                     # HTML templates
    ├── layouts/
    │   ├── header.php         # Header/navbar
    │   ├── sidebar.php        # Sidebar navigation
    │   ├── footer.php         # Footer
    │   └── app.php            # Main layout
    ├── pages/
    │   ├── login.php
    │   ├── 404.php
    │   ├── 500.php
    │   └── dashboard.php
    ├── stok/
    │   ├── index.php          # Inventory list
    │   ├── masuk.php          # Stock in form
    │   ├── timbang.php        # Weighing form
    │   └── history.php        # Stock history
    ├── penjualan/
    │   ├── index.php          # Sales list
    │   ├── create.php         # Create nota
    │   ├── detail.php         # Nota detail
    │   └── print.php          # Nota print
    ├── penitipan/
    │   ├── index.php          # Consignment list
    │   ├── terima.php         # Receive consignment
    │   ├── jual.php           # Sell consignment
    │   └── settlement.php     # Settlement report
    ├── retur/
    │   ├── index.php          # Return list
    │   ├── create.php         # Create return
    │   └── detail.php         # Return detail
    ├── keuangan/
    │   ├── hutang-piutang.php
    │   ├── biaya.php
    │   └── cash-flow.php
    ├── laporan/
    │   ├── stok.php
    │   ├── penjualan.php
    │   ├── keuangan.php
    │   └── hutang-aging.php
    ├── master-data/
    │   ├── supplier.php
    │   ├── pembeli.php
    │   ├── jenis-ikan.php
    │   ├── produk.php
    │   └── harga.php
    ├── settings/
    │   ├── gudang.php
    │   ├── general.php
    │   ├── notifikasi.php
    │   ├── backup.php
    │   └── user.php
    └── components/
        ├── navbar.php
        ├── sidebar.php
        ├── modal.php
        ├── form-group.php
        └── table-component.php
```

---

### **3. database/ — Database Files**

```
database/
├── schema.sql                 # Complete database schema
├── migrations/
│   ├── 001_create_users.sql
│   ├── 002_create_gudang.sql
│   ├── 003_create_supplier.sql
│   └── ... (one per table)
├── seeders/
│   ├── UserSeeder.sql
│   ├── GudangSeeder.sql
│   ├── JenisIkanSeeder.sql
│   └── ... (initial data)
└── backups/
    └── (backup dumps)
```

---

### **4. storage/ — Runtime Files**

```
storage/
├── logs/
│   ├── app.log                # Application log
│   ├── error.log              # Error log
│   ├── database.log           # Query log
│   └── 2025-05/
│       ├── app-2025-05-17.log
│       └── ...
├── uploads/
│   ├── images/
│   ├── documents/
│   └── .gitkeep              # Keep folder in git
├── exports/
│   ├── pdf/
│   ├── excel/
│   └── .gitkeep
└── cache/
    └── (cache files jika digunakan)
```

**Rules:**
- `storage/` tidak di-commit ke git (add to .gitignore)
- Subdirectories created programmatically
- Regular cleanup untuk old files

---

### **5. config/ — Configuration**

```
config/
├── database.php               # Database connection
├── app.php                    # App settings
├── constants.php              # Application constants
├── timezone.php               # Timezone & date settings
├── roles.php                  # Role & permission definition
├── settings.php               # Default settings
└── routes.php                 # Route mapping (optional)
```

---

### **6. routes/ — API Routes**

```
routes/
├── api.php                    # Main API routes
└── web.php                    # Web routes (if needed)
```

**Example structure in api.php:**
```php
// Auth routes
$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/logout', 'AuthController@logout');

// Stok routes
$router->get('/stok', 'StokController@index');
$router->post('/stok/masuk', 'StokController@masuk');

// ... more routes
```

---

### **7. cli/ — CLI Commands**

```
cli/
├── seeder.php                 # Run seeders
├── migrate.php                # Run migrations
├── cache-clear.php            # Clear cache
├── logs-clear.php             # Clear old logs
└── backup.php                 # Database backup
```

**Usage:**
```bash
php cli/seeder.php
php cli/migrate.php
```

---

### **8. docs/ — Documentation**

```
docs/
├── API.md                     # API documentation
├── DATABASE.md                # Database guide
├── SETUP.md                   # Setup instructions
├── DEPLOYMENT.md              # Deployment guide
└── TROUBLESHOOTING.md         # Troubleshooting
```

---

## 📋 File Naming Conventions

### **PHP Files**
- **Controllers**: `{Name}Controller.php` (PascalCase)
- **Models**: `{Name}.php` (PascalCase)
- **Services**: `{Name}Service.php` (PascalCase)
- **Middleware**: `{Name}Middleware.php` (PascalCase)
- **Utils**: `{Name}.php` (PascalCase)
- **Views**: `{slug-name}.php` (kebab-case)

### **Frontend Files**
- **JavaScript**: `{slug-name}.js` (kebab-case)
- **CSS**: `{slug-name}.css` (kebab-case)
- **Images**: `{slug-name}.{ext}` (kebab-case)

### **Database Files**
- **Schema/Migration**: `{number}_{description}.sql`
  - Example: `001_create_users.sql`
  - Example: `002_create_gudang.sql`
- **Seeders**: `{Name}Seeder.sql`

---

## 🔐 .gitignore

```gitignore
# Environment
.env
.env.local

# Dependencies
vendor/
node_modules/

# Storage
storage/logs/*
storage/uploads/*
storage/exports/*
storage/cache/*
!storage/.gitkeep
!storage/logs/.gitkeep
!storage/uploads/.gitkeep
!storage/exports/.gitkeep

# IDE
.vscode/
.idea/
*.swp
*.swo
*~

# OS
.DS_Store
Thumbs.db

# Cache
*.cache
```

---

## 🚀 How to Use

### **1. Start New Feature**
Create corresponding files:
```
src/controllers/NewFeatureController.php
src/models/NewFeature.php
src/services/NewFeatureService.php
src/views/new-feature/
```

### **2. Create Database Table**
```
database/migrations/XXX_create_new_feature.sql
```

### **3. Add Route**
```php
// routes/api.php
$router->get('/new-feature', 'NewFeatureController@index');
```

### **4. Add View**
```
src/views/new-feature/index.php
```

---

## ✅ Initial Setup

```bash
# Create directories
mkdir -p public/css public/js public/assets
mkdir -p src/{controllers,models,middleware,services,utils,views}
mkdir -p database/{migrations,seeders,backups}
mkdir -p storage/{logs,uploads,exports,cache}
mkdir -p config routes cli docs tests

# Create .gitkeep files
touch storage/logs/.gitkeep
touch storage/uploads/.gitkeep
touch storage/exports/.gitkeep
touch storage/cache/.gitkeep

# Create main files
touch public/index.php
touch .env.example
touch .gitignore
touch .htaccess
```

---

**Next**: Baca `05-database-schema.md` untuk schema design →

