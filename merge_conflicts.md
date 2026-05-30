# ⚠️ MERGE CONFLICTS — Peace Seafood Conflict Analysis

**Date**: May 30, 2026  
**Status**: Pre-Merge Conflict Scan Complete  
**Priority**: Critical for Clean Merge  

---

## 📋 EXECUTIVE SUMMARY

Comprehensive analysis of potential merge conflicts, duplicate files, naming inconsistencies, and structural issues that could cause problems during code merging, deployment, or version control operations.

### **Conflict Categories:**
1. **🔴 Critical Conflicts** — Will break merge/deployment
2. **🟠 High Risk** — Likely to cause issues
3. **🟡 Medium Risk** — May cause confusion
4. **🔵 Low Risk** — Minor inconsistencies
5. **⚪ Informational** — No immediate action needed

---

## 🔴 CRITICAL CONFLICTS

### **CC1. Duplicate View Files (index.php vs index.view.php)**
**Status**: 🔴 Critical  
**Impact**: Route confusion, file serving errors  
**Risk**: High - Multiple files with same purpose  

**Duplicate Files Identified:**
```
src/views/keuangan/
  ├── index.php (OLD)
  └── index.view.php (NEW) ✓

src/views/penitipan/
  ├── index.php (OLD)
  └── index.view.php (NEW) ✓

src/views/penjualan/
  ├── index.php (OLD)
  └── index.view.php (NEW) ✓

src/views/retur/
  ├── index.php (OLD)
  └── index.view.php (NEW) ✓

src/views/stok/
  ├── index.php (OLD)
  └── index.view.php (NEW) ✓
```

**Issue**: Both old and new naming conventions exist simultaneously

**Solution**:
```bash
# Remove old index.php files after verifying .view.php versions work
rm src/views/keuangan/index.php
rm src/views/penitipan/index.php
rm src/views/penjualan/index.php
rm src/views/retur/index.php
rm src/views/stok/index.php
```

**Verification Required**:
- [ ] Confirm routes point to .view.php versions
- [ ] Test all affected pages load correctly
- [ ] Check for any hardcoded references to old filenames

---

### **CC2. Hardcoded Base Path (/peace_seafood/)**
**Status**: 🔴 Critical  
**Impact**: Breaks in different environments  
**Risk**: High - Deployment failures  

**Affected Files** (50+ occurrences):
```javascript
// src/views/stok/transfer.php
axios.get('/peace_seafood/api/settings/gudang', ...)
axios.get('/peace_seafood/api/master/produk?id_gudang=', ...)
axios.post('/peace_seafood/api/stok-transfer', ...)

// src/views/stok/timbangan.view.php
window.location.href = '/peace_seafood/dashboard';
axios.get('/peace_seafood/api/stok/pending-timbang', ...)

// src/views/stok/opname.php
axios.get('/peace_seafood/api/settings/gudang', ...)
axios.post('/peace_seafood/api/stok-opname', ...)

// src/views/stok/masuk.php
href="/peace_seafood/stok"
window.location.href = '/peace_seafood/dashboard';
axios.get('/peace_seafood/api/master/supplier', ...)

// And 40+ more files...
```

**Solution**:
```javascript
// Create global base URL variable in layout
// src/views/layouts/app.php
<script>
    window.APP_BASE_URL = '<?= $baseUrl ?>';
    window.API_BASE_URL = '<?= $baseUrl ?>/api';
</script>

// Then use in all files:
axios.get(`${window.API_BASE_URL}/settings/gudang`, ...)
window.location.href = `${window.APP_BASE_URL}/dashboard`;
```

**Files Requiring Update**: 50+ view files

---

### **CC3. Environment Configuration Conflicts**
**Status**: 🔴 Critical  
**Impact**: Different behavior in dev/staging/production  
**Risk**: High - Data corruption, security issues  

**Issues**:
```php
// Inconsistent environment variable usage
$_ENV['APP_DEBUG'] ?? 'false'  // String comparison
$_ENV['APP_DEBUG'] === 'true'  // Boolean comparison

// Database connection scattered across files
config/database.php
database/run_setup.php
database/seed_profitable.php
database/reset_tanpa_hapus_users.php
database/clean_data.php
```

**Solution**:
1. Centralize environment configuration
2. Create single database connection utility
3. Standardize boolean environment variables
4. Add environment validation on startup

---

### **CC4. Duplicate Configuration Files**
**Status**: 🔴 Critical  
**Impact**: Configuration confusion, wrong settings loaded  
**Risk**: High - Application malfunction  

**Duplicates Identified**:
```
.htaccess (2 files)
├── .htaccess (root - redirects to public/)
└── public/.htaccess (actual Apache config)
Purpose: Different - KEEP BOTH

manifest.json (2 files)
├── manifest.json (root - PWA manifest)
└── public/build/manifest.json (build manifest)
Purpose: Different - KEEP BOTH

README.md (3 files)
├── README.md (root - project readme)
├── .docs/README.md (documentation index)
└── resources/ui/README.md (UI resources readme)
Purpose: Different - KEEP ALL

app.php (2 files)
├── config/app.php (application config)
└── src/views/layouts/app.php (layout template)
Purpose: Different - KEEP BOTH

database.php (2 files)
├── config/database.php (connection config)
└── src/utils/Database.php (database utility class)
Purpose: Different - KEEP BOTH
```

**Action**: No deletion needed - all serve different purposes

---

## 🟠 HIGH RISK CONFLICTS

### **HR1. Inconsistent File Naming Convention**
**Status**: 🟠 High Risk  
**Impact**: Developer confusion, maintenance issues  
**Risk**: Medium - Inconsistent codebase  

**Patterns Found**:
```
View Files:
✓ index.view.php (NEW standard)
✗ index.php (OLD - being phased out)
✓ timbangan.view.php (NEW)
✗ masuk.php (OLD - no .view suffix)
✗ history.php (OLD - no .view suffix)
✗ opname.php (OLD - no .view suffix)
✗ transfer.php (OLD - no .view suffix)

Master Data:
✓ pembeli.view.php (NEW)
✓ produk.view.php (NEW)
✓ supplier.view.php (NEW)
✗ jenis-ikan.php (OLD - no .view suffix)
✗ migrasi.php (OLD - no .view suffix)
```

**Solution**:
```bash
# Rename remaining view files to .view.php convention
mv src/views/stok/masuk.php src/views/stok/masuk.view.php
mv src/views/stok/history.php src/views/stok/history.view.php
mv src/views/stok/opname.php src/views/stok/opname.view.php
mv src/views/stok/transfer.php src/views/stok/transfer.view.php
mv src/views/master-data/jenis-ikan.php src/views/master-data/jenis-ikan.view.php
mv src/views/master-data/migrasi.php src/views/master-data/migrasi.view.php
mv src/views/penitipan/create.php src/views/penitipan/create.view.php
mv src/views/penjualan/create.php src/views/penjualan/create.view.php
mv src/views/retur/create.php src/views/retur/create.view.php
mv src/views/checker/draft-penjualan.php src/views/checker/draft-penjualan.view.php
mv src/views/pages/dashboard.php src/views/pages/dashboard.view.php
```

**Update Routes**: After renaming, update `routes/web.php`

---

### **HR2. Database Connection Duplication**
**Status**: 🟠 High Risk  
**Impact**: Maintenance burden, inconsistent behavior  
**Risk**: Medium - Connection issues  

**Duplicate Connection Logic**:
```php
// config/database.php (main connection)
$pdo = new PDO($dsn, $user, $password, [...]);

// database/run_setup.php (duplicate)
$pdo = new PDO($dsn, $user, $password, [...]);

// database/seed_profitable.php (duplicate)
$pdo = new PDO($dsn, $user, $password, [...]);

// database/reset_tanpa_hapus_users.php (duplicate)
$pdo = new PDO($dsn, $user, $password, [...]);

// database/clean_data.php (duplicate)
$pdo = new PDO($dsn, $user, $password, [...]);
```

**Solution**:
```php
// Create shared connection utility for CLI scripts
// database/includes/connection.php
<?php
require_once __DIR__ . '/../../config/database.php';
return $pdo;

// Then in CLI scripts:
$pdo = require __DIR__ . '/includes/connection.php';
```

---

### **HR3. Missing .gitignore Entries**
**Status**: 🟠 High Risk  
**Impact**: Sensitive data in version control  
**Risk**: Medium - Security exposure  

**Potentially Missing Entries**:
```gitignore
# Environment files
.env
.env.local
.env.*.local

# Storage directories
storage/cache/*
!storage/cache/.gitkeep
storage/logs/*
!storage/logs/.gitkeep
storage/uploads/*
!storage/uploads/.gitkeep
storage/exports/*
!storage/exports/.gitkeep

# Build artifacts
public/build/*
!public/build/.gitkeep

# IDE files
.vscode/
.idea/
*.swp
*.swo
*~

# OS files
.DS_Store
Thumbs.db

# Composer
vendor/
composer.phar

# Node
node_modules/
npm-debug.log
yarn-error.log

# Database backups
*.sql
!database/schema.sql
!database/migrations/*.sql
!database/seeders/*.sql
```

**Action**: Verify and update .gitignore

---

## 🟡 MEDIUM RISK CONFLICTS

### **MR1. Inconsistent Error Handling**
**Status**: 🟡 Medium Risk  
**Impact**: Debugging difficulties  
**Risk**: Low - Functional but inconsistent  

**Patterns Found**:
```php
// Pattern 1: Generic error
Response::error('Data tidak lengkap', 422);

// Pattern 2: With error details
Response::error('Data tidak lengkap', 422, ['required' => [...]]);

// Pattern 3: With exception message
Response::error($e->getMessage(), 500);

// Pattern 4: Conditional debug info
'error' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Internal Server Error'
```

**Solution**: Standardize error response format across all controllers

---

### **MR2. Mixed Date/Time Handling**
**Status**: 🟡 Medium Risk  
**Impact**: Timezone confusion  
**Risk**: Low - Mostly consistent  

**Issues**:
```php
// Database timezone set to +07:00 (WIB)
PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+07:00'"

// Application timezone
'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Jakarta'

// But some queries use CURDATE(), NOW() without timezone awareness
```

**Solution**: Ensure all date/time operations are timezone-aware

---

### **MR3. Inconsistent API Response Format**
**Status**: 🟡 Medium Risk  
**Impact**: Frontend parsing issues  
**Risk**: Low - Mostly standardized  

**Variations Found**:
```json
// Pattern 1: Standard success
{"success": true, "data": [...]}

// Pattern 2: With message
{"success": true, "message": "...", "data": [...]}

// Pattern 3: With pagination
{"success": true, "data": [...], "pagination": {...}}

// Pattern 4: Error format
{"success": false, "message": "...", "errors": {...}}
```

**Solution**: Document and enforce standard response format

---

## 🔵 LOW RISK CONFLICTS

### **LR1. Empty .gitkeep Files**
**Status**: 🔵 Low Risk  
**Impact**: None - intentional  
**Risk**: None  

**Files**:
```
public/assets/images/.gitkeep
storage/cache/.gitkeep
storage/exports/excel/.gitkeep
storage/exports/pdf/.gitkeep
storage/logs/.gitkeep
storage/uploads/.gitkeep
```

**Action**: Keep as-is - these preserve empty directories in git

---

### **LR2. Multiple index.php Files**
**Status**: 🔵 Low Risk  
**Impact**: None - different purposes  
**Risk**: None  

**Files** (10 total):
```
public/index.php (application entry point)
src/views/*/index.php (view files - being renamed)
```

**Action**: No conflict - different purposes

---

### **LR3. Multiple create.php Files**
**Status**: 🔵 Low Risk  
**Impact**: None - different modules  
**Risk**: None  

**Files**:
```
src/views/penitipan/create.php
src/views/penjualan/create.php
src/views/retur/create.php
```

**Action**: Rename to .view.php for consistency

---

## ⚪ INFORMATIONAL

### **I1. Dependency Versions**
**Status**: ⚪ Informational  
**Impact**: None currently  
**Risk**: None  

**Current Versions**:
```json
// package.json
"esbuild": "^0.28.0"

// composer.json
"firebase/php-jwt": "^6.11",
"dompdf/dompdf": "^2.0",
"phpoffice/phpspreadsheet": "^1.29"
```

**Action**: Monitor for security updates

---

### **I2. Build Artifacts**
**Status**: ⚪ Informational  
**Impact**: None  
**Risk**: None  

**Files**:
```
public/build/manifest.json (generated)
node_modules/ (dependencies)
vendor/ (dependencies)
```

**Action**: Ensure in .gitignore

---

## 🛠️ CONFLICT RESOLUTION STRATEGY

### **Phase 1: Critical Conflicts (Day 1)**
**Priority**: Must fix before merge

1. **Remove Duplicate View Files**
   ```bash
   # Backup first
   mkdir -p .backup/views
   cp src/views/*/index.php .backup/views/
   
   # Remove old files
   rm src/views/keuangan/index.php
   rm src/views/penitipan/index.php
   rm src/views/penjualan/index.php
   rm src/views/retur/index.php
   rm src/views/stok/index.php
   ```

2. **Fix Hardcoded Paths**
   ```javascript
   // Add to src/views/layouts/app.php
   <script>
   window.APP_BASE_URL = '<?= $baseUrl ?>';
   window.API_BASE_URL = '<?= $baseUrl ?>/api';
   </script>
   
   // Then find and replace in all view files:
   // '/peace_seafood/' → '${window.APP_BASE_URL}/'
   // '/peace_seafood/api/' → '${window.API_BASE_URL}/'
   ```

3. **Centralize Database Connection**
   ```php
   // Create database/includes/connection.php
   // Update all CLI scripts to use it
   ```

4. **Standardize Environment Variables**
   ```php
   // Create config/env.php for environment helpers
   // Standardize boolean checks
   ```

---

### **Phase 2: High Risk Conflicts (Day 2)**
**Priority**: Should fix before merge

1. **Rename Remaining View Files**
   ```bash
   # Use git mv to preserve history
   git mv src/views/stok/masuk.php src/views/stok/masuk.view.php
   git mv src/views/stok/history.php src/views/stok/history.view.php
   # ... etc
   ```

2. **Update Routes**
   ```php
   // Update routes/web.php with new filenames
   ```

3. **Update .gitignore**
   ```bash
   # Add missing entries
   # Verify no sensitive files tracked
   ```

---

### **Phase 3: Medium Risk Conflicts (Day 3)**
**Priority**: Fix for consistency

1. **Standardize Error Handling**
2. **Document API Response Format**
3. **Audit Timezone Handling**

---

### **Phase 4: Low Risk Conflicts (Day 4)**
**Priority**: Polish and cleanup

1. **Rename create.php files**
2. **Code style consistency**
3. **Documentation updates**

---

## 📊 CONFLICT STATISTICS

### **By Priority:**
- 🔴 Critical: 4 conflicts
- 🟠 High Risk: 3 conflicts
- 🟡 Medium Risk: 3 conflicts
- 🔵 Low Risk: 3 conflicts
- ⚪ Informational: 2 items
- **Total: 15 items**

### **By Type:**
- File Duplicates: 5 issues
- Hardcoded Paths: 1 issue (50+ occurrences)
- Configuration: 3 issues
- Naming Convention: 2 issues
- Code Quality: 4 issues

### **By Effort:**
- High Effort (>1 day): 2 conflicts
- Medium Effort (4-8 hours): 5 conflicts
- Low Effort (<4 hours): 8 conflicts

---

## 🎯 IMMEDIATE ACTION ITEMS

### **Before Merge (Critical):**
- [ ] Remove duplicate index.php files (5 files)
- [ ] Fix hardcoded /peace_seafood/ paths (50+ occurrences)
- [ ] Centralize database connection logic
- [ ] Standardize environment variable usage
- [ ] Verify .gitignore completeness

### **During Merge (High Priority):**
- [ ] Rename remaining view files to .view.php
- [ ] Update all route references
- [ ] Test all renamed files load correctly
- [ ] Verify no broken links

### **After Merge (Medium Priority):**
- [ ] Standardize error handling
- [ ] Document API response format
- [ ] Audit timezone handling
- [ ] Code style consistency pass

---

## 🔍 VERIFICATION CHECKLIST

### **Pre-Merge Verification:**
- [ ] All duplicate files identified and resolved
- [ ] All hardcoded paths replaced with variables
- [ ] All routes point to correct files
- [ ] Database connection centralized
- [ ] Environment configuration standardized
- [ ] .gitignore updated and verified

### **Post-Merge Verification:**
- [ ] Application loads without errors
- [ ] All pages accessible
- [ ] API endpoints functional
- [ ] Database connections work
- [ ] No 404 errors in browser console
- [ ] No broken links in navigation

---

## 📞 CONFLICT RESOLUTION SUPPORT

### **For File Conflicts:**
- **Duplicate Files**: Remove old versions after verification
- **Naming Conflicts**: Follow .view.php convention
- **Path Conflicts**: Use environment-aware base URLs

### **For Configuration Conflicts:**
- **Environment Variables**: Centralize in config/
- **Database Connections**: Use shared utility
- **API Endpoints**: Standardize response format

### **For Code Conflicts:**
- **Error Handling**: Follow standard pattern
- **Date/Time**: Use timezone-aware functions
- **Validation**: Consistent across frontend/backend

---

## 📚 RELATED DOCUMENTATION

### **Conflict Resolution:**
- `MERGE_REPAIR.md` - General issues and repairs
- `MIGRATION_PLAN.md` - Database migration plan
- `.docs/BACKEND_STATUS.md` - Backend optimization status

### **Implementation:**
- `.docs/implementation_Plan/` - Specific bug fixes
- `.docs/FRONTEND_STATUS.md` - Frontend status
- `routes/web.php` - Route definitions

---

## ✅ RESOLUTION CHECKLIST

### **Critical Conflicts (Must Fix):**
- [ ] **CC1** - Remove duplicate view files
- [ ] **CC2** - Fix hardcoded base paths
- [ ] **CC3** - Standardize environment config
- [ ] **CC4** - Verify duplicate configs are intentional

### **High Risk Conflicts (Should Fix):**
- [ ] **HR1** - Rename files to .view.php convention
- [ ] **HR2** - Centralize database connections
- [ ] **HR3** - Update .gitignore

### **Medium Risk Conflicts (Nice to Fix):**
- [ ] **MR1** - Standardize error handling
- [ ] **MR2** - Audit timezone handling
- [ ] **MR3** - Document API response format

---

**Conflict Scan Completed**: May 30, 2026  
**Total Conflicts**: 15 items  
**Critical Issues**: 4  
**Estimated Resolution Time**: 2-4 days  
**Merge Readiness**: After critical conflicts resolved  

**Status**: ⚠️ **CONFLICTS IDENTIFIED - RESOLUTION REQUIRED**

---

*This document provides a comprehensive analysis of all potential merge conflicts. Resolve critical conflicts before attempting any merge operations.*