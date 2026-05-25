# 🔐 SESSION & COOKIES MANAGEMENT
## Peace Seafood - Security & Session Handling

**Version:** 1.0.0  
**Last Updated:** 2025-05-21  
**Status:** ✅ Implemented

---

## 📋 OVERVIEW

Sistem Peace Seafood menggunakan kombinasi **JWT Token** dan **PHP Session** dengan **HTTP-only Cookies** untuk keamanan maksimal.

### **Key Features:**
- ✅ Session timeout 30 menit
- ✅ HTTP-only cookies (tidak bisa diakses JavaScript)
- ✅ Secure cookies (HTTPS only di production)
- ✅ SameSite protection (CSRF protection)
- ✅ Auto-refresh token
- ✅ Session warning sebelum expired
- ✅ Activity tracking
- ✅ Session regeneration untuk security

---

## ⚙️ CONFIGURATION

### **1. Environment Variables (.env)**

```env
# JWT Configuration (30 minutes)
JWT_SECRET=change-this-to-a-random-secret-key-min-32-chars
JWT_ALGORITHM=HS256
JWT_EXPIRATION=1800

# Session Configuration (30 minutes)
SESSION_TIMEOUT_MINUTES=30
SESSION_NAME=PEACE_SEAFOOD_SESSION
SESSION_COOKIE_LIFETIME=1800
SESSION_COOKIE_DOMAIN=
SESSION_COOKIE_SECURE=false
SESSION_COOKIE_SAMESITE=Strict
```

### **2. Config File (config/app.php)**

```php
'jwt' => [
    'secret'     => $_ENV['JWT_SECRET'] ?? 'change-this-secret',
    'algorithm'  => $_ENV['JWT_ALGORITHM'] ?? 'HS256',
    'expiration' => (int)($_ENV['JWT_EXPIRATION'] ?? 1800), // 30 minutes
],

'session' => [
    'timeout_minutes' => (int)($_ENV['SESSION_TIMEOUT_MINUTES'] ?? 30),
    'name'            => $_ENV['SESSION_NAME'] ?? 'PEACE_SEAFOOD_SESSION',
    'cookie_lifetime' => (int)($_ENV['SESSION_COOKIE_LIFETIME'] ?? 1800),
    'cookie_path'     => '/',
    'cookie_domain'   => $_ENV['SESSION_COOKIE_DOMAIN'] ?? '',
    'cookie_secure'   => ($_ENV['SESSION_COOKIE_SECURE'] ?? 'false') === 'true',
    'cookie_httponly' => true,
    'cookie_samesite' => $_ENV['SESSION_COOKIE_SAMESITE'] ?? 'Strict',
],
```

---

## 🔒 SECURITY FEATURES

### **1. HTTP-Only Cookies**

**Apa itu?**
- Cookie yang tidak bisa diakses oleh JavaScript
- Melindungi dari XSS (Cross-Site Scripting) attacks

**Implementasi:**
```php
setcookie('auth_token', $token, [
    'httponly' => true,  // ← Tidak bisa diakses JavaScript
    'secure'   => true,  // ← Hanya HTTPS (production)
    'samesite' => 'Strict', // ← CSRF protection
]);
```

### **2. Secure Cookies (HTTPS Only)**

**Apa itu?**
- Cookie hanya dikirim melalui HTTPS
- Melindungi dari man-in-the-middle attacks

**Kapan aktif?**
- Development (HTTP): `secure = false`
- Production (HTTPS): `secure = true`

### **3. SameSite Protection**

**Apa itu?**
- Melindungi dari CSRF (Cross-Site Request Forgery)
- Cookie hanya dikirim dari same-site requests

**Options:**
- `Strict`: Paling ketat (recommended)
- `Lax`: Lebih fleksibel
- `None`: Tidak ada proteksi (tidak recommended)

### **4. Session Regeneration**

**Apa itu?**
- Mengubah session ID setelah login
- Melindungi dari session fixation attacks

**Implementasi:**
```php
Session::regenerate(); // Setelah login
```

### **5. Session Validation**

**Apa itu?**
- Validasi session di setiap request
- Cek apakah session masih valid (tidak expired)

**Implementasi:**
```php
if (!Session::isValid()) {
    Response::unauthorized('Session expired');
}
```

---

## 🔄 SESSION FLOW

### **1. Login Flow**

```
User Login
    ↓
Validate Credentials
    ↓
Generate JWT Token (30 min expiration)
    ↓
Create PHP Session
    ↓
Set HTTP-only Cookie with Token
    ↓
Store User Data in Session
    ↓
Regenerate Session ID (security)
    ↓
Return Token + User Data
```

**Code Example:**
```php
// AuthController::login()
$result = $this->authService->login($email, $password);

// Store in session
Session::set('user_id', $result['user']['id']);
Session::set('authenticated', true);

// Regenerate for security
Session::regenerate();

// Set cookie (30 minutes)
JWT::setHttpOnlyCookie($result['token'], 1800);
```

### **2. Request Flow (Protected Routes)**

```
User Request
    ↓
Initialize Session
    ↓
Check Session Valid? (not expired)
    ↓ Yes
Get Token from Cookie/Header
    ↓
Verify JWT Token
    ↓
Check User Active in DB
    ↓
Verify Session User = Token User
    ↓
Update Last Activity Time
    ↓
Process Request
```

**Code Example:**
```php
// AuthMiddleware::handle()
Session::init();

if (!Session::isValid()) {
    Response::unauthorized('Session expired');
}

$token = JWT::getFromRequest();
$payload = JWT::verify($token);

// Verify session matches token
if (Session::get('user_id') != $payload['id']) {
    Session::destroy();
    Response::unauthorized('Session mismatch');
}
```

### **3. Logout Flow**

```
User Logout
    ↓
Destroy PHP Session
    ↓
Clear Session Cookie
    ↓
Clear Auth Token Cookie
    ↓
Return Success
```

**Code Example:**
```php
// AuthController::logout()
Session::destroy();
JWT::clearCookie();
Response::success(null, 'Logout berhasil');
```

### **4. Refresh Flow**

```
User Activity / Manual Refresh
    ↓
Check Session Valid
    ↓
Get Current User from Token
    ↓
Generate New JWT Token (30 min)
    ↓
Update Session Last Activity
    ↓
Set New Cookie
    ↓
Return New Token
```

**Code Example:**
```php
// AuthController::refresh()
if (!Session::isValid()) {
    Response::unauthorized('Session expired');
}

$newToken = $this->authService->refreshToken($user);
Session::set('last_activity', time());
JWT::setHttpOnlyCookie($newToken, 1800);
```

---

## 📡 API ENDPOINTS

### **1. POST /api/auth/login**

**Request:**
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "role": "admin",
      "id_gudang": 1
    },
    "session": {
      "timeout_minutes": 30,
      "expires_at": "2025-05-21 15:30:00"
    }
  }
}
```

**Cookies Set:**
- `auth_token`: JWT token (HTTP-only, 30 min)
- `PEACE_SEAFOOD_SESSION`: PHP session ID

### **2. POST /api/auth/logout**

**Response:**
```json
{
  "success": true,
  "message": "Logout berhasil",
  "data": null
}
```

**Cookies Cleared:**
- `auth_token`
- `PEACE_SEAFOOD_SESSION`

### **3. GET /api/auth/profile**

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "role": "admin",
    "id_gudang": 1,
    "session_info": {
      "remaining_time_seconds": 1500,
      "remaining_time_minutes": 25.0
    }
  }
}
```

### **4. GET /api/auth/session-info**

**Response:**
```json
{
  "success": true,
  "data": {
    "session_id": "abc123...",
    "session_name": "PEACE_SEAFOOD_SESSION",
    "created_at": 1621600000,
    "last_activity": 1621601800,
    "remaining_time": 1200,
    "timeout_minutes": 30,
    "is_valid": true,
    "user": {
      "id": 1,
      "email": "admin@example.com",
      "role": "admin",
      "name": "Admin",
      "id_gudang": 1
    }
  }
}
```

### **5. POST /api/auth/refresh**

**Response:**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_at": "2025-05-21 16:00:00",
    "remaining_time_seconds": 1800
  }
}
```

---

## 💻 FRONTEND INTEGRATION

### **1. Include Session Manager**

```html
<!-- In your layout/header -->
<script src="/js/session-manager.js"></script>
```

### **2. Auto-Initialize**

Session manager akan otomatis initialize di semua halaman kecuali login:

```javascript
// Auto-initialized with default settings
window.sessionManager = new SessionManager({
    sessionTimeout: 30 * 60 * 1000,  // 30 minutes
    warningTime: 5 * 60 * 1000,      // 5 minutes warning
    autoRefresh: true,                // Auto refresh enabled
    refreshThreshold: 10 * 60 * 1000, // Refresh when 10 min remaining
});
```

### **3. Manual Control**

```javascript
// Get remaining time
const remaining = sessionManager.getRemainingTime(); // milliseconds
const minutes = sessionManager.getRemainingMinutes(); // minutes

// Manually extend session
await sessionManager.extendSession();

// Destroy session manager
sessionManager.destroy();
```

### **4. Custom Configuration**

```javascript
const sessionManager = new SessionManager({
    sessionTimeout: 30 * 60 * 1000,   // 30 minutes
    warningTime: 5 * 60 * 1000,       // Show warning 5 min before
    checkInterval: 60 * 1000,         // Check every minute
    autoRefresh: true,                 // Auto refresh enabled
    refreshThreshold: 10 * 60 * 1000, // Refresh when 10 min left
});
```

---

## ⚡ FEATURES

### **1. Activity Tracking**

Session manager tracks user activity:
- Mouse movements
- Keyboard input
- Scrolling
- Touch events
- Clicks

**Benefit:** Session hanya expired jika user benar-benar idle.

### **2. Auto-Refresh**

Token otomatis di-refresh ketika:
- User masih aktif
- Remaining time < 10 menit
- Remaining time > 5 menit (sebelum warning)

**Benefit:** User tidak perlu manual refresh.

### **3. Warning Modal**

Modal warning muncul 5 menit sebelum session expired:
- Countdown timer
- Tombol "Perpanjang Session"
- Auto-hide jika user aktif

**Benefit:** User diberi kesempatan untuk extend session.

### **4. Toast Notifications**

Notifikasi untuk:
- Session berhasil diperpanjang
- Session gagal diperpanjang
- Session expired

**Benefit:** User selalu tahu status session.

---

## 🧪 TESTING

### **1. Test Session Timeout**

```bash
# Set short timeout for testing
SESSION_TIMEOUT_MINUTES=1
JWT_EXPIRATION=60

# Login and wait 1 minute
# Session should expire
```

### **2. Test Auto-Refresh**

```javascript
// Monitor console logs
// Should see: "[Session] Auto-refreshed successfully"
```

### **3. Test Warning Modal**

```javascript
// Set short warning time
const sessionManager = new SessionManager({
    sessionTimeout: 2 * 60 * 1000,  // 2 minutes
    warningTime: 1 * 60 * 1000,     // 1 minute warning
});

// Wait 1 minute, modal should appear
```

### **4. Test Activity Tracking**

```javascript
// Check remaining time
console.log(sessionManager.getRemainingMinutes());

// Do some activity (click, scroll)
// Check again - should reset to 30 minutes
console.log(sessionManager.getRemainingMinutes());
```

---

## 🔍 TROUBLESHOOTING

### **Problem: Session expired too quickly**

**Solution:**
```env
# Increase timeout
SESSION_TIMEOUT_MINUTES=60
JWT_EXPIRATION=3600
```

### **Problem: Cookie not set**

**Check:**
1. HTTPS enabled? Set `SESSION_COOKIE_SECURE=true`
2. Domain correct? Set `SESSION_COOKIE_DOMAIN=yourdomain.com`
3. Path correct? Default is `/`

### **Problem: Session not persisting**

**Check:**
1. Session directory writable?
2. PHP session.save_path configured?
3. Cookies enabled in browser?

### **Problem: Auto-refresh not working**

**Check:**
1. `autoRefresh: true` in SessionManager?
2. User activity being tracked?
3. Network requests successful?

---

## 📊 SESSION DATA STRUCTURE

### **PHP Session ($_SESSION)**

```php
[
    'initialized' => true,
    'created_at' => 1621600000,
    'last_activity' => 1621601800,
    'last_regeneration' => 1621600000,
    'user_id' => 1,
    'user_email' => 'admin@example.com',
    'user_role' => 'admin',
    'user_name' => 'Admin',
    'id_gudang' => 1,
    'authenticated' => true,
    '_flash' => [
        // Flash messages
    ]
]
```

### **JWT Token Payload**

```json
{
  "id": 1,
  "name": "Admin",
  "email": "admin@example.com",
  "role": "admin",
  "id_gudang": 1,
  "iat": 1621600000,
  "exp": 1621601800
}
```

### **Cookies**

```
auth_token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...;
  Path=/;
  HttpOnly;
  Secure;
  SameSite=Strict;
  Expires=Thu, 21 May 2025 15:30:00 GMT

PEACE_SEAFOOD_SESSION=abc123...;
  Path=/;
  HttpOnly;
  Secure;
  SameSite=Strict;
  Expires=Thu, 21 May 2025 15:30:00 GMT
```

---

## 🛡️ SECURITY BEST PRACTICES

### **1. Always Use HTTPS in Production**

```env
# Production
SESSION_COOKIE_SECURE=true
```

### **2. Use Strong JWT Secret**

```env
# Minimum 32 characters, random
JWT_SECRET=your-very-long-random-secret-key-here-min-32-chars
```

### **3. Regenerate Session After Login**

```php
Session::regenerate(); // Prevent session fixation
```

### **4. Validate Session on Every Request**

```php
if (!Session::isValid()) {
    Response::unauthorized('Session expired');
}
```

### **5. Clear Session on Logout**

```php
Session::destroy();
JWT::clearCookie();
```

### **6. Use SameSite=Strict**

```env
SESSION_COOKIE_SAMESITE=Strict
```

### **7. Monitor Session Activity**

```php
// Log suspicious activity
if ($elapsed > $threshold) {
    Logger::warning('Suspicious session activity', [
        'user_id' => $userId,
        'elapsed' => $elapsed,
    ]);
}
```

---

## 📚 FILES CREATED/MODIFIED

### **New Files:**
- `src/utils/Session.php` - Session manager utility
- `public/js/session-manager.js` - Frontend session handler
- `.docs/PRD/18-sessions-and-cookies.md` - This documentation

### **Modified Files:**
- `config/app.php` - Added session configuration
- `src/utils/JWT.php` - Added custom expiration support
- `src/controllers/AuthController.php` - Added session handling
- `src/middleware/AuthMiddleware.php` - Added session validation
- `.env.example` - Added session environment variables

---

## ✅ CHECKLIST

### **Backend:**
- [x] Session utility created
- [x] JWT updated with custom expiration
- [x] AuthController updated with session
- [x] AuthMiddleware updated with validation
- [x] Configuration updated
- [x] Environment variables added

### **Frontend:**
- [x] Session manager JavaScript created
- [x] Activity tracking implemented
- [x] Auto-refresh implemented
- [x] Warning modal implemented
- [x] Toast notifications implemented

### **Security:**
- [x] HTTP-only cookies
- [x] Secure cookies (HTTPS)
- [x] SameSite protection
- [x] Session regeneration
- [x] Session validation
- [x] CSRF protection

### **Documentation:**
- [x] Configuration guide
- [x] API endpoints documented
- [x] Frontend integration guide
- [x] Security best practices
- [x] Troubleshooting guide

---

## 🎯 SUMMARY

✅ **Session timeout: 30 menit**  
✅ **HTTP-only cookies: Enabled**  
✅ **Auto-refresh: Enabled**  
✅ **Warning modal: 5 menit sebelum expired**  
✅ **Activity tracking: Enabled**  
✅ **Security: Maximum**

**Status:** Ready for Production ✅

---

**Version:** 1.0.0  
**Last Updated:** 2025-05-21  
**Author:** Peace Seafood Development Team

