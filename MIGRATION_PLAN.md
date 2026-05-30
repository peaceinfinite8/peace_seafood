# 🔄 MIGRATION PLAN — Peace Seafood Database Synchronization

**Date**: May 30, 2026  
**Status**: Planning Phase  
**Priority**: Critical  

---

## 📋 EXECUTIVE SUMMARY

Rencana migrasi komprehensif untuk menyelaraskan database dengan kebutuhan web, sinkronisasi semua data flow yang saling terkait, dan memastikan tidak mengganggu data flow yang telah berjalan.

### **Tujuan Utama:**
1. **Database Alignment** — Sinkronisasi schema dengan kebutuhan web application
2. **Data Flow Integrity** — Memastikan semua relasi data tetap konsisten
3. **Zero Downtime** — Tidak mengganggu operasional yang sedang berjalan
4. **Data Consistency** — Validasi dan perbaikan data yang tidak konsisten

---

## 🔍 ANALISIS KEBUTUHAN

### **1. Schema Gap Analysis**

#### **Missing Tables (Perlu Ditambahkan)**
```sql
-- 1. Stok Opname Detail (untuk physical inventory)
CREATE TABLE stok_opname_detail (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_stok_opname INT NOT NULL,
    id_produk INT NOT NULL,
    qty_sistem DECIMAL(10,2) NOT NULL,
    qty_fisik DECIMAL(10,2) NOT NULL,
    selisih DECIMAL(10,2) GENERATED ALWAYS AS (qty_fisik - qty_sistem) STORED,
    keterangan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_stok_opname) REFERENCES stok_opname(id),
    FOREIGN KEY (id_produk) REFERENCES produk(id)
);

-- 2. Stok Transfer (untuk multi-warehouse)
CREATE TABLE stok_transfer (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_gudang_asal INT NOT NULL,
    id_gudang_tujuan INT NOT NULL,
    id_produk INT NOT NULL,
    qty DECIMAL(10,2) NOT NULL,
    status ENUM('pending','sent','received','cancelled') DEFAULT 'pending',
    catatan TEXT NULL,
    created_by INT NOT NULL,
    received_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    received_at TIMESTAMP NULL,
    FOREIGN KEY (id_gudang_asal) REFERENCES gudang(id),
    FOREIGN KEY (id_gudang_tujuan) REFERENCES gudang(id),
    FOREIGN KEY (id_produk) REFERENCES produk(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (received_by) REFERENCES users(id)
);

-- 3. Activity Log (untuk audit trail)
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    id_gudang INT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id),
    FOREIGN KEY (id_gudang) REFERENCES gudang(id),
    INDEX idx_user_action (id_user, action),
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_created_at (created_at)
);
```

#### **Missing Columns (Perlu Ditambahkan)**
```sql
-- Sudah ada dari migrasi sebelumnya, perlu validasi:
ALTER TABLE produk ADD COLUMN IF NOT EXISTS satuan VARCHAR(20) DEFAULT 'kg';
ALTER TABLE produk ADD COLUMN IF NOT EXISTS size VARCHAR(50) NULL;
ALTER TABLE produk ADD COLUMN IF NOT EXISTS grade VARCHAR(50) NULL;
ALTER TABLE produk ADD COLUMN IF NOT EXISTS asal VARCHAR(100) NULL;
ALTER TABLE produk ADD COLUMN IF NOT EXISTS gambar VARCHAR(255) NULL;

ALTER TABLE jenis_ikan ADD COLUMN IF NOT EXISTS allowed_sizes TEXT NULL;
ALTER TABLE jenis_ikan ADD COLUMN IF NOT EXISTS allowed_grades TEXT NULL;
ALTER TABLE jenis_ikan ADD COLUMN IF NOT EXISTS allowed_origins TEXT NULL;

ALTER TABLE users ADD COLUMN IF NOT EXISTS is_first_login TINYINT DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS registration_status ENUM('active','pending_signup') DEFAULT 'active';
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token_expires_at TIMESTAMP NULL;

ALTER TABLE gudang ADD COLUMN IF NOT EXISTS subscription_until DATE NULL;
ALTER TABLE gudang ADD COLUMN IF NOT EXISTS status_langganan ENUM('aktif','suspend') DEFAULT 'aktif';

ALTER TABLE nota ADD COLUMN IF NOT EXISTS bank_account_id INT NULL;
ALTER TABLE titipan ADD COLUMN IF NOT EXISTS id_produk INT NULL;
```

### **2. Data Flow Analysis**

#### **Current Data Flows (Sudah Berjalan)**
```
1. STOK MASUK FLOW:
   Input → Pending → Timbangan → Confirmed → Update Inventory

2. PENJUALAN FLOW:
   Draft → Items → Finalize → Update Stok → Create Hutang/Piutang

3. PENITIPAN FLOW:
   Masuk → Jual (Supplier/Gudang) → Settlement → Komisi

4. RETUR FLOW:
   Create → Approve/Reject → Update Stok/Hutang

5. KEUANGAN FLOW:
   Hutang/Piutang → Pembayaran → Update Status
```

#### **Missing Data Flows (Perlu Implementasi)**
```
6. STOK OPNAME FLOW:
   Create → Input Detail → Finalize → Adjust Stok

7. STOK TRANSFER FLOW:
   Create → Send → Receive → Update Both Gudang

8. ACTIVITY LOG FLOW:
   Any Action → Log → Audit Trail

9. BANK ACCOUNT FLOW:
   Create → Link to Nota → Payment Tracking
```

### **3. Data Consistency Issues**

#### **Identified Issues:**
1. **Orphaned Records** — Records yang tidak memiliki parent
2. **Invalid Foreign Keys** — FK yang menunjuk ke record yang tidak ada
3. **Calculation Inconsistencies** — Nilai stok tidak sesuai dengan transaksi
4. **Status Inconsistencies** — Status yang tidak valid atau bertentangan
5. **Missing Required Data** — Field yang seharusnya required tapi NULL

---

## 🎯 MIGRATION STRATEGY

### **Phase 1: Pre-Migration Validation (1-2 hours)**

#### **1.1 Data Backup**
```bash
# Full database backup
mysqldump -u root -p peace_seafood > backup_pre_migration_$(date +%Y%m%d_%H%M%S).sql

# Table-specific backups for critical tables
mysqldump -u root -p peace_seafood produk stok_masuk nota nota_detail > backup_critical_tables.sql
```

#### **1.2 Data Integrity Check**
```sql
-- Check orphaned records
SELECT 'produk' as table_name, COUNT(*) as orphaned_count
FROM produk p 
LEFT JOIN gudang g ON p.id_gudang = g.id 
WHERE g.id IS NULL

UNION ALL

SELECT 'stok_masuk', COUNT(*)
FROM stok_masuk sm 
LEFT JOIN produk p ON sm.id_produk = p.id 
WHERE p.id IS NULL

UNION ALL

SELECT 'nota_detail', COUNT(*)
FROM nota_detail nd 
LEFT JOIN nota n ON nd.id_nota = n.id 
WHERE n.id IS NULL;

-- Check calculation consistency
SELECT 
    p.id,
    p.nama,
    p.stok_qty as sistem_qty,
    p.nilai_stok as sistem_nilai,
    COALESCE(SUM(sm.qty_actual), 0) - COALESCE(SUM(nd.qty), 0) as calculated_qty,
    p.harga_beli * (COALESCE(SUM(sm.qty_actual), 0) - COALESCE(SUM(nd.qty), 0)) as calculated_nilai
FROM produk p
LEFT JOIN (
    SELECT sm.id_produk, t.qty_actual 
    FROM stok_masuk sm 
    JOIN timbangan t ON sm.id = t.id_stok_masuk 
    WHERE sm.status = 'confirmed'
) sm ON p.id = sm.id_produk
LEFT JOIN (
    SELECT nd.id_produk, nd.qty 
    FROM nota_detail nd 
    JOIN nota n ON nd.id_nota = n.id 
    WHERE n.status = 'final'
) nd ON p.id = nd.id_produk
GROUP BY p.id
HAVING ABS(p.stok_qty - calculated_qty) > 0.01;
```

#### **1.3 Schema Validation**
```sql
-- Check if all required tables exist
SELECT 
    CASE 
        WHEN COUNT(*) = 20 THEN 'OK' 
        ELSE CONCAT('MISSING: ', 20 - COUNT(*), ' tables') 
    END as table_status
FROM information_schema.tables 
WHERE table_schema = 'peace_seafood' 
AND table_name IN (
    'users', 'gudang', 'supplier', 'pembeli', 'jenis_ikan', 'produk',
    'harga_history', 'stok_masuk', 'timbangan', 'nota', 'nota_detail',
    'titipan', 'titipan_penjualan', 'retur', 'hutang_piutang', 
    'hutang_piutang_history', 'biaya_operasional', 'notifikasi', 
    'settings', 'stok_opname'
);

-- Check if all required columns exist
SELECT 
    table_name,
    column_name,
    is_nullable,
    data_type
FROM information_schema.columns 
WHERE table_schema = 'peace_seafood' 
AND table_name = 'produk'
AND column_name IN ('satuan', 'size', 'grade', 'asal', 'gambar')
ORDER BY table_name, ordinal_position;
```

### **Phase 2: Schema Migration (30-60 minutes)**

#### **2.1 Create Missing Tables**
```sql
-- Execute migration script
SOURCE database/migrations/20260530_add_missing_tables.sql;

-- Verify table creation
SHOW TABLES LIKE '%opname%';
SHOW TABLES LIKE '%transfer%';
SHOW TABLES LIKE '%activity%';
```

#### **2.2 Add Missing Columns**
```sql
-- Execute column additions (safe operations)
SOURCE database/migrations/20260530_add_missing_columns.sql;

-- Verify column additions
DESCRIBE produk;
DESCRIBE jenis_ikan;
DESCRIBE users;
DESCRIBE gudang;
DESCRIBE nota;
DESCRIBE titipan;
```

#### **2.3 Create Missing Indexes**
```sql
-- Performance indexes for new tables
CREATE INDEX idx_stok_opname_gudang_tanggal ON stok_opname_detail(id_stok_opname, id_produk);
CREATE INDEX idx_stok_transfer_status ON stok_transfer(status, created_at);
CREATE INDEX idx_activity_log_user_date ON activity_log(id_user, created_at);
CREATE INDEX idx_activity_log_table_record ON activity_log(table_name, record_id);

-- Verify indexes
SHOW INDEX FROM stok_opname_detail;
SHOW INDEX FROM stok_transfer;
SHOW INDEX FROM activity_log;
```

### **Phase 3: Data Migration & Cleanup (1-2 hours)**

#### **3.1 Fix Data Inconsistencies**
```sql
-- Fix orphaned records (move to default gudang or delete)
UPDATE produk p 
LEFT JOIN gudang g ON p.id_gudang = g.id 
SET p.id_gudang = (SELECT MIN(id) FROM gudang WHERE is_active = 1)
WHERE g.id IS NULL;

-- Fix calculation inconsistencies
UPDATE produk p 
SET p.stok_qty = (
    SELECT COALESCE(SUM(t.qty_actual), 0) - COALESCE(SUM(nd.qty), 0)
    FROM (
        SELECT sm.id_produk, t.qty_actual 
        FROM stok_masuk sm 
        JOIN timbangan t ON sm.id = t.id_stok_masuk 
        WHERE sm.status = 'confirmed' AND sm.id_produk = p.id
    ) t
    LEFT JOIN (
        SELECT nd.id_produk, nd.qty 
        FROM nota_detail nd 
        JOIN nota n ON nd.id_nota = n.id 
        WHERE n.status = 'final' AND nd.id_produk = p.id
    ) nd ON 1=1
)
WHERE ABS(p.stok_qty - (
    -- Same calculation as above
)) > 0.01;

-- Update nilai_stok based on corrected qty
UPDATE produk 
SET nilai_stok = stok_qty * harga_beli 
WHERE nilai_stok != stok_qty * harga_beli;
```

#### **3.2 Populate Default Values**
```sql
-- Set default satuan for existing products
UPDATE produk SET satuan = 'kg' WHERE satuan IS NULL OR satuan = '';

-- Set default subscription for existing gudang
UPDATE gudang SET status_langganan = 'aktif' WHERE status_langganan IS NULL;

-- Set default registration status for existing users
UPDATE users SET registration_status = 'active' WHERE registration_status IS NULL;
```

#### **3.3 Create Initial Settings**
```sql
-- Insert default settings for each gudang
INSERT INTO settings (id_gudang, kunci, nilai, deskripsi)
SELECT 
    g.id,
    'stok_minimum_threshold',
    '10',
    'Minimum stock threshold for alerts'
FROM gudang g
WHERE g.is_active = 1
AND NOT EXISTS (
    SELECT 1 FROM settings s 
    WHERE s.id_gudang = g.id 
    AND s.kunci = 'stok_minimum_threshold'
);

-- Insert other default settings
INSERT INTO settings (id_gudang, kunci, nilai, deskripsi)
SELECT 
    g.id,
    setting_key,
    default_value,
    description
FROM gudang g
CROSS JOIN (
    SELECT 'komisi_penitipan_tipe' as setting_key, 'potong' as default_value, 'Commission payment type' as description
    UNION ALL
    SELECT 'pajak_default_persen', '10', 'Default tax percentage'
    UNION ALL
    SELECT 'jatuh_tempo_default_days', '30', 'Default payment due days'
    UNION ALL
    SELECT 'notifikasi_stok_minimum', '1', 'Enable minimum stock notifications'
) defaults
WHERE g.is_active = 1
AND NOT EXISTS (
    SELECT 1 FROM settings s 
    WHERE s.id_gudang = g.id 
    AND s.kunci = defaults.setting_key
);
```

### **Phase 4: Application Layer Sync (30-60 minutes)**

#### **4.1 Update Controllers**
```php
// Ensure all controllers use the new schema
// Check StokOpnameController exists and works
// Check StokTransferController exists and works
// Check ActivityLogController exists and works
```

#### **4.2 Update Services**
```php
// Ensure all services handle new fields
// Update StokService for new columns
// Update PenjualanService for bank_account_id
// Update PenitipanService for id_produk
```

#### **4.3 Update Models**
```php
// Ensure all models reflect new schema
// Update Produk model for new attributes
// Update User model for new fields
// Update Gudang model for subscription fields
```

### **Phase 5: Validation & Testing (1-2 hours)**

#### **5.1 Data Integrity Validation**
```sql
-- Re-run integrity checks
-- Verify all foreign keys are valid
-- Verify all calculations are correct
-- Verify all required fields are populated
```

#### **5.2 Application Testing**
```bash
# Test all major flows
# 1. Stok masuk flow
# 2. Penjualan flow  
# 3. Penitipan flow
# 4. Retur flow
# 5. Keuangan flow
# 6. New: Stok opname flow
# 7. New: Stok transfer flow
```

#### **5.3 Performance Testing**
```sql
-- Test query performance on large datasets
EXPLAIN SELECT * FROM produk WHERE stok_qty < 10;
EXPLAIN SELECT * FROM activity_log WHERE id_user = 1 ORDER BY created_at DESC LIMIT 50;
EXPLAIN SELECT * FROM stok_transfer WHERE status = 'pending';
```

---

## 🔒 SAFETY MEASURES

### **1. Rollback Plan**

#### **Database Rollback**
```bash
# If migration fails, restore from backup
mysql -u root -p peace_seafood < backup_pre_migration_YYYYMMDD_HHMMSS.sql
```

#### **Application Rollback**
```bash
# Revert to previous commit if needed
git checkout HEAD~1
```

### **2. Monitoring During Migration**

#### **Real-time Monitoring**
```sql
-- Monitor active connections
SHOW PROCESSLIST;

-- Monitor table locks
SHOW OPEN TABLES WHERE In_use > 0;

-- Monitor migration progress
SELECT 
    table_name,
    table_rows,
    data_length,
    index_length
FROM information_schema.tables 
WHERE table_schema = 'peace_seafood'
ORDER BY data_length DESC;
```

### **3. Validation Checkpoints**

#### **After Each Phase**
```sql
-- Checkpoint 1: Schema validation
SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'peace_seafood';

-- Checkpoint 2: Data validation  
SELECT COUNT(*) FROM produk WHERE stok_qty < 0;

-- Checkpoint 3: Relationship validation
SELECT COUNT(*) FROM nota_detail nd LEFT JOIN nota n ON nd.id_nota = n.id WHERE n.id IS NULL;

-- Checkpoint 4: Performance validation
SELECT AVG(query_time) FROM mysql.slow_log WHERE start_time > NOW() - INTERVAL 1 HOUR;
```

---

## 📋 MIGRATION CHECKLIST

### **Pre-Migration (Required)**
- [ ] **Database Backup** — Full backup created and verified
- [ ] **Application Backup** — Code backup and git commit
- [ ] **Maintenance Mode** — Application in maintenance mode (optional)
- [ ] **User Notification** — Users notified of maintenance window
- [ ] **Resource Check** — Sufficient disk space and memory
- [ ] **Permission Check** — Database user has all required permissions

### **Schema Migration**
- [ ] **Missing Tables** — stok_opname_detail, stok_transfer, activity_log created
- [ ] **Missing Columns** — All new columns added successfully
- [ ] **Indexes Created** — Performance indexes added
- [ ] **Foreign Keys** — All FK constraints validated
- [ ] **Data Types** — All data types correct and consistent

### **Data Migration**
- [ ] **Orphaned Records** — Fixed or removed
- [ ] **Calculation Fix** — Stok quantities and values corrected
- [ ] **Default Values** — All required fields populated
- [ ] **Settings Created** — Default settings for all gudang
- [ ] **Data Validation** — All data passes integrity checks

### **Application Sync**
- [ ] **Controllers Updated** — All controllers handle new schema
- [ ] **Services Updated** — All services use new fields
- [ ] **Models Updated** — All models reflect new schema
- [ ] **Routes Updated** — All routes work with new endpoints
- [ ] **Frontend Updated** — UI handles new fields and features

### **Testing & Validation**
- [ ] **Unit Tests** — All existing tests pass
- [ ] **Integration Tests** — All data flows work correctly
- [ ] **Performance Tests** — No significant performance degradation
- [ ] **User Acceptance** — Key users validate functionality
- [ ] **Error Monitoring** — No critical errors in logs

### **Post-Migration**
- [ ] **Maintenance Mode Off** — Application back online
- [ ] **User Notification** — Users notified migration complete
- [ ] **Monitoring Active** — Error and performance monitoring enabled
- [ ] **Documentation Updated** — All docs reflect new schema
- [ ] **Backup Schedule** — Regular backup schedule resumed

---

## ⚠️ RISK ASSESSMENT

### **High Risk Items**
1. **Data Loss** — Risk: Medium, Mitigation: Full backup + validation
2. **Downtime** — Risk: Low, Mitigation: Quick rollback plan
3. **Performance Impact** — Risk: Low, Mitigation: Index optimization
4. **Data Corruption** — Risk: Low, Mitigation: Transaction-based migration

### **Medium Risk Items**
1. **Foreign Key Violations** — Risk: Medium, Mitigation: Pre-validation
2. **Application Errors** — Risk: Medium, Mitigation: Thorough testing
3. **User Confusion** — Risk: Low, Mitigation: Clear communication

### **Mitigation Strategies**
1. **Comprehensive Backup** — Multiple backup points
2. **Staged Migration** — Phase-by-phase with validation
3. **Quick Rollback** — Automated rollback procedures
4. **Real-time Monitoring** — Continuous monitoring during migration
5. **User Communication** — Clear timeline and expectations

---

## 📅 TIMELINE ESTIMATE

### **Total Time: 4-6 hours**

| Phase | Duration | Description |
|-------|----------|-------------|
| **Phase 1** | 1-2 hours | Pre-migration validation and backup |
| **Phase 2** | 30-60 min | Schema migration (tables, columns, indexes) |
| **Phase 3** | 1-2 hours | Data migration and cleanup |
| **Phase 4** | 30-60 min | Application layer synchronization |
| **Phase 5** | 1-2 hours | Validation and testing |

### **Recommended Schedule**
- **Best Time**: Weekend or off-peak hours
- **Maintenance Window**: 6 hours (with buffer)
- **Team Required**: 1-2 developers + 1 DBA (optional)
- **Rollback Time**: 30 minutes if needed

---

## 🎯 SUCCESS CRITERIA

### **Technical Success**
- [ ] All tables and columns exist as specified
- [ ] All data integrity checks pass
- [ ] All application features work correctly
- [ ] Performance is maintained or improved
- [ ] No data loss or corruption

### **Business Success**
- [ ] All existing workflows continue to work
- [ ] New features are available and functional
- [ ] Users can perform all required operations
- [ ] Reports and calculations are accurate
- [ ] System is stable and reliable

### **Quality Success**
- [ ] Code quality is maintained or improved
- [ ] Documentation is complete and accurate
- [ ] Test coverage is adequate
- [ ] Error handling is robust
- [ ] Monitoring and logging are effective

---

## 📞 SUPPORT & ESCALATION

### **During Migration**
- **Primary Contact**: Lead Developer
- **Database Issues**: DBA or Senior Developer  
- **Application Issues**: Backend Developer
- **Emergency Rollback**: Any team member with database access

### **Post-Migration**
- **Bug Reports**: Standard issue tracking system
- **Performance Issues**: Monitor for 48 hours post-migration
- **User Training**: Provide documentation for new features
- **Ongoing Support**: Regular development team

---

## 📚 DOCUMENTATION REFERENCES

### **Technical Documentation**
- `database/schema.sql` — Current database schema
- `.docs/PRD/05-database-schema.md` — Schema documentation
- `.docs/PRD/06-module-flows.md` — Business flow documentation
- `.docs/PRD/09-business-rules.md` — Business rules
- `.docs/PRD/12-api-endpoints.md` — API documentation

### **Migration Files**
- `database/migrations/20260530_add_missing_tables.sql` — New tables
- `database/migrations/20260530_add_missing_columns.sql` — New columns
- `database/migrations/20260530_data_cleanup.sql` — Data fixes
- `database/migrations/20260530_default_settings.sql` — Default data

### **Testing Documentation**
- `.docs/changes/calculations-testing-guide.md` — Testing scenarios
- `.docs/guides/tech-stack-notes.md` — Technical notes
- `.docs/BACKEND_STATUS.md` — Current backend status

---

## ✅ FINAL CHECKLIST

### **Before Starting Migration**
- [ ] All team members notified
- [ ] Backup verified and tested
- [ ] Migration scripts reviewed and tested
- [ ] Rollback plan documented and tested
- [ ] Monitoring tools ready

### **During Migration**
- [ ] Follow phases in order
- [ ] Validate each checkpoint
- [ ] Monitor system performance
- [ ] Document any issues or deviations
- [ ] Communicate progress to stakeholders

### **After Migration**
- [ ] All validation tests pass
- [ ] Application fully functional
- [ ] Users notified of completion
- [ ] Documentation updated
- [ ] Post-migration monitoring active

---

**Migration Plan Created**: May 30, 2026  
**Estimated Completion**: 4-6 hours  
**Risk Level**: Low-Medium  
**Confidence Level**: High  

**Status**: ✅ **READY FOR EXECUTION**

---

*This migration plan ensures zero data loss, minimal downtime, and complete synchronization between database schema and web application requirements.*