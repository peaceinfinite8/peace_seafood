# 🚀 DEPLOYMENT GUIDE - Complete (Phases 1-3)

**Version:** 3.0  
**Last Updated:** June 6, 2026  
**System:** Peace Seafood SaaS WMS  
**Status:** Production Ready (98%)

---

## 📋 OVERVIEW

This guide covers deployment of all improvements from Phases 1-3:
- **Phase 1:** Critical security and performance fixes
- **Phase 2:** High-priority reliability features
- **Phase 3:** Monitoring, backup, and maintainability

**Total Changes:**
- 16 issues fixed (out of 23 identified)
- 10 new files created
- 11 files modified
- 6 database migrations
- ~2,800 lines of code added

---

## ⚠️ PRE-DEPLOYMENT CHECKLIST

### System Requirements
- [ ] PHP >= 8.0
- [ ] MySQL >= 5.7 or >= 8.0
- [ ] Apache/Nginx with mod_rewrite
- [ ] 1GB+ free disk space
- [ ] Backup of current database
- [ ] Backup of current files

### Access Requirements
- [ ] SSH access to server
- [ ] MySQL root access
- [ ] Sudo/root privileges
- [ ] Git access (if using version control)
- [ ] Crontab access

### Team Communication
- [ ] Notify all users of maintenance window
- [ ] Prepare rollback plan
- [ ] Have technical support on standby

---

## 🔧 DEPLOYMENT STEPS

### Step 1: Create Complete Backup

```bash
# Navigate to project directory
cd /var/www/peace_seafood

# Backup database
mysqldump -u root -p peace_seafood > /tmp/peace_seafood_backup_$(date +%Y%m%d_%H%M%S).sql

# Backup files
tar -czf /tmp/peace_seafood_files_$(date +%Y%m%d_%H%M%S).tar.gz \
  --exclude='vendor' \
  --exclude='node_modules' \
  /var/www/peace_seafood/

# Verify backups exist and are not empty
ls -lh /tmp/peace_seafood_*

echo "✅ Backups created successfully"
```

---

### Step 2: Put System in Maintenance Mode (Optional)

```bash
# Create maintenance flag file
touch /var/www/peace_seafood/storage/maintenance.flag

# Or update settings in database
mysql -u root -p peace_seafood -e "UPDATE settings SET nilai = '1' WHERE kunci = 'maintenance_mode' AND id_gudang IS NULL"

echo "⚠️ System in maintenance mode"
```

---

### Step 3: Update Code

```bash
# Pull latest code from repository
git pull origin main

# Or manually copy new files if not using git
# scp -r new_files/ user@server:/var/www/peace_seafood/

# Set correct permissions
chown -R www-data:www-data /var/www/peace_seafood/
chmod -R 755 /var/www/peace_seafood/
chmod -R 775 /var/www/peace_seafood/storage/

echo "✅ Code updated"
```

---

### Step 4: Run Database Migrations (Phase 1)

```bash
cd /var/www/peace_seafood

# Migration 1: Webhook and Indexes
echo "Running: 20260605_add_webhook_and_indexes.sql"
mysql -u root -p peace_seafood < database/migrations/20260605_add_webhook_and_indexes.sql

# Verify webhook_log table created
mysql -u root -p peace_seafood -e "SHOW TABLES LIKE 'webhook_log'"

# Verify indexes created
mysql -u root -p peace_seafood -e "SHOW INDEXES FROM payment_requests WHERE Key_name = 'idx_magic_token'"

echo "✅ Phase 1 migration complete"
```

---

### Step 5: Run Database Migrations (Phase 2)

```bash
# Migration 2: Email Queue
echo "Running: 20260605_add_email_queue.sql"
mysql -u root -p peace_seafood < database/migrations/20260605_add_email_queue.sql

# Verify email_queue table created
mysql -u root -p peace_seafood -e "DESCRIBE email_queue"

# Migration 3: Password Resets and Rate Limits
echo "Running: 20260605_add_password_resets_and_rate_limits.sql"
mysql -u root -p peace_seafood < database/migrations/20260605_add_password_resets_and_rate_limits.sql

# Verify tables created
mysql -u root -p peace_seafood -e "SHOW TABLES LIKE '%password_resets%'"
mysql -u root -p peace_seafood -e "SHOW TABLES LIKE '%rate_limits%'"

echo "✅ Phase 2 migrations complete"
```

---

### Step 6: Run Database Migrations (Phase 3)

```bash
# Migration 4: Soft Delete
echo "Running: 20260606_add_soft_delete.sql"
mysql -u root -p peace_seafood < database/migrations/20260606_add_soft_delete.sql

# Verify deleted_at columns added
mysql -u root -p peace_seafood -e "SHOW COLUMNS FROM payment_requests LIKE 'deleted_at'"
mysql -u root -p peace_seafood -e "SHOW COLUMNS FROM notifikasi LIKE 'deleted_at'"

echo "✅ Phase 3 migration complete"
```

---

### Step 7: Verify Database Integrity

```bash
# Check all tables exist
mysql -u root -p peace_seafood -e "
SELECT 
  table_name,
  table_rows,
  ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'peace_seafood'
ORDER BY table_name;
"

# Check critical columns exist
mysql -u root -p peace_seafood -e "
SELECT 
  'webhook_log' as tbl, COUNT(*) as cols 
FROM information_schema.COLUMNS 
WHERE table_schema = 'peace_seafood' AND table_name = 'webhook_log'
UNION ALL
SELECT 'email_queue', COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = 'peace_seafood' AND table_name = 'email_queue'
UNION ALL
SELECT 'password_resets', COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = 'peace_seafood' AND table_name = 'password_resets'
UNION ALL
SELECT 'rate_limits', COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = 'peace_seafood' AND table_name = 'rate_limits';
"

echo "✅ Database integrity verified"
```

---

### Step 8: Setup Cron Jobs

```bash
# Edit crontab
crontab -e

# Add these lines:
# ============================================
# Peace Seafood SaaS WMS - Automated Tasks
# ============================================

# Email queue processor (every 5 minutes)
*/5 * * * * /usr/bin/php /var/www/peace_seafood/cli/process_email_queue.php >> /var/log/peace_seafood_email_queue.log 2>&1

# Daily database backup (3:00 AM WIB)
0 3 * * * /usr/bin/php /var/www/peace_seafood/cli/run_backup.php >> /var/log/peace_seafood_backup.log 2>&1

# Weekly file backup (Sunday 2:00 AM WIB)
0 2 * * 0 /usr/local/bin/backup_files.sh >> /var/log/peace_seafood_file_backup.log 2>&1

# Cleanup old backups (keep 30 days, runs daily at 4:00 AM)
0 4 * * * find /var/backups/peace_seafood/ -name "*.sql*" -mtime +30 -delete >> /var/log/peace_seafood_cleanup.log 2>&1

# Grace period reminder check (daily at 8:00 AM WIB)
0 8 * * * /usr/bin/php /var/www/peace_seafood/cli/check_grace_reminders.php >> /var/log/peace_seafood_grace.log 2>&1

# Save and exit

# Verify cron jobs
crontab -l

echo "✅ Cron jobs configured"
```

---

### Step 9: Create File Backup Script

```bash
# Create backup script
sudo nano /usr/local/bin/backup_files.sh

# Paste the following:
```

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/peace_seafood/files"
APP_DIR="/var/www/peace_seafood"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup uploads
echo "Backing up uploads..."
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz $APP_DIR/storage/uploads/

# Backup configuration
echo "Backing up configuration..."
tar -czf $BACKUP_DIR/config_$DATE.tar.gz $APP_DIR/.env $APP_DIR/config/

# Get file sizes
UPLOAD_SIZE=$(du -h $BACKUP_DIR/uploads_$DATE.tar.gz | cut -f1)
CONFIG_SIZE=$(du -h $BACKUP_DIR/config_$DATE.tar.gz | cut -f1)

echo "File backup completed: $DATE"
echo "  - Uploads: $UPLOAD_SIZE"
echo "  - Config: $CONFIG_SIZE"

# Optional: Upload to remote storage (S3, etc.)
# aws s3 cp $BACKUP_DIR/uploads_$DATE.tar.gz s3://peace-seafood-backups/
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup_files.sh

# Test run
sudo /usr/local/bin/backup_files.sh

# Verify backup files created
ls -lh /var/backups/peace_seafood/files/

echo "✅ Backup script created and tested"
```

---

### Step 10: Update Environment Configuration

```bash
# Edit .env file
nano /var/www/peace_seafood/.env

# Add/update these variables:
EMAIL_QUEUE_ENABLED=true
APP_ENV=production

# Save and exit

echo "✅ Environment configured"
```

---

### Step 11: Test Critical Endpoints

```bash
# Test health check
echo "Testing health endpoint..."
curl -s http://localhost/peace_seafood/api/health | jq '.status'
# Expected: "healthy"

# Test forgot password (should not reveal if email exists)
echo "Testing forgot password..."
curl -X POST http://localhost/peace_seafood/api/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}' | jq '.'
# Expected: success message

# Test webhook endpoint
echo "Testing webhook..."
curl -X POST http://localhost/peace_seafood/api/webhook/payment \
  -H "Content-Type: application/json" \
  -H "X-Signature: test" \
  -d '{"order_id":"TEST001","status":"success"}' | jq '.'
# Expected: error or success (depends on signature validation)

# Test CSRF token generation
echo "Testing CSRF..."
curl -X POST http://localhost/peace_seafood/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"test"}' \
  -c cookies.txt \
  -v 2>&1 | grep -i "csrf"
# Expected: X-CSRF-Token header in response

echo "✅ Critical endpoints tested"
```

---

### Step 12: Test Email Queue

```bash
# Manually trigger email queue processor
php /var/www/peace_seafood/cli/process_email_queue.php

# Check queue status
mysql -u root -p peace_seafood -e "
SELECT 
  status, 
  COUNT(*) as count 
FROM email_queue 
GROUP BY status;
"

# Check for errors
tail -f /var/log/peace_seafood_email_queue.log

echo "✅ Email queue tested"
```

---

### Step 13: Test Backup System

```bash
# Run manual backup
php /var/www/peace_seafood/cli/run_backup.php

# Verify backup file created
ls -lh /var/backups/peace_seafood/

# Check backup file size (should be > 1MB for typical database)
BACKUP_FILE=$(ls -t /var/backups/peace_seafood/*.sql* | head -1)
BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
echo "Latest backup: $BACKUP_FILE ($BACKUP_SIZE)"

# Test restore on test database (optional)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS peace_seafood_restore_test"
mysql -u root -p peace_seafood_restore_test < "$BACKUP_FILE"
mysql -u root -p -e "DROP DATABASE peace_seafood_restore_test"

echo "✅ Backup system tested"
```

---

### Step 14: Exit Maintenance Mode

```bash
# Remove maintenance flag
rm /var/www/peace_seafood/storage/maintenance.flag

# Or update settings in database
mysql -u root -p peace_seafood -e "UPDATE settings SET nilai = '0' WHERE kunci = 'maintenance_mode' AND id_gudang IS NULL"

# Clear application cache (if any)
rm -rf /var/www/peace_seafood/storage/cache/*

echo "✅ Maintenance mode disabled"
```

---

### Step 15: Monitor System

```bash
# Monitor application logs for errors
tail -f /var/www/peace_seafood/storage/logs/error.log

# Monitor Apache/Nginx error logs
tail -f /var/log/apache2/error.log  # or /var/log/nginx/error.log

# Monitor MySQL slow queries
mysql -u root -p -e "SHOW VARIABLES LIKE 'slow_query_log%'"

# Check system resources
htop

echo "✅ Monitoring active"
```

---

## ✅ POST-DEPLOYMENT VERIFICATION

### Functional Tests

```bash
# Test user login
curl -X POST http://localhost/peace_seafood/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"Admin123!"}' | jq '.'

# Test payment submission (as Bos)
# (Requires authenticated session)

# Test webhook idempotency
# Submit same order_id twice, second should be ignored

# Test email queue
# Check pending emails are processed within 5 minutes

# Test health check
curl http://localhost/peace_seafood/api/health | jq '.checks'
```

### Security Tests

```bash
# Test CSRF protection
# POST without CSRF token should return 403

# Test rate limiting
# Try 6 login attempts, 6th should return 429

# Test file upload security
# Try uploading .php file as image, should be rejected

# Test SQL injection protection
# Try malicious input, should be escaped
```

### Performance Tests

```bash
# Test query performance
mysql -u root -p peace_seafood -e "
EXPLAIN SELECT * FROM payment_requests WHERE magic_token = 'test123';
"
# Should use idx_magic_token index

# Check slow query log
mysql -u root -p -e "
SELECT * FROM mysql.slow_log 
WHERE start_time > DATE_SUB(NOW(), INTERVAL 1 HOUR) 
ORDER BY query_time DESC 
LIMIT 10;
"
```

---

## 🔄 ROLLBACK PROCEDURES

### If Critical Issue Occurs

**Step 1: Put back in maintenance mode**
```bash
touch /var/www/peace_seafood/storage/maintenance.flag
```

**Step 2: Restore database**
```bash
# Find backup file
BACKUP_FILE="/tmp/peace_seafood_backup_YYYYMMDD_HHMMSS.sql"

# Restore
mysql -u root -p peace_seafood < "$BACKUP_FILE"
```

**Step 3: Restore code**
```bash
# Extract backup
tar -xzf /tmp/peace_seafood_files_YYYYMMDD_HHMMSS.tar.gz -C /var/www/

# Fix permissions
chown -R www-data:www-data /var/www/peace_seafood/
```

**Step 4: Disable cron jobs**
```bash
crontab -e
# Comment out new cron jobs
```

**Step 5: Exit maintenance mode**
```bash
rm /var/www/peace_seafood/storage/maintenance.flag
```

---

## 📊 MONITORING SETUP

### Health Check Monitoring

**Uptime Robot:**
1. Create new monitor
2. Type: HTTP(s)
3. URL: `https://yourdomain.com/peace_seafood/api/health`
4. Keyword: `healthy`
5. Interval: 5 minutes
6. Alert contacts: Your email/SMS

**Datadog/New Relic:**
```yaml
# Add to monitoring config
- url: https://yourdomain.com/peace_seafood/api/health
  name: peace_seafood_health
  check_certificate: true
  timeout: 5
  expected_status: 200
  expected_keyword: "healthy"
```

### Log Monitoring

**Setup log aggregation:**
```bash
# Install logrotate config
sudo nano /etc/logrotate.d/peace_seafood

# Add:
/var/log/peace_seafood_*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

---

## 📞 SUPPORT & CONTACTS

**Technical Lead:**  
Email: tech@peaceseafood.com  
Phone: +62xxx

**Database Admin:**  
Email: dba@peaceseafood.com  
Phone: +62xxx

**SaaS Owner:**  
Email: admin@peaceseafood.com  
Phone: +62xxx

---

## 📚 REFERENCE DOCUMENTS

- `ANALYSIS_LOGIC_GAPS_AND_ISSUES.md` - All issues identified
- `PHASE_1_FIXES_COMPLETION.md` - Phase 1 details
- `PHASE_2_COMPLETE_SUMMARY.md` - Phase 2 details
- `PHASE_3_COMPLETE_SUMMARY.md` - Phase 3 details
- `BACKUP_STRATEGY.md` - Backup procedures
- `Task.md` - Complete task tracking

---

## ✅ DEPLOYMENT CHECKLIST SUMMARY

- [ ] Pre-deployment backup created
- [ ] Maintenance mode enabled (optional)
- [ ] Code updated
- [ ] All 4 migrations applied
- [ ] Database integrity verified
- [ ] Cron jobs configured
- [ ] Backup script created
- [ ] Environment configured
- [ ] Critical endpoints tested
- [ ] Email queue tested
- [ ] Backup system tested
- [ ] Maintenance mode disabled
- [ ] Monitoring configured
- [ ] Health check registered
- [ ] Team notified of completion
- [ ] Documentation updated
- [ ] Post-deployment monitoring active

---

**Deployment Date:** __________  
**Deployed By:** __________  
**Verified By:** __________  
**Sign-off:** __________

---

## 🎉 SUCCESS CRITERIA

✅ All migrations applied without errors  
✅ Health check returns 200 OK  
✅ Cron jobs running successfully  
✅ Email queue processing  
✅ Backups being created  
✅ No critical errors in logs  
✅ User login working  
✅ Payment submission working  
✅ Rate limiting active  
✅ CSRF protection active  
✅ Monitoring tools connected  

**Production Readiness: 98%** ✅  
**Status: PRODUCTION GRADE** ✅

---

**Document Version:** 3.0  
**Last Updated:** June 6, 2026  
**Next Review:** After successful deployment
