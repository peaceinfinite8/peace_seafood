# 🗄️ Backup & Disaster Recovery Strategy

**Last Updated:** June 6, 2026  
**Version:** 1.0  
**System:** Peace Seafood SaaS WMS

---

## 📋 OVERVIEW

This document outlines the backup strategy, recovery procedures, and disaster recovery plan for the Peace Seafood SaaS WMS application.

---

## 🔄 AUTOMATED BACKUPS

### Database Backups

**Frequency:** Daily at 3:00 AM WIB (Asia/Jakarta)

**Retention Policy:**
- Daily backups: 30 days
- Weekly backups: 12 weeks (3 months)
- Monthly backups: 12 months (1 year)

**Backup Location:**
- **Primary:** Local server `/var/backups/peace_seafood/`
- **Secondary:** Remote storage (recommended: AWS S3, Google Cloud Storage, or similar)

**Backup Contents:**
- Complete database dump (structure + data)
- All tables including:
  - User data
  - Transaction records
  - Inventory data
  - Activity logs
  - Payment records
  - Configuration settings

### File System Backups

**Frequency:** Weekly (Sunday 2:00 AM WIB)

**Retention:** 8 weeks

**Backup Contents:**
- `/storage/uploads/` - All uploaded files (payment proofs, logos, documents)
- `/storage/logs/` - Application logs
- `.env` file - Environment configuration
- `/config/` - Configuration files

**Exclusions:**
- `/vendor/` - Composer dependencies (can be restored with `composer install`)
- `/node_modules/` - NPM dependencies (can be restored with `npm install`)
- `/storage/cache/` - Temporary cache files
- `/storage/framework/sessions/` - Session files

---

## 🛠️ MANUAL BACKUP

### Database Backup

**Command:**
```bash
# Run backup script
php cli/run_backup.php

# Or manual mysqldump
mysqldump -u root -p peace_seafood > backup_$(date +%Y%m%d_%H%M%S).sql

# With compression
mysqldump -u root -p peace_seafood | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

**Backup Script Location:** `cli/run_backup.php`

**Output Location:** `storage/backups/` or configured backup directory

### File System Backup

**Command:**
```bash
# Backup uploads directory
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz storage/uploads/

# Backup entire application (excluding dependencies)
tar -czf peace_seafood_full_$(date +%Y%m%d).tar.gz \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage/cache' \
  --exclude='storage/logs' \
  /var/www/peace_seafood/
```

---

## 🔧 BACKUP CONFIGURATION

### Cron Jobs Setup

Add to crontab (`crontab -e`):

```bash
# Daily database backup at 3:00 AM WIB
0 3 * * * /usr/bin/php /var/www/peace_seafood/cli/run_backup.php >> /var/log/peace_seafood_backup.log 2>&1

# Weekly file backup on Sunday at 2:00 AM WIB
0 2 * * 0 /usr/local/bin/backup_files.sh >> /var/log/peace_seafood_file_backup.log 2>&1

# Cleanup old backups (keep 30 days)
0 4 * * * find /var/backups/peace_seafood/ -name "*.sql*" -mtime +30 -delete >> /var/log/peace_seafood_cleanup.log 2>&1
```

### File Backup Script

Create `/usr/local/bin/backup_files.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/peace_seafood/files"
APP_DIR="/var/www/peace_seafood"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup uploads
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz $APP_DIR/storage/uploads/

# Backup configuration
tar -czf $BACKUP_DIR/config_$DATE.tar.gz $APP_DIR/.env $APP_DIR/config/

echo "File backup completed: $DATE"
```

Make executable:
```bash
chmod +x /usr/local/bin/backup_files.sh
```

---

## 📥 RESTORE PROCEDURES

### Database Restore

#### Option 1: Full Restore

```bash
# Stop application (optional but recommended)
sudo systemctl stop apache2  # or nginx

# Restore from backup
mysql -u root -p peace_seafood < backup_2026-06-06_030000.sql

# Or from compressed backup
gunzip < backup_2026-06-06_030000.sql.gz | mysql -u root -p peace_seafood

# Restart application
sudo systemctl start apache2
```

#### Option 2: Selective Table Restore

```bash
# Extract specific table from backup
sed -n '/CREATE TABLE `users`/,/UNLOCK TABLES;/p' backup_2026-06-06_030000.sql > users_backup.sql

# Restore specific table
mysql -u root -p peace_seafood < users_backup.sql
```

### File Restore

```bash
# Restore uploads
cd /var/www/peace_seafood/storage/
tar -xzf /var/backups/peace_seafood/files/uploads_20260606_020000.tar.gz

# Restore configuration
cd /var/www/peace_seafood/
tar -xzf /var/backups/peace_seafood/files/config_20260606_020000.tar.gz

# Fix permissions
chown -R www-data:www-data storage/
chmod -R 755 storage/uploads/
```

---

## 🚨 DISASTER RECOVERY PLAN

### Level 1: Data Corruption (Single Table)

**Impact:** Low  
**RTO:** 15 minutes  
**RPO:** Last backup (max 24 hours)

**Steps:**
1. Identify corrupted table
2. Stop write operations to affected table
3. Restore specific table from latest backup
4. Verify data integrity
5. Resume operations

### Level 2: Database Failure

**Impact:** Medium  
**RTO:** 30 minutes  
**RPO:** Last backup (max 24 hours)

**Steps:**
1. Put application in maintenance mode
2. Diagnose database issue
3. Restore full database from latest backup
4. Verify all tables and data
5. Test critical functions
6. Exit maintenance mode

### Level 3: Server Failure

**Impact:** High  
**RTO:** 2-4 hours  
**RPO:** Last backup (max 24 hours)

**Steps:**
1. Provision new server
2. Install required software (PHP, MySQL, Apache/Nginx)
3. Clone application code from repository
4. Restore database from backup
5. Restore file uploads from backup
6. Configure environment (.env)
7. Test all functionality
8. Update DNS if necessary

### Level 4: Complete Data Loss

**Impact:** Critical  
**RTO:** 4-8 hours  
**RPO:** Last backup (max 24 hours)

**Steps:**
1. Follow Level 3 steps
2. Restore from remote backup location (S3/Cloud)
3. Verify data completeness
4. Contact all tenants about potential data loss
5. Document incident for post-mortem

---

## ✅ BACKUP VERIFICATION

### Daily Verification

**Automated checks:**
```bash
# Verify backup file exists and is not empty
BACKUP_FILE="/var/backups/peace_seafood/backup_$(date +%Y%m%d).sql.gz"
if [ -f "$BACKUP_FILE" ] && [ -s "$BACKUP_FILE" ]; then
    echo "Backup verified: $BACKUP_FILE"
else
    echo "ERROR: Backup missing or empty!" | mail -s "Backup Failed" admin@peaceseafood.com
fi
```

**Manual verification (weekly):**
1. Download random backup file
2. Restore to test database
3. Run sample queries
4. Verify data integrity
5. Document verification in log

### Monthly Recovery Drill

**Schedule:** First Sunday of each month

**Procedure:**
1. Select backup from previous month
2. Restore to staging environment
3. Test critical functionality:
   - User login
   - Transaction creation
   - Report generation
   - Payment processing
4. Document results
5. Update procedures if issues found

---

## 📊 BACKUP MONITORING

### Metrics to Track

- Backup success/failure rate
- Backup file size (monitor for anomalies)
- Backup duration
- Restore test success rate
- Storage space utilization

### Alerts

**Critical Alerts:**
- Backup failed for 2 consecutive days
- Backup file size < 50% of average
- Storage space < 10GB free

**Warning Alerts:**
- Backup duration > 2x average
- Storage space < 20GB free
- Backup file size variation > 30%

**Notification Channels:**
- Email: admin@peaceseafood.com
- SMS: +62xxx (SaaS Owner)
- Slack/Discord: #alerts channel

---

## 🔐 BACKUP SECURITY

### Encryption

**At Rest:**
```bash
# Encrypt backup file
gpg --encrypt --recipient admin@peaceseafood.com backup_20260606.sql

# Decrypt for restore
gpg --decrypt backup_20260606.sql.gpg > backup_20260606.sql
```

**In Transit:**
```bash
# Upload to S3 with encryption
aws s3 cp backup_20260606.sql.gz s3://peace-seafood-backups/ --sse AES256
```

### Access Control

- Backup directory permissions: `700` (owner only)
- Backup files permissions: `600` (owner read/write only)
- SSH key authentication for remote access
- MFA required for backup restoration in production

### Offsite Backup

**Recommended Services:**
- AWS S3 (with versioning enabled)
- Google Cloud Storage
- Backblaze B2
- rsync to remote server

**Upload Command:**
```bash
# Upload to S3
aws s3 sync /var/backups/peace_seafood/ s3://peace-seafood-backups/$(hostname)/ --exclude "*" --include "*.sql.gz"

# Or rsync to remote server
rsync -avz -e "ssh -p 22" /var/backups/peace_seafood/ backup@remote-server:/backups/peace_seafood/
```

---

## 📝 BACKUP CHECKLIST

### Daily (Automated)
- [ ] Database backup completed
- [ ] Backup file verified (exists, non-zero size)
- [ ] Email confirmation sent
- [ ] Log entry created

### Weekly (Automated)
- [ ] File system backup completed
- [ ] Old backups cleaned up (> 30 days)
- [ ] Upload to remote storage
- [ ] Verify remote backup integrity

### Monthly (Manual)
- [ ] Recovery drill performed
- [ ] Backup strategy reviewed
- [ ] Storage capacity checked
- [ ] Retention policy validated
- [ ] Update documentation if needed

### Quarterly (Manual)
- [ ] Disaster recovery plan tested
- [ ] Backup procedures updated
- [ ] Team training on recovery procedures
- [ ] Audit backup logs
- [ ] Review and update RTO/RPO targets

---

## 📞 EMERGENCY CONTACTS

**Primary:** SaaS Owner  
Email: admin@peaceseafood.com  
Phone: +62xxx

**Secondary:** Technical Lead  
Email: tech@peaceseafood.com  
Phone: +62xxx

**Database Administrator:**  
Email: dba@peaceseafood.com  
Phone: +62xxx

**Hosting Provider Support:**  
- AWS: 1-xxx-xxx-xxxx
- DigitalOcean: support.digitalocean.com
- [Your provider]

---

## 📚 RELATED DOCUMENTS

- `DEPLOYMENT_GUIDE_PHASE_1.md` - Deployment procedures
- `PHASE_1_FIXES_COMPLETION.md` - System architecture
- `PHASE_2_COMPLETE_SUMMARY.md` - Recent changes
- `.doc/05-database.md` - Database schema documentation

---

## 🔄 VERSION HISTORY

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0 | 2026-06-06 | Initial backup strategy document | Development Team |

---

## ⚠️ IMPORTANT NOTES

1. **Always test restores** - A backup is only good if it can be restored
2. **Monitor backup logs** - Set up alerts for failed backups
3. **Keep backup scripts updated** - Review and update as system evolves
4. **Document all changes** - Update this document when procedures change
5. **Train the team** - Ensure multiple people can perform restores
6. **Secure backups** - Backups contain sensitive data, treat them accordingly
7. **Test disaster recovery annually** - Full end-to-end test once per year

---

**Next Review Date:** September 6, 2026  
**Document Owner:** SaaS Owner / Technical Lead
