# 🛠️ TECH STACK & DEPENDENCIES — Peace Seafood

---

## 📦 PROJECT DEPENDENCIES

Complete list of all dependencies with versions, purposes, and usage.

---

## ⚙️ BACKEND DEPENDENCIES (PHP 8.2 Native)

### **Core Framework & OOP**

```
- PHP 8.2.x native (no framework)
- Composer 2.x (dependency manager)
- PDO + MySQL 8.0
```

### **Authentication & Security**

| Package          | Version | Purpose                           | Usage                         |
| ---------------- | ------- | --------------------------------- | ----------------------------- |
| firebase/jwt     | 5.x     | JWT token generation & validation | Auth middleware, login tokens |
| vlucas/phpdotenv | 5.x     | Environment variable management   | .env file loading             |

**Installation:**

```bash
composer require firebase/jwt
composer require vlucas/phpdotenv
```

### **Logging & Error Tracking**

| Package         | Version | Purpose            | Usage                            |
| --------------- | ------- | ------------------ | -------------------------------- |
| monolog/monolog | 3.x     | Structured logging | Application logs, error tracking |

**Installation:**

```bash
composer require monolog/monolog
```

### **Document Generation**

| Package                  | Version | Purpose                | Usage                           |
| ------------------------ | ------- | ---------------------- | ------------------------------- |
| dompdf/dompdf            | 2.x     | HTML to PDF conversion | Invoice PDFs, report generation |
| phpoffice/phpspreadsheet | 1.x     | Excel file generation  | Report exports to XLSX          |

**Installation:**

```bash
composer require dompdf/dompdf
composer require phpoffice/phpspreadsheet
```

---

## 🎨 FRONTEND DEPENDENCIES

### **Core CSS & Framework**

| Library      | Version | Purpose           | CDN                           |
| ------------ | ------- | ----------------- | ----------------------------- |
| Tailwind CSS | 3.x     | Utility-first CSS | `https://cdn.tailwindcss.com` |

### **JavaScript Interactivity**

| Library   | Version | Purpose                | CDN                                                         |
| --------- | ------- | ---------------------- | ----------------------------------------------------------- |
| Alpine.js | 3.x     | Lightweight reactivity | `https://cdn.jsdelivr.net/npm/alpinejs@3.x/dist/cdn.min.js` |

### **Charts & Visualization**

| Library  | Version | Purpose            | CDN                                                           |
| -------- | ------- | ------------------ | ------------------------------------------------------------- |
| Chart.js | 4.x     | Data visualization | `https://cdn.jsdelivr.net/npm/chart.js@4.x/dist/chart.min.js` |

### **HTTP & UI Utilities**

| Library      | Version | Purpose     | CDN                                                    |
| ------------ | ------- | ----------- | ------------------------------------------------------ |
| Axios        | 1.x     | HTTP client | `https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js` |
| Lucide Icons | Latest  | SVG icons   | `https://cdn.jsdelivr.net/npm/lucide@latest`           |

---

## 🌐 COMPLETE HEAD SETUP

```html
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Peace Seafood</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Custom Config -->
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: "#2563eb",
            secondary: "#1f2937",
            danger: "#dc2626",
            success: "#10b981",
            warning: "#f59e0b",
          },
        },
      },
    };
  </script>

  <!-- Dark Mode Variables -->
  <style>
    :root {
      --color-primary: #2563eb;
      --bg-light: #ffffff;
    }
    [data-theme="dark"] {
      --color-primary: #3b82f6;
      --bg-light: #111827;
    }
  </style>
</head>
```

---

## 📝 COMPLETE SCRIPT SETUP

```html
<body>
  <!-- Main Content -->

  <!-- Lucide Icons -->
  <script src="https://cdn.jsdelivr.net/npm/lucide@latest"></script>

  <!-- Alpine.js -->
  <script
    defer
    src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"
  ></script>

  <!-- Axios -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>

  <!-- Initialize -->
  <script>
    lucide.createIcons();
    const theme = localStorage.getItem("theme") || "light";
    document.documentElement.setAttribute("data-theme", theme);
  </script>
</body>
```

---

## 🚀 INSTALLATION & SETUP

### **Backend Setup**

```bash
# 1. Install PHP dependencies
composer install

# 2. Setup environment
cp .env.example .env
# Edit .env with database credentials

# 3. Create database
mysql -u root -p
CREATE DATABASE peace_seafood CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 4. Run seeders
php cli/seeder.php

# 5. Start development server
php -S localhost:8080 -t public
```

---

## ⚙️ .env Configuration

```
APP_NAME=Peace Seafood
APP_ENV=development
APP_DEBUG=true

DB_HOST=localhost
DB_PORT=3306
DB_NAME=peace_seafood
DB_USER=root
DB_PASSWORD=

JWT_SECRET=your_secret_key_here

SESSION_TIMEOUT_MINUTES=30
CORS_ORIGIN=http://localhost:8080
```

---

## ✅ DEFAULT ACCOUNTS

| Role    | Email               | Password    |
| ------- | ------------------- | ----------- |
| BOZ     | bos@example.com     | password123 |
| Admin   | admin@example.com   | password123 |
| Checker | checker@example.com | password123 |

---

**Next**: Read `PRD/00-index.md` untuk memahami struktur dokumentasi lengkap →
