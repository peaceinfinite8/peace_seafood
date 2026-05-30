# ✅ TODO LIST - Perbaikan Merge Conflicts

**Date**: May 30, 2026  
**Priority**: Critical untuk Clean Merge  
**Estimated Time**: 2-4 hari  

---

## 📋 OVERVIEW

Daftar lengkap tugas untuk menyelesaikan semua merge conflicts yang teridentifikasi di `merge_conflicts.md`.

**Total Tasks**: 47 tasks  
**Critical**: 15 tasks  
**High Priority**: 12 tasks  
**Medium Priority**: 10 tasks  
**Low Priority**: 10 tasks  

---

## 🔴 PHASE 1: CRITICAL CONFLICTS (Day 1)

**Priority**: MUST FIX BEFORE MERGE  
**Estimated Time**: 6-8 hours  

### **CC1: Remove Duplicate View Files**

- [ ] **Task 1.1**: Backup duplicate files
  ```bash
  mkdir -p .backup/views/$(date +%Y%m%d)
  cp src/views/keuangan/index.php .backup/views/$(date +%Y%m%d)/
  cp src/views/penitipan/index.php .backup/views/$(date +%Y%m%d)/
  cp src/views/penjualan/index.php .backup/views/$(date +%Y%m%d)/
  cp src/views/retur/index.php .backup/views/$(date +%Y%m%d)/
  cp src/views/stok/index.php .backup/views/$(date +%Y%m%d)/
  ```

- [ ] **Task 1.2**: Verify .view.php versions work correctly
  - [ ] Test keuangan/index.view.php loads
  - [ ] Test penitipan/index.view.php loads
  - [ ] Test penjualan/index.view.php loads
  - [ ] Test retur/index.view.php loads
  - [ ] Test stok/index.view.php loads

- [ ] **Task 1.3**: Remove old index.php files
  ```bash
  rm src/views/keuangan/index.php
  rm src/views/penitipan/index.php
  rm src/views/penjualan/index.php
  rm src/views/retur/index.php
  rm src/views/stok/index.php
  ```

- [ ] **Task 1.4**: Verify routes still work
  - [ ] Test /keuangan route
  - [ ] Test /penitipan route
  - [ ] Test /penjualan route
  - [ ] Test /retur route
  - [ ] Test /stok route

- [ ] **Task 1.5**: Commit changes
  ```bash
  git add src/views/
  git commit -m "Remove duplicate index.php files, keep .view.php versions"
  ```

---

### **CC2: Fix Hardcoded Base Path (/peace_seafood/)**

- [ ] **Task 2.1**: Add global base URL to layout
  ```javascript
  // Add to src/views/layouts/app.php before closing </head>
  <script>
      window.APP_BASE_URL = '<?= $baseUrl ?>';
      window.API_BASE_URL = '<?= $baseUrl ?>/api';
  </script>
  ```

- [ ] **Task 2.2**: Create find-replace script
  ```bash
  # Create script: scripts/fix-hardcoded-paths.sh
  ```

- [ ] **Task 2.3**: Fix hardcoded paths in stok module (10 files)
  - [ ] src/views/stok/transfer.php
  - [ ] src/views/stok/timbangan.view.php
  - [ ] src/views/stok/opname.php
  - [ ] src/views/stok/masuk.php
  - [ ] src/views/stok/index.view.php
  - [ ] src/views/stok/index.php
  - [ ] src/views/stok/history.php
  - [ ] Replace: `/peace_seafood/` → `${window.APP_BASE_URL}/`
  - [ ] Replace: `/peace_seafood/api/` → `${window.API_BASE_URL}/`
  - [ ] Test each file after replacement

- [ ] **Task 2.4**: Fix hardcoded paths in penjualan module (3 files)
  - [ ] src/views/penjualan/index.view.php
  - [ ] src/views/penjualan/index.php
  - [ ] src/views/penjualan/create.php
  - [ ] Replace hardcoded paths
  - [ ] Test functionality

- [ ] **Task 2.5**: Fix hardcoded paths in penitipan module (3 files)
  - [ ] src/views/penitipan/index.view.php
  - [ ] src/views/penitipan/index.php
  - [ ] src/views/penitipan/create.php
  - [ ] Replace hardcoded paths
  - [ ] Test functionality

- [ ] **Task 2.6**: Fix hardcoded paths in retur module (3 files)
  - [ ] src/views/retur/index.view.php
  - [ ] src/views/retur/index.php
  - [ ] src/views/retur/create.php
  - [ ] Replace hardcoded paths
  - [ ] Test functionality

- [ ] **Task 2.7**: Fix hardcoded paths in keuangan module (2 files)
  - [ ] src/views/keuangan/index.view.php
  - [ ] src/views/keuangan/index.php
  - [ ] Replace hardcoded paths
  - [ ] Test functionality

- [ ] **Task 2.8**: Fix hardcoded paths in master-data module (5 files)
  - [ ] src/views/master-data/index.view.php
  - [ ] src/views/master-data/produk.view.php
  - [ ] src/views/master-data/supplier.view.php
  - [ ] src/views/master-data/pembeli.view.php
  - [ ] src/views/master-data/jenis-ikan.php
  - [ ] Replace hardcoded paths
  - [ ] Test functionality

- [ ] **Task 2.9**: Fix hardcoded paths in other modules (10+ files)
  - [ ] src/views/settings/index.view.php
  - [ ] src/views/laporan/index.view.php
  - [ ] src/views/activity-log/index.view.php
  - [ ] src/views/pages/dashboard.php
  - [ ] src/views/pages/login.php
  - [ ] src/views/checker/draft-penjualan.php
  - [ ] Replace hardcoded paths
  - [ ] Test functionality

- [ ] **Task 2.10**: Verify all AJAX calls work
  - [ ] Test API calls from each module
  - [ ] Check browser console for errors
  - [ ] Verify redirects work correctly

- [ ] **Task 2.11**: Commit changes
  ```bash
  git add src/views/
  git commit -m "Replace hardcoded /peace_seafood/ paths with dynamic base URL"
  ```

---

### **CC3: Centralize Database Connection**

- [ ] **Task 3.1**: Create shared connection utility
  ```bash
  mkdir -p database/includes
  # Create database/includes/connection.php
  ```

- [ ] **Task 3.2**: Write connection utility
  ```php
  <?php
  // database/includes/connection.php
  require_once __DIR__ . '/../../config/database.php';
  return $pdo;
  ```

- [ ] **Task 3.3**: Update CLI scripts to use shared connection
  - [ ] database/run_setup.php
  - [ ] database/seed_profitable.php
  - [ ] database/reset_tanpa_hapus_users.php
  - [ ] database/clean_data.php
  - [ ] cli/*.php files (if needed)

- [ ] **Task 3.4**: Test all CLI scripts
  - [ ] Test database setup script
  - [ ] Test seeder scripts
  - [ ] Test reset script
  - [ ] Test cleanup script

- [ ] **Task 3.5**: Commit changes
  ```bash
  git add database/
  git commit -m "Centralize database connection logic"
  ```

---

### **CC4: Standardize Environment Variables**

- [ ] **Task 4.1**: Create environment helper utility
  ```bash
  # Create config/env.php
  ```

- [ ] **Task 4.2**: Write environment helper functions
  ```php
  <?php
  // config/env.php
  function env(string $key, $default = null) { ... }
  function envBool(string $key, bool $default = false): bool { ... }
  function envInt(string $key, int $default = 0): int { ... }
  ```

- [ ] **Task 4.3**: Update config files to use helper
  - [ ] config/app.php
  - [ ] config/database.php
  - [ ] Update boolean checks to use envBool()

- [ ] **Task 4.4**: Standardize APP_DEBUG usage
  - [ ] Find all `$_ENV['APP_DEBUG']` usage
  - [ ] Replace with `envBool('APP_DEBUG')`
  - [ ] Test debug mode on/off

- [ ] **Task 4.5**: Add environment validation
  ```php
  // Validate required environment variables on startup
  ```

- [ ] **Task 4.6**: Commit changes
  ```bash
  git add config/
  git commit -m "Standardize environment variable handling"
  ```

---

## 🟠 PHASE 2: HIGH RISK CONFLICTS (Day 2)

**Priority**: SHOULD FIX BEFORE MERGE  
**Estimated Time**: 6-8 hours  

### **HR1: Rename Files to .view.php Convention**

- [ ] **Task 5.1**: Rename stok module files
  ```bash
  git mv src/views/stok/masuk.php src/views/stok/masuk.view.php
  git mv src/views/stok/history.php src/views/stok/history.view.php
  git mv src/views/stok/opname.php src/views/stok/opname.view.php
  git mv src/views/stok/transfer.php src/views/stok/transfer.view.php
  ```

- [ ] **Task 5.2**: Rename master-data module files
  ```bash
  git mv src/views/master-data/jenis-ikan.php src/views/master-data/jenis-ikan.view.php
  git mv src/views/master-data/migrasi.php src/views/master-data/migrasi.view.php
  ```

- [ ] **Task 5.3**: Rename create files
  ```bash
  git mv src/views/penitipan/create.php src/views/penitipan/create.view.php
  git mv src/views/penjualan/create.php src/views/penjualan/create.view.php
  git mv src/views/retur/create.php src/views/retur/create.view.php
  ```

- [ ] **Task 5.4**: Rename other module files
  ```bash
  git mv src/views/checker/draft-penjualan.php src/views/checker/draft-penjualan.view.php
  git mv src/views/pages/dashboard.php src/views/pages/dashboard.view.php
  ```

- [ ] **Task 5.5**: Update routes/web.php
  - [ ] Update '/stok/masuk' route
  - [ ] Update '/stok/history' route
  - [ ] Update '/stok-opname' route
  - [ ] Update '/stok-transfer' route
  - [ ] Update '/master-data/jenis-ikan' route
  - [ ] Update '/migrasi' route
  - [ ] Update '/penitipan/create' route
  - [ ] Update '/penjualan/create' route
  - [ ] Update '/retur/create' route
  - [ ] Update '/checker/draft-penjualan' route
  - [ ] Update '/dashboard' route

- [ ] **Task 5.6**: Test all renamed routes
  - [ ] Test each route loads correctly
  - [ ] Check for 404 errors
  - [ ] Verify functionality works

- [ ] **Task 5.7**: Commit changes
  ```bash
  git add src/views/ routes/
  git commit -m "Rename all view files to .view.php convention"
  ```

---

### **HR2: Update .gitignore**

- [ ] **Task 6.1**: Review current .gitignore
  ```bash
  cat .gitignore
  ```

- [ ] **Task 6.2**: Add missing entries
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
  
  # Backup files
  .backup/
  *.bak
  ```

- [ ] **Task 6.3**: Check for tracked sensitive files
  ```bash
  git ls-files | grep -E '\.(env|log|sql)$'
  ```

- [ ] **Task 6.4**: Remove sensitive files from git if found
  ```bash
  git rm --cached <sensitive-file>
  ```

- [ ] **Task 6.5**: Commit changes
  ```bash
  git add .gitignore
  git commit -m "Update .gitignore with comprehensive entries"
  ```

---

### **HR3: Verify Duplicate Configs**

- [ ] **Task 7.1**: Document purpose of each duplicate
  - [ ] .htaccess (root vs public) - Different purposes ✓
  - [ ] manifest.json (root vs build) - Different purposes ✓
  - [ ] README.md (3 files) - Different purposes ✓
  - [ ] app.php (config vs layout) - Different purposes ✓
  - [ ] database.php (config vs utility) - Different purposes ✓

- [ ] **Task 7.2**: Add comments to clarify purpose
  ```php
  // Add header comments to each file explaining its purpose
  ```

- [ ] **Task 7.3**: Update documentation
  - [ ] Document file structure in README
  - [ ] Explain duplicate files in docs

- [ ] **Task 7.4**: Commit changes
  ```bash
  git add .
  git commit -m "Document purpose of duplicate configuration files"
  ```

---

## 🟡 PHASE 3: MEDIUM RISK CONFLICTS (Day 3)

**Priority**: FIX FOR CONSISTENCY  
**Estimated Time**: 4-6 hours  

### **MR1: Standardize Error Handling**

- [ ] **Task 8.1**: Create error response standard
  ```php
  // Create src/utils/ErrorResponse.php
  ```

- [ ] **Task 8.2**: Define error response format
  ```php
  {
    "success": false,
    "message": "User-friendly message",
    "error_code": "SPECIFIC_ERROR_CODE",
    "errors": {
      "field": "Field-specific error"
    },
    "debug": "Debug info (only in dev mode)"
  }
  ```

- [ ] **Task 8.3**: Create error code constants
  ```php
  // Create src/constants/ErrorCodes.php
  const ERR_VALIDATION = 'VALIDATION_ERROR';
  const ERR_AUTH = 'AUTHENTICATION_ERROR';
  const ERR_PERMISSION = 'PERMISSION_DENIED';
  // etc...
  ```

- [ ] **Task 8.4**: Update controllers to use standard
  - [ ] StokController.php
  - [ ] PenjualanController.php
  - [ ] KeuanganController.php
  - [ ] MasterDataController.php
  - [ ] All other controllers

- [ ] **Task 8.5**: Test error responses
  - [ ] Test validation errors
  - [ ] Test authentication errors
  - [ ] Test permission errors
  - [ ] Verify error format consistency

- [ ] **Task 8.6**: Commit changes
  ```bash
  git add src/
  git commit -m "Standardize error handling across all controllers"
  ```

---

### **MR2: Audit Timezone Handling**

- [ ] **Task 9.1**: Verify database timezone setting
  ```sql
  SHOW VARIABLES LIKE '%time_zone%';
  ```

- [ ] **Task 9.2**: Check all date/time queries
  - [ ] Find all NOW(), CURDATE(), CURTIME() usage
  - [ ] Verify timezone awareness
  - [ ] Check date comparisons

- [ ] **Task 9.3**: Standardize date formatting
  - [ ] PHP: Use DateTime with timezone
  - [ ] Database: Use consistent timezone
  - [ ] Frontend: Display in WIB

- [ ] **Task 9.4**: Test date/time operations
  - [ ] Test date filtering
  - [ ] Test date display
  - [ ] Test date calculations

- [ ] **Task 9.5**: Document timezone handling
  - [ ] Add to technical documentation
  - [ ] Document best practices

- [ ] **Task 9.6**: Commit changes
  ```bash
  git add .
  git commit -m "Audit and standardize timezone handling"
  ```

---

### **MR3: Document API Response Format**

- [ ] **Task 10.1**: Create API documentation
  ```bash
  # Create .docs/API_RESPONSE_FORMAT.md
  ```

- [ ] **Task 10.2**: Document success response format
  ```json
  {
    "success": true,
    "data": { ... },
    "message": "Optional success message",
    "pagination": { ... } // Optional
  }
  ```

- [ ] **Task 10.3**: Document error response format
  ```json
  {
    "success": false,
    "message": "Error message",
    "error_code": "ERROR_CODE",
    "errors": { ... }
  }
  ```

- [ ] **Task 10.4**: Document all endpoints
  - [ ] List all API endpoints
  - [ ] Document request format
  - [ ] Document response format
  - [ ] Add examples

- [ ] **Task 10.5**: Commit documentation
  ```bash
  git add .docs/
  git commit -m "Add comprehensive API response format documentation"
  ```

---

## 🔵 PHASE 4: LOW RISK CONFLICTS (Day 4)

**Priority**: POLISH AND CLEANUP  
**Estimated Time**: 2-4 hours  

### **LR1: Code Style Consistency**

- [ ] **Task 11.1**: Run code formatter
  ```bash
  # If using PHP CS Fixer
  php-cs-fixer fix src/
  ```

- [ ] **Task 11.2**: Check indentation consistency
  - [ ] Verify 4 spaces (or tabs)
  - [ ] Fix mixed indentation

- [ ] **Task 11.3**: Check naming conventions
  - [ ] camelCase for methods
  - [ ] PascalCase for classes
  - [ ] snake_case for database columns

- [ ] **Task 11.4**: Add missing PHPDoc comments
  - [ ] Controllers
  - [ ] Services
  - [ ] Utilities

- [ ] **Task 11.5**: Commit changes
  ```bash
  git add .
  git commit -m "Improve code style consistency"
  ```

---

### **LR2: Update Documentation**

- [ ] **Task 12.1**: Update README.md
  - [ ] Update port to 8080
  - [ ] Update installation instructions
  - [ ] Update usage examples

- [ ] **Task 12.2**: Update .docs/user.md
  - [ ] Update URLs to port 8080
  - [ ] Update screenshots if needed

- [ ] **Task 12.3**: Update PRD documents
  - [ ] Update tech stack notes
  - [ ] Update deployment guide

- [ ] **Task 12.4**: Create CONTRIBUTING.md
  - [ ] Coding standards
  - [ ] Git workflow
  - [ ] Testing guidelines

- [ ] **Task 12.5**: Commit changes
  ```bash
  git add .
  git commit -m "Update documentation for port 8080 and current state"
  ```

---

## ✅ FINAL VERIFICATION

### **Pre-Merge Checklist**

- [ ] **V1**: All duplicate files removed
- [ ] **V2**: All hardcoded paths replaced
- [ ] **V3**: Database connection centralized
- [ ] **V4**: Environment variables standardized
- [ ] **V5**: All files renamed to .view.php
- [ ] **V6**: Routes updated and tested
- [ ] **V7**: .gitignore updated
- [ ] **V8**: Error handling standardized
- [ ] **V9**: Timezone handling verified
- [ ] **V10**: API format documented
- [ ] **V11**: Code style consistent
- [ ] **V12**: Documentation updated

### **Functional Testing**

- [ ] **T1**: Application loads at http://localhost:8080/
- [ ] **T2**: Login works
- [ ] **T3**: All modules accessible
- [ ] **T4**: CRUD operations work
- [ ] **T5**: API endpoints functional
- [ ] **T6**: File uploads work
- [ ] **T7**: Reports generate correctly
- [ ] **T8**: No console errors
- [ ] **T9**: No broken links
- [ ] **T10**: CORS works correctly

### **Code Quality**

- [ ] **Q1**: No syntax errors
- [ ] **Q2**: No undefined variables
- [ ] **Q3**: No SQL injection vulnerabilities
- [ ] **Q4**: No XSS vulnerabilities
- [ ] **Q5**: Proper error handling
- [ ] **Q6**: Consistent code style
- [ ] **Q7**: Adequate comments
- [ ] **Q8**: No dead code
- [ ] **Q9**: No duplicate code
- [ ] **Q10**: Performance acceptable

---

## 📊 PROGRESS TRACKING

### **Overall Progress**

```
Phase 1 (Critical):     [ ] 0/15 tasks (0%)
Phase 2 (High):         [ ] 0/12 tasks (0%)
Phase 3 (Medium):       [ ] 0/10 tasks (0%)
Phase 4 (Low):          [ ] 0/10 tasks (0%)
Verification:           [ ] 0/30 checks (0%)
─────────────────────────────────────────
Total:                  [ ] 0/77 items (0%)
```

### **Time Tracking**

| Phase | Estimated | Actual | Status |
|-------|-----------|--------|--------|
| Phase 1 | 6-8h | - | ⏳ Pending |
| Phase 2 | 6-8h | - | ⏳ Pending |
| Phase 3 | 4-6h | - | ⏳ Pending |
| Phase 4 | 2-4h | - | ⏳ Pending |
| **Total** | **18-26h** | **-** | **⏳ Pending** |

---

## 🎯 DAILY GOALS

### **Day 1 Goals**
- [ ] Complete Phase 1 (Critical Conflicts)
- [ ] Remove all duplicate files
- [ ] Fix 50+ hardcoded paths
- [ ] Centralize database connection
- [ ] Standardize environment variables

### **Day 2 Goals**
- [ ] Complete Phase 2 (High Risk)
- [ ] Rename all files to .view.php
- [ ] Update all routes
- [ ] Update .gitignore
- [ ] Verify duplicate configs

### **Day 3 Goals**
- [ ] Complete Phase 3 (Medium Risk)
- [ ] Standardize error handling
- [ ] Audit timezone handling
- [ ] Document API format

### **Day 4 Goals**
- [ ] Complete Phase 4 (Low Risk)
- [ ] Code style consistency
- [ ] Update documentation
- [ ] Final verification
- [ ] Merge ready!

---

## 📞 SUPPORT & ESCALATION

### **If Stuck on a Task:**
1. Check `merge_conflicts.md` for detailed explanation
2. Review related documentation
3. Test in isolated environment first
4. Ask for code review if unsure

### **If Task Takes Too Long:**
1. Break into smaller subtasks
2. Focus on critical path first
3. Document blockers
4. Adjust timeline if needed

---

## 🎉 COMPLETION CRITERIA

**Ready to Merge When:**
- ✅ All critical tasks complete
- ✅ All high priority tasks complete
- ✅ All tests passing
- ✅ No console errors
- ✅ Documentation updated
- ✅ Code review approved

---

**Created**: May 30, 2026  
**Status**: ⏳ **READY TO START**  
**Estimated Completion**: 2-4 days  

---

*Gunakan checklist ini untuk melacak progress perbaikan merge conflicts. Update status setiap task setelah selesai.*