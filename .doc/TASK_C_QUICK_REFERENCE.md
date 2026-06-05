# 🚀 Task C - Quick Reference Cheat Sheet

## One-Command Setup

```bash
# 1. Run migration
mysql -u root -p peace_seafood < database/migrations/20260605_add_grace_period_tables.sql

# 2. Test grace reminder
php cli/check_grace_reminders.php

# 3. Add cron job
echo "0 8 * * * cd $(pwd) && php cli/check_grace_reminders.php >> /var/log/grace.log 2>&1" | crontab -
```

---

## 📡 API Endpoints

### Grace Period
```bash
# Get banner data
curl -X GET http://localhost/peace_seafood/api/grace-period/banner \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Manual trigger reminders (admin only)
curl -X POST http://localhost/peace_seafood/api/grace-period/check-reminders \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Notifications
```bash
# Get unread count (polling)
curl -X GET http://localhost/peace_seafood/api/notifications/unread-count \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Get all notifications
curl -X GET http://localhost/peace_seafood/api/notifications?limit=50 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Mark as read
curl -X POST http://localhost/peace_seafood/api/notifications/123/read \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Mark all as read
curl -X POST http://localhost/peace_seafood/api/notifications/mark-all-read \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

---

## 💻 Code Snippets

### Create Notification (PHP)

```php
use App\Services\Shared\NotificationService;

// Single user
NotificationService::create(
    idUser: 123,
    tipe: 'grace_period',
    judul: 'Masa Aktif Tersisa 3 Hari',
    pesan: 'Gudang akan terkunci dalam 3 hari',
    referenceId: null,
    referenceTipe: null,
    actionUrl: '/settings'
);

// By role
NotificationService::createForRole(
    idGudang: 5,
    role: 'admin',
    tipe: 'stok',
    judul: 'Stok Hampir Habis',
    pesan: 'Salmon tinggal 5kg',
    referenceId: 42,
    referenceTipe: 'produk',
    actionUrl: '/stok'
);

// All users in gudang
NotificationService::createForGudang(
    idGudang: 5,
    tipe: 'payment',
    judul: 'Akses Dipulihkan',
    pesan: 'Pembayaran disetujui',
    referenceId: null,
    referenceTipe: null,
    actionUrl: '/dashboard'
);
```

### Check Grace Period (PHP)

```php
use App\Services\WMS\GracePeriodService;

// Get remaining days
$days = GracePeriodService::getRemainingDays($idGudang);

// Get banner data
$banner = GracePeriodService::getBannerData($idGudang);
// Returns: ['show' => true/false, 'color' => 'yellow'|'red', 'days' => int, 'message' => string]

// Send reminders
$result = GracePeriodService::checkAndSendReminders();
// Returns: ['success' => bool, 'sent' => int]
```

---

## 🗄️ SQL Queries

### Check Grace Status
```sql
-- All gudang expiring soon
SELECT 
    g.id,
    g.nama,
    g.subscription_until,
    DATEDIFF(g.subscription_until, NOW()) as days_remaining,
    u.email as bos_email
FROM gudang g
JOIN users u ON g.id_bos = u.id
WHERE g.subscription_until IS NOT NULL
  AND g.is_active = 1
  AND DATEDIFF(g.subscription_until, NOW()) <= 7
ORDER BY days_remaining ASC;
```

### Email Log History
```sql
-- Recent grace emails
SELECT 
    l.trigger,
    l.sent_at,
    l.email_to,
    g.nama as gudang_name
FROM grace_email_log l
JOIN gudang g ON l.id_gudang = g.id
WHERE DATE(l.sent_at) = CURDATE()
ORDER BY l.sent_at DESC;
```

### Notification Stats
```sql
-- Unread count per user
SELECT 
    u.name,
    u.role,
    COUNT(*) as unread_count
FROM notifikasi n
JOIN users u ON n.id_user = u.id
WHERE n.is_read = 0
GROUP BY u.id
ORDER BY unread_count DESC;
```

### Update Settings
```sql
-- Enable/disable email reminders
UPDATE settings SET nilai = '0' WHERE kunci = 'notification_email_reminder' AND id_gudang IS NULL;

-- Change grace thresholds
UPDATE settings SET nilai = '5' WHERE kunci = 'grace_red_days' AND id_gudang IS NULL;
UPDATE settings SET nilai = '10' WHERE kunci = 'grace_yellow_days' AND id_gudang IS NULL;

-- Update WhatsApp number
UPDATE settings SET nilai = '628123456789' WHERE kunci = 'platform_whatsapp' AND id_gudang IS NULL;
```

---

## 🧪 Testing Scenarios

### Scenario 1: Test Yellow Banner
```sql
-- Set gudang to expire in 7 days
UPDATE gudang SET subscription_until = DATE_ADD(NOW(), INTERVAL 7 DAYS) WHERE id = 1;
-- Expected: Yellow banner appears
```

### Scenario 2: Test Red Banner
```sql
-- Set gudang to expire in 3 days
UPDATE gudang SET subscription_until = DATE_ADD(NOW(), INTERVAL 3 DAYS) WHERE id = 1;
-- Expected: Red banner appears
```

### Scenario 3: Test Email Reminder
```bash
# Set expiry and run script
mysql -u root -p -e "UPDATE peace_seafood.gudang SET subscription_until = DATE_ADD(NOW(), INTERVAL 3 DAYS) WHERE id = 1;"
php cli/check_grace_reminders.php
# Expected: Email sent, logged in grace_email_log
```

### Scenario 4: Test Notification Bell
```sql
-- Create test notification
INSERT INTO notifikasi (id_user, tipe, judul, pesan, is_read) 
VALUES (1, 'grace_period', 'Test Notif', 'This is a test', 0);
-- Expected: Badge count increases, appears in dropdown
```

---

## 🎨 CSS Classes

All custom classes use `gpa-` prefix:

```css
/* Banner */
.gpa-banner              /* Container */
.gpa-banner-yellow       /* Yellow variant */
.gpa-banner-red          /* Red variant */
.gpa-banner-content      /* Content wrapper */
.gpa-banner-icon         /* Icon */
.gpa-banner-text         /* Text content */
.gpa-banner-title        /* Title text */
.gpa-banner-action       /* Action button wrapper */
.gpa-btn-whatsapp        /* WhatsApp button */

/* Notification Bell */
.gpa-notif-bell-container   /* Container */
.gpa-notif-bell-btn         /* Bell button */
.gpa-notif-badge            /* Badge circle */
.gpa-notif-dropdown         /* Dropdown */
.gpa-notif-header           /* Dropdown header */
.gpa-notif-title            /* Title */
.gpa-notif-mark-all         /* Mark all button */
.gpa-notif-list             /* List container */
.gpa-notif-item             /* Single item */
.gpa-notif-item.unread      /* Unread item */
.gpa-notif-empty            /* Empty state */
```

---

## 🔧 Debugging

### Check if Grace Service Works
```php
// In browser console or PHP file
var_dump(\App\Services\WMS\GracePeriodService::getRemainingDays(1));
var_dump(\App\Services\WMS\GracePeriodService::getBannerData(1));
```

### Check if Notification Service Works
```php
$count = \App\Services\Shared\NotificationService::getUnreadCount(1);
var_dump($count);

$notifs = \App\Services\Shared\NotificationService::getForUser(1, 10, 0);
var_dump($notifs);
```

### Check Settings
```sql
SELECT * FROM settings WHERE kunci LIKE 'grace%' OR kunci LIKE 'notification%';
```

### Check Email Config
```php
// Test email sending
$result = \App\Utils\Email::send(
    'test@example.com',
    'Test Email',
    '<h1>Test</h1><p>This is a test email</p>'
);
var_dump($result);
```

---

## 🚨 Common Issues & Fixes

### Issue: Banner not showing
```bash
# Check if subscription_until is set
mysql -u root -p -e "SELECT id, nama, subscription_until FROM peace_seafood.gudang WHERE id = 1;"

# Check if grace settings exist
mysql -u root -p -e "SELECT * FROM peace_seafood.settings WHERE kunci LIKE 'grace%';"
```

### Issue: Email not sent
```bash
# Check email config
php -r "require 'vendor/autoload.php'; \$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); \$dotenv->load(); echo getenv('MAIL_HOST');"

# Check if reminders enabled
mysql -u root -p -e "SELECT * FROM peace_seafood.settings WHERE kunci = 'notification_email_reminder';"

# Run CLI with verbose output
php cli/check_grace_reminders.php
```

### Issue: Bell not polling
```javascript
// In browser console
console.log(apiClient); // Should exist
console.log(Alpine.store('auth')); // Should have user data

// Check endpoint manually
fetch('/peace_seafood/api/notifications/unread-count', {
  headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') }
}).then(r => r.json()).then(console.log);
```

### Issue: Cron not running
```bash
# Check cron logs
tail -f /var/log/cron

# Check file permissions
chmod +x cli/check_grace_reminders.php

# Test cron command manually
cd /path/to/peace_seafood && php cli/check_grace_reminders.php
```

---

## 📋 Settings Reference

| Setting Key | Type | Default | Description |
|-------------|------|---------|-------------|
| `grace_yellow_days` | int | 7 | Yellow banner threshold (days) |
| `grace_red_days` | int | 3 | Red banner threshold (days) |
| `grace_warning_day_1` | int | 7 | First email reminder (H-7) |
| `grace_warning_day_2` | int | 3 | Second email reminder (H-3) |
| `grace_warning_day_3` | int | 1 | Third email reminder (H-1) |
| `notification_email_reminder` | bool | 1 | Enable email reminders |
| `notification_digest` | bool | 1 | Enable daily digest |
| `notification_inapp` | bool | 1 | Enable in-app notifications |
| `digest_send_time` | time | 08:00 | Daily digest send time |
| `timezone` | string | Asia/Jakarta | System timezone |
| `platform_whatsapp` | string | 628123456789 | Support WhatsApp number |

---

## 📞 Quick Commands

```bash
# Enable all notifications
mysql -u root -p peace_seafood -e "
UPDATE settings SET nilai = '1' 
WHERE kunci IN ('notification_email_reminder', 'notification_digest', 'notification_inapp') 
AND id_gudang IS NULL;"

# Disable all notifications
mysql -u root -p peace_seafood -e "
UPDATE settings SET nilai = '0' 
WHERE kunci IN ('notification_email_reminder', 'notification_digest', 'notification_inapp') 
AND id_gudang IS NULL;"

# Clear all notifications
mysql -u root -p peace_seafood -e "DELETE FROM notifikasi WHERE tipe = 'grace_period';"

# Clear grace email log
mysql -u root -p peace_seafood -e "DELETE FROM grace_email_log;"

# Reset all gudang expiry to +30 days
mysql -u root -p peace_seafood -e "
UPDATE gudang SET subscription_until = DATE_ADD(NOW(), INTERVAL 30 DAY) 
WHERE is_active = 1;"
```

---

## 🎯 Production Checklist

Before deploying to production:

- [ ] Migration executed on production DB
- [ ] Settings configured with real values
- [ ] Email SMTP tested and working
- [ ] WhatsApp number updated to real support number
- [ ] Cron job added to production server
- [ ] Timezone setting matches production location
- [ ] All API endpoints tested with Postman
- [ ] Frontend tested on major browsers (Chrome, Firefox, Safari)
- [ ] Mobile responsive tested
- [ ] Error logging configured
- [ ] Backup database before deployment

---

*Last updated: June 5, 2026*  
*Task C - Peace Seafood SaaS WMS*
