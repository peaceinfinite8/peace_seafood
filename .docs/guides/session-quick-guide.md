# 🔐 SESSION & COOKIES - QUICK GUIDE
## Peace Seafood

**Status:** ✅ Implemented  
**Session Timeout:** 30 Minutes  
**Auto-Refresh:** Enabled

---

## 🚀 QUICK START

### **1. Configuration (.env)**

```env
# Session & JWT (30 minutes)
JWT_EXPIRATION=1800
SESSION_TIMEOUT_MINUTES=30
SESSION_COOKIE_LIFETIME=1800
```

### **2. Frontend Integration**

```html
<!-- Include in your layout -->
<script src="/js/session-manager.js"></script>
```

**That's it!** Session manager akan otomatis aktif.

---

## 🔒 SECURITY FEATURES

✅ **HTTP-Only Cookies** - Tidak bisa diakses JavaScript  
✅ **Secure Cookies** - HTTPS only (production)  
✅ **SameSite Protection** - CSRF protection  
✅ **Session Regeneration** - Prevent session fixation  
✅ **Activity Tracking** - Auto-refresh saat user aktif  
✅ **Warning Modal** - 5 menit sebelum expired

---

## 📡 NEW API ENDPOINTS

### **1. GET /api/auth/session-info**
Cek status session saat ini

### **2. POST /api/auth/refresh**
Refresh token dan extend session

---

## ⚡ FEATURES

### **Auto-Refresh**
Token otomatis di-refresh ketika:
- User masih aktif
- Remaining time < 10 menit

### **Warning Modal**
Modal muncul 5 menit sebelum expired dengan:
- Countdown timer
- Tombol "Perpanjang Session"

### **Activity Tracking**
Tracks: mouse, keyboard, scroll, touch, click

---

## 💻 USAGE EXAMPLES

### **Check Remaining Time**
```javascript
const minutes = sessionManager.getRemainingMinutes();
console.log(`Session expires in ${minutes} minutes`);
```

### **Manual Refresh**
```javascript
await sessionManager.extendSession();
```

### **Get Session Info**
```javascript
const response = await fetch('/api/auth/session-info');
const data = await response.json();
console.log(data.data.remaining_time_seconds);
```

---

## 🔧 CONFIGURATION OPTIONS

```javascript
const sessionManager = new SessionManager({
    sessionTimeout: 30 * 60 * 1000,   // 30 minutes
    warningTime: 5 * 60 * 1000,       // 5 minutes warning
    autoRefresh: true,                 // Auto refresh
    refreshThreshold: 10 * 60 * 1000, // Refresh at 10 min
});
```

---

## 📊 SESSION FLOW

```
Login → Create Session → Set Cookies → User Active
                                            ↓
                                    Track Activity
                                            ↓
                                    Auto-Refresh (10 min)
                                            ↓
                                    Show Warning (5 min)
                                            ↓
                                    Expired → Logout
```

---

## 🧪 TESTING

### **Test Session Timeout**
```env
# Set to 1 minute for testing
SESSION_TIMEOUT_MINUTES=1
JWT_EXPIRATION=60
```

### **Test Warning Modal**
```javascript
// Set short timeout
const sessionManager = new SessionManager({
    sessionTimeout: 2 * 60 * 1000,  // 2 minutes
    warningTime: 1 * 60 * 1000,     // 1 minute warning
});
```

---

## 🔍 TROUBLESHOOTING

### **Session expired too quickly?**
```env
# Increase timeout
SESSION_TIMEOUT_MINUTES=60
JWT_EXPIRATION=3600
```

### **Cookie not set?**
Check HTTPS settings:
```env
SESSION_COOKIE_SECURE=true  # For HTTPS
SESSION_COOKIE_SECURE=false # For HTTP (dev)
```

### **Auto-refresh not working?**
Check console logs:
```javascript
// Should see:
[Session] Session manager initialized
[Session] Remaining: 1800s
[Session] Auto-refreshed successfully
```

---

## 📁 FILES

### **Backend:**
- `src/utils/Session.php` - Session manager
- `src/controllers/AuthController.php` - Login/logout with session
- `src/middleware/AuthMiddleware.php` - Session validation
- `config/app.php` - Configuration

### **Frontend:**
- `public/js/session-manager.js` - Session handler

### **Documentation:**
- `.docs/PRD/18-sessions-and-cookies.md` - Full documentation
- `session-quick-guide.md` - This file

---

## ✅ CHECKLIST

- [x] Session timeout 30 menit
- [x] HTTP-only cookies
- [x] Auto-refresh enabled
- [x] Warning modal implemented
- [x] Activity tracking
- [x] Security features
- [x] API endpoints
- [x] Frontend integration
- [x] Documentation

---

## 📚 FULL DOCUMENTATION

Lihat dokumentasi lengkap di: `.docs/PRD/18-sessions-and-cookies.md`

---

**Version:** 1.0.0  
**Last Updated:** 2025-05-21  
**Status:** ✅ Ready for Production

