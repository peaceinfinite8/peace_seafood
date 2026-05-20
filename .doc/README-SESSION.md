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
- `.doc/18-session-cookies.md` - Full documentation
- `README-SESSION.md` - This file

---

**Version:** 1.0.0  
**Last Updated:** 2025-05-21  
**Status:** ✅ Ready for Production
