# 🔐 SECURITY — Peace Seafood

---

## 🎯 Security Principles

- **Defense in Depth**: Multiple layers of protection
- **Principle of Least Privilege**: Users only get necessary access
- **Security by Default**: Secure defaults, opt-in for less secure options
- **Fail Secure**: Errors default to denial

---

## 🔑 AUTHENTICATION

### **JWT (JSON Web Token)**

```php
<?php
// Generate JWT
use Firebase\JWT\JWT;

$payload = [
    'user_id' => $user['id'],
    'role' => $user['role'],
    'exp' => time() + 3600  // 1 hour
];

$token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

// Set HttpOnly Cookie (secure)
setcookie(
    'auth_token',
    $token,
    [
        'expires' => time() + 3600,
        'path' => '/',
        'httponly' => true,      // No JS access
        'secure' => true,        // HTTPS only
        'samesite' => 'Strict'   // CSRF protection
    ]
);
?>
```

### **Password Hashing**

```php
<?php
// Hash pada register/change password
$password_hashed = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 10]);

// Verify pada login
if (password_verify($_POST['password'], $user['password'])) {
    // Login success
}

// Jangan pernah:
// - Store plain text password
// - Use md5 / sha1 (outdated)
// - Double hash
?>
```

### **Session Management**

```php
<?php
// Session timeout
$session_timeout = $_ENV['SESSION_TIMEOUT_MINUTES'] ?? 30;
$last_activity = $_SESSION['last_activity'] ?? time();

if (time() - $last_activity > $session_timeout * 60) {
    session_destroy();
    header('Location: /login');
    exit;
}

$_SESSION['last_activity'] = time();
?>
```

---

## 🛡️ INPUT VALIDATION & SANITIZATION

### **SQL Injection Prevention**

```php
<?php
// WRONG (Vulnerable)
$user = $db->query("SELECT * FROM users WHERE email = '" . $_POST['email'] . "'");

// CORRECT (Prepared Statement)
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$_POST['email']]);
$user = $stmt->fetch();

// ALSO CORRECT (Named parameters)
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = :status");
$stmt->execute([':email' => $_POST['email'], ':status' => 'active']);
?>
```

### **XSS (Cross-Site Scripting) Prevention**

```php
<?php
// WRONG
echo "Halo " . $_POST['name'];  // If name = "<script>alert('xss')</script>"

// CORRECT (Escape output)
echo "Halo " . htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');

// For JSON (always escape)
echo json_encode($data);  // Auto-escapes
?>
```

```javascript
// Client-side XSS prevention
// WRONG
element.innerHTML = userInput;

// CORRECT
element.textContent = userInput; // For text only
element.innerText = userInput; // For text only

// Or use sanitizer library
DOMPurify.sanitize(userInput);
```

### **Input Validation**

```php
<?php
// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new ValidationException('Email tidak valid');
}

// Numeric
if (!is_numeric($qty) || $qty <= 0) {
    throw new ValidationException('Qty harus angka positif');
}

// String length
if (strlen($name) < 3 || strlen($name) > 100) {
    throw new ValidationException('Nama harus 3-100 karakter');
}

// File upload (MIME type)
$allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
$file_mime = mime_content_type($_FILES['file']['tmp_name']);

if (!in_array($file_mime, $allowed_types)) {
    throw new ValidationException('File type tidak diizinkan');
}

// File size
$max_size = 5 * 1024 * 1024;  // 5MB
if ($_FILES['file']['size'] > $max_size) {
    throw new ValidationException('Ukuran file terlalu besar');
}
?>
```

---

## 🔐 CORS (Cross-Origin Resource Sharing)

```php
<?php
// Set CORS headers
header('Access-Control-Allow-Origin: ' . $_ENV['CORS_ORIGIN'] ?? 'http://localhost');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Validate origin
$allowed_origins = explode(',', $_ENV['CORS_ORIGIN']);
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (!in_array($origin, $allowed_origins)) {
    http_response_code(403);
    die('Origin not allowed');
}
?>
```

---

## 🔒 CSRF (Cross-Site Request Forgery) Protection

```php
<?php
// Generate CSRF token
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include dalam form
?>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
</form>

<?php
// Verify CSRF token
function verifyCsrfToken($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    throw new SecurityException('CSRF token tidak valid');
}
?>
```

---

## 🚫 RATE LIMITING

```php
<?php
// Prevent brute force login attempts
class RateLimiter {
    private $redis;

    public function checkLimit($key, $max_attempts, $window_seconds) {
        $current = $this->redis->incr($key);

        if ($current === 1) {
            $this->redis->expire($key, $window_seconds);
        }

        if ($current > $max_attempts) {
            throw new RateLimitException("Terlalu banyak percobaan. Coba lagi dalam {$window_seconds} detik");
        }
    }
}

// Usage
$limiter = new RateLimiter($redis);
$limiter->checkLimit("login_" . $_POST['email'], 5, 300);  // 5 attempts per 5 minutes
?>
```

---

## 📁 FILE UPLOAD SECURITY

```php
<?php
class FileUploadHandler {
    private $upload_dir = 'storage/uploads/';
    private $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
    private $max_size = 5242880;  // 5MB

    public function upload($file) {
        // 1. Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $this->allowed_types)) {
            throw new ValidationException('File type tidak diizinkan');
        }

        // 2. Validate file size
        if ($file['size'] > $this->max_size) {
            throw new ValidationException('File terlalu besar');
        }

        // 3. Check for suspicious content
        if (strpos($mime, 'executable') !== false) {
            throw new ValidationException('File terdeteksi berbahaya');
        }

        // 4. Generate safe filename
        $filename = bin2hex(random_bytes(16)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);

        // 5. Move to upload directory (outside web root if possible)
        if (!move_uploaded_file($file['tmp_name'], $this->upload_dir . $filename)) {
            throw new Exception('Gagal upload file');
        }

        return $filename;
    }
}
?>
```

---

## 🔑 PERMISSION & AUTHORIZATION

```php
<?php
// Check permission before every action
class AuthorizationMiddleware {
    public static function requireRole($required_roles) {
        $current_role = $_SESSION['user_role'] ?? null;

        if (!in_array($current_role, (array)$required_roles)) {
            http_response_code(403);
            die('Forbidden');
        }
    }

    public static function requireWarehouseAccess($warehouse_id) {
        $user_warehouse = $_SESSION['user_warehouse'] ?? null;
        $user_role = $_SESSION['user_role'] ?? null;

        // BOZ can access semua warehouse
        if ($user_role === 'bos') {
            return true;
        }

        // Admin/Checker hanya bisa access assigned warehouse
        if ($user_warehouse !== $warehouse_id) {
            http_response_code(403);
            die('Akses warehouse ditolak');
        }
    }
}

// Usage
AuthorizationMiddleware::requireRole(['bos', 'admin']);
AuthorizationMiddleware::requireWarehouseAccess($_POST['id_gudang']);
?>
```

---

## 🔒 DATABASE SECURITY

### **User Permissions**

```sql
-- Create app user (limited permissions)
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'strong_password';

-- Grant only necessary permissions
GRANT SELECT, INSERT, UPDATE ON peace_seafood.* TO 'app_user'@'localhost';
GRANT DELETE ON peace_seafood.nota TO 'app_user'@'localhost';  -- Only specific tables

-- Revoke dangerous permissions
REVOKE DROP ON peace_seafood.* FROM 'app_user'@'localhost';
REVOKE ALTER ON peace_seafood.* FROM 'app_user'@'localhost';
```

### **Connection Security**

```php
<?php
// Use SSL for database connection
$options = [
    PDO::MYSQL_ATTR_SSL_KEY    => '/path/to/client-key.pem',
    PDO::MYSQL_ATTR_SSL_CERT   => '/path/to/client-cert.pem',
    PDO::MYSQL_ATTR_SSL_CA     => '/path/to/ca-cert.pem',
];

$pdo = new PDO($dsn, $user, $password, $options);
?>
```

---

## 📝 LOGGING & MONITORING

```php
<?php
// Log security events
Logger::warning('Failed login attempt', [
    'email' => $email,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'timestamp' => date('Y-m-d H:i:s')
]);

Logger::info('User password changed', [
    'user_id' => $user_id,
    'ip' => $_SERVER['REMOTE_ADDR']
]);

Logger::error('SQL injection attempt detected', [
    'input' => $_GET['search'],
    'ip' => $_SERVER['REMOTE_ADDR']
]);
?>
```

---

## 🚀 DEPLOYMENT SECURITY

### **.env Security**

```
# Never commit .env to git
# Add to .gitignore

# Store in secure location outside web root
/var/www/peace-seafood/.env  (inside app, not web accessible)

# Permissions
chmod 600 .env  (read-only for app user)
```

### **HTTPS/SSL**

```apache
# Force HTTPS redirect
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### **Security Headers**

```php
<?php
// Set security headers
header('X-Content-Type-Options: nosniff');           // Prevent MIME sniffing
header('X-Frame-Options: DENY');                     // Clickjacking protection
header('X-XSS-Protection: 1; mode=block');           // XSS protection
header('Strict-Transport-Security: max-age=31536000'); // Force HTTPS
header('Content-Security-Policy: default-src self'); // CSP
?>
```

---

## ✅ Security Checklist

- [ ] All inputs validated & sanitized
- [ ] Prepared statements untuk semua queries
- [ ] Password hashed dengan bcrypt
- [ ] JWT tokens dengan expiration
- [ ] CORS configured properly
- [ ] CSRF protection implemented
- [ ] Rate limiting untuk login
- [ ] File upload validated
- [ ] Authorization check pada setiap endpoint
- [ ] Security headers set
- [ ] Logging security events
- [ ] .env tidak di-commit
- [ ] Database user limited permissions
- [ ] HTTPS forced
- [ ] No sensitive data in logs

---

**Next**: Baca `16-notifikasi.md` untuk sistem notifikasi →
