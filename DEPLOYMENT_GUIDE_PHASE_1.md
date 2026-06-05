# 🚀 DEPLOYMENT GUIDE - Phase 1 Fixes

**Target:** Production/Staging Environment  
**Date:** June 5, 2026  
**Estimated Downtime:** 5-10 minutes  
**Risk Level:** Low-Medium

---

## 📋 PRE-DEPLOYMENT CHECKLIST

### **1. Backup Everything**
```bash
# Database backup
mysqldump -u root -p peace_seafood > backups/peace_seafood_$(date +%Y%m%d_%H%M%S).sql

# Code backup
tar -czf backups/peace_seafood_code_$(date +%Y%m%d_%H%M%S).tar.gz \
  /var/www/peace_seafood \
  --exclude=node_modules \
  --exclude=vendor

# Verify backups
ls -lh backups/
```

### **2. Check Server Requirements**
```bash
# PHP version (need 8.0+)
php -v

# MySQL version
mysql --version

# Required PHP extensions
php -m | grep -E "pdo|mysqli|gd|fileinfo|openssl"

# Disk space (need at least 1GB free)
df -h
```

### **3. Test Environment Setup**
```bash
# Copy .env.example if needed
cp .env.example .env

# Set environment
# APP_ENV=production
# WEBHOOK_SECRET=generate-secure-random-string-here
```

---

## 🔧 DEPLOYMENT STEPS

### **Step 1: Pull Latest Code**
```bash
cd /var/www/peace_seafood

# Stash any local changes
git stash

# Pull latest
git pull origin main

# Or copy files manually if not using git
# scp -r ./src ./routes ./public ./database user@server:/var/www/peace_seafood/
```

### **Step 2: Run Database Migration**
```bash
# Ensure you're in project root
cd /var/www/peace_seafood

# Run migration
mysql -u root -p peace_seafood < database/migrations/20260605_add_webhook_and_indexes.sql

# Verify indexes created
mysql -u root -p peace_seafood -e "SHOW INDEX FROM payment_requests;"
```

**Expected Output:**
```
Table: payment_requests
Key_name: idx_magic_token
Key_name: idx_order_id
Key_name: idx_gudang_status
Key_name: idx_status_created
```

### **Step 3: Update File Permissions**
```bash
# Set correct permissions
chmod 755 storage/uploads/payment_proofs
chmod 644 storage/uploads/payment_proofs/.htaccess

# Ensure web server can write
chown -R www-data:www-data storage/uploads/payment_proofs
```

### **Step 4: Clear Cache (if any)**
```bash
# Clear PHP opcode cache
php -r "opcache_reset();"

# Or restart PHP-FPM
sudo systemctl restart php8.0-fpm

# Restart web server
sudo systemctl restart apache2
# OR
sudo systemctl restart nginx
```

### **Step 5: Test Critical Endpoints**

**Test 1: Health Check**
```bash
curl -I http://your-domain.com/peace_seafood/
# Expected: HTTP/1.1 200 OK
```

**Test 2: Login (get CSRF token)**
```bash
curl -X POST http://your-domain.com/peace_seafood/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "saas@peaceseafood.com",
    "password": "password123"
  }'

# Expected response should include:
# "csrf_token": "abc123..."
```

**Test 3: Webhook Endpoint**
```bash
curl -X POST http://your-domain.com/peace_seafood/api/webhook/test \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'

# Expected: {"success": true, "payload": {"test": "data"}}
```

**Test 4: CSRF Protection**
```bash
# This should FAIL with 403
curl -X POST http://your-domain.com/peace_seafood/api/settings \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'

# Expected: 403 CSRF token validation failed
```

**Test 5: File Upload (via UI)**
- Login as Bos
- Navigate to lock screen (if subscription expired)
- Try upload valid JPG → Should work
- Try upload renamed .exe → Should reject

---

## ✅ POST-DEPLOYMENT VERIFICATION

### **1. Database Checks**
```sql
-- Check webhook_log table exists
SHOW TABLES LIKE 'webhook_log';

-- Check indexes
SHOW INDEX FROM payment_requests;
SHOW INDEX FROM webhook_log;
SHOW INDEX FROM activity_log;

-- Check new columns
DESCRIBE payment_requests;
-- Should see: order_id, payment_method, webhook_payload
```

### **2. Log Monitoring**
```bash
# Watch logs for errors
tail -f /var/log/apache2/error.log
# OR
tail -f /var/log/nginx/error.log

# Watch application logs
tail -f storage/logs/error.log

# Look for these patterns (should NOT appear):
# - "Race condition"
# - "CSRF validation failed" (when legitimate request)
# - "SQL injection"
# - "File upload security"
```

### **3. Functionality Tests**

**Test Payment Approval:**
1. Submit payment as Bos (locked gudang)
2. Check email received by SaaS Owner
3. Click Magic Link
4. Verify:
   - ✅ Subscription extended
   - ✅ Notification created
   - ✅ Activity logged
   - ✅ Double-click prevented

**Test Webhook:**
```bash
# Send test webhook
curl -X POST http://your-domain.com/peace_seafood/api/webhook/payment \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "TEST_DEPLOY_001",
    "id_gudang": 1,
    "amount": 500000,
    "status": "paid",
    "payment_method": "bank_transfer"
  }'

# Check webhook_log table
mysql -u root -p peace_seafood -e \
  "SELECT * FROM webhook_log WHERE order_id='TEST_DEPLOY_001';"
```

**Test CSRF:**
1. Login via UI
2. Open browser DevTools → Network
3. Submit any form (create penjualan, etc)
4. Verify request headers include: `X-CSRF-Token`
5. Verify response is 200 (not 403)

**Test File Upload:**
1. Create test files:
   ```bash
   # Valid image
   convert -size 100x100 xc:red test_valid.jpg
   
   # Malicious PHP disguised as JPG
   echo "<?php phpinfo(); ?>" > test_malicious.jpg
   ```
2. Try upload via UI
3. Valid → should work
4. Malicious → should reject with security message

### **4. Performance Checks**
```bash
# Test query performance with EXPLAIN
mysql -u root -p peace_seafood -e "
  EXPLAIN SELECT * FROM payment_requests WHERE magic_token = 'test';
"
# Should show: type=ref, key=idx_magic_token (using index)

# Before: type=ALL (full table scan)
# After: type=ref (index lookup)
```

---

## 🔄 ROLLBACK PROCEDURE (If Issues Found)

### **Option 1: Quick Rollback (Code Only)**
```bash
# Restore code from backup
cd /var/www/
rm -rf peace_seafood
tar -xzf backups/peace_seafood_code_TIMESTAMP.tar.gz

# Restart services
sudo systemctl restart php8.0-fpm
sudo systemctl restart apache2
```

### **Option 2: Full Rollback (Code + Database)**
```bash
# Restore database
mysql -u root -p peace_seafood < backups/peace_seafood_TIMESTAMP.sql

# Restore code (see Option 1)

# Restart services
sudo systemctl restart php8.0-fpm
sudo systemctl restart apache2
```

### **Option 3: Selective Rollback (Keep Indexes)**
```bash
# Only rollback code, keep database improvements
# (Indexes won't hurt even if code rolled back)

# Restore code from backup
cd /var/www/
rm -rf peace_seafood
tar -xzf backups/peace_seafood_code_TIMESTAMP.tar.gz

# Restart services
sudo systemctl restart php8.0-fpm
sudo systemctl restart apache2
```

---

## 📊 MONITORING POST-DEPLOYMENT

### **1. Watch for Errors (First 24 Hours)**
```bash
# Real-time error monitoring
watch -n 5 'tail -20 storage/logs/error.log'

# Count errors per hour
grep "$(date +%Y-%m-%d)" storage/logs/error.log | \
  awk '{print $2}' | cut -d: -f1 | sort | uniq -c
```

### **2. Monitor Performance**
```bash
# Check database slow queries
mysql -u root -p -e "SHOW PROCESSLIST;"

# Check Apache/Nginx connections
netstat -an | grep :80 | wc -l

# Check PHP-FPM status
sudo systemctl status php8.0-fpm
```

### **3. Key Metrics to Track**

| Metric | Check Method | Healthy Range |
|--------|-------------|---------------|
| Response Time | `curl -w "%{time_total}" http://domain/` | < 500ms |
| Error Rate | Count 500 errors in logs | < 0.1% |
| Database Connections | `SHOW PROCESSLIST` | < 100 |
| Disk Space | `df -h` | > 20% free |
| Memory Usage | `free -h` | < 80% used |

### **4. User Feedback**
- Monitor support tickets/complaints
- Check for reports of:
  - CSRF errors on forms
  - File upload rejections (false positives)
  - Slow payment approval
  - Duplicate webhook processing

---

## 🆘 TROUBLESHOOTING

### **Issue: CSRF Token Errors**
```bash
# Symptom: Legitimate requests getting 403
# Solution: Clear sessions
rm -rf /var/lib/php/sessions/sess_*
sudo systemctl restart php8.0-fpm
```

### **Issue: Webhook Not Working**
```bash
# Check webhook secret configured
grep WEBHOOK_SECRET .env

# Check webhook_log for errors
mysql -u root -p peace_seafood -e \
  "SELECT * FROM webhook_log ORDER BY created_at DESC LIMIT 10;"

# Test webhook manually
curl -X POST http://localhost/peace_seafood/api/webhook/test \
  -H "Content-Type: application/json" \
  -d '{"test": "manual"}'
```

### **Issue: File Upload Rejections**
```bash
# Check .htaccess loaded
grep "payment_proofs" /etc/apache2/sites-available/default.conf

# Check file permissions
ls -la storage/uploads/payment_proofs/

# Enable debug logging
# In PaymentApprovalService.php, check error_log() output
tail -f storage/logs/error.log | grep "File upload"
```

### **Issue: Slow Queries**
```bash
# Verify indexes actually created
mysql -u root -p peace_seafood -e "SHOW INDEX FROM payment_requests;"

# Check query execution plan
mysql -u root -p peace_seafood -e \
  "EXPLAIN SELECT * FROM payment_requests WHERE magic_token='test';"

# If not using index, rebuild:
mysql -u root -p peace_seafood -e \
  "ALTER TABLE payment_requests DROP INDEX idx_magic_token;"
mysql -u root -p peace_seafood -e \
  "ALTER TABLE payment_requests ADD INDEX idx_magic_token (magic_token);"
```

---

## ✅ DEPLOYMENT SIGN-OFF

**Deployment Checklist:**
- [ ] Backup completed and verified
- [ ] Database migration successful
- [ ] All indexes created
- [ ] File permissions correct
- [ ] Services restarted
- [ ] Health check passed
- [ ] CSRF protection working
- [ ] Webhook endpoint responding
- [ ] File upload security active
- [ ] Performance improved (query times)
- [ ] No errors in logs
- [ ] User acceptance testing passed

**Deployed by:** _________________  
**Date:** _________________  
**Time:** _________________  
**Environment:** [ ] Staging [ ] Production  
**Status:** [ ] Success [ ] Failed [ ] Rolled Back  

**Notes:**
_________________________________________
_________________________________________
_________________________________________

---

## 📞 SUPPORT CONTACTS

**Emergency Rollback:**  
Contact: SysAdmin  
Phone: +62-XXX-XXXX-XXXX

**Database Issues:**  
Contact: DBA Team  
Email: dba@company.com

**Application Issues:**  
Contact: Development Team  
Email: dev@company.com

---

**Document Version:** 1.0  
**Last Updated:** June 5, 2026  
**Next Review:** After Production Deployment
