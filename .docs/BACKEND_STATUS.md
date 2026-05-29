# ⚙️ BACKEND STATUS & OPTIMIZATION PLAN
**Date**: May 30, 2026  
**Status**: In Progress — shared backend helpers added, service query cleanup underway

---

## 📌 OVERVIEW

The backend already has a service layer, but several controllers and services still duplicate the same warehouse resolution, request-body normalization, and access checks. The current optimization pass focuses on moving those repeated paths into shared helpers first, then applying the same pattern to remaining services.

### Current Backend Stack
- **Controllers**: HTTP request orchestration and permission checks
- **Services**: Business logic for stok, penjualan, keuangan, retur, penitipan
- **Models**: Thin database wrappers over the shared `Model` base class
- **Middleware**: Auth and role guards
- **Utilities**: Database, Response, Helper, Validator

---

## ✅ COMPLETED IN THIS PASS

### Shared Helpers Added
Location: `src/utils/Helper.php`

- `firstActiveGudangId(bool $ordered = false)`
  - Centralized active gudang lookup
  - Replaces repeated `SELECT id FROM gudang WHERE is_active = 1 ...` calls

- `normalizeContactFields(array $body)`
  - Normalizes legacy `telepon` input to `telpon`
  - Removes non-database fields like `email`

- `normalizePembeliType(array $body)`
  - Maps legacy buyer types to database ENUM values
  - Handles `umum`, `grosir`, `langganan` aliases

- `buildGudangScope(string $tableAlias, int $idGudang, bool $allGudang = false, string $gudangColumn = 'id_gudang')`
  - Centralizes the common all-gudang vs specific-gudang WHERE clause
  - Removes repeated `1=1` and `id_gudang = ?` branching across reporting services

- `appendFilters(string $where, array $params, array $filters, array $map)`
  - Shared filter builder for date/status criteria
  - Keeps query construction consistent across list/export endpoints

### Controller Cleanup
Location: `src/controllers/MasterDataController.php`

- Removed repeated contact-field normalization in supplier and pembeli write paths
- Removed repeated pembeli type mapping logic
- Replaced duplicated gudang fallback queries with `Helper::firstActiveGudangId()`
- Replaced duplicated ordered gudang lookup for produk with shared helper

### Controller Simplification
Location: `src/controllers/DashboardController.php`

- Replaced local gudang resolution logic with `AuthMiddleware::resolveGudang()`
- Reduced duplicate policy logic in the controller itself

### Service Cleanup
Location: `src/services/StokService.php`

- Replaced local active-gudang fallback query with `Helper::firstActiveGudangId()`
- Kept fallback behavior unchanged when no gudang is available

Location: `src/services/KeuanganService.php`

- Replaced repeated gudang/date/status branching in hutang/piutang, aging, biaya, and payment lookups with shared scope logic

Location: `src/services/PenjualanService.php`

- Replaced repeated nota list filter branching with shared gudang scope logic

Location: `src/services/ExportService.php`

- Replaced repeated stok, penjualan, keuangan, and laporan export filters with shared gudang scope logic

Location: `src/services/ReturService.php`

- Replaced repeated retur list gudang/date/status branching with shared gudang scope logic

---

## 🧠 OBSERVED DUPLICATION PATTERNS

### 1. Active Gudang Lookup
Repeated in:
- `src/controllers/MasterDataController.php`
- `src/services/StokService.php`

Status: centralized in `Helper.php`

### 2. Request Payload Normalization
Repeated in:
- `src/controllers/MasterDataController.php`

Patterns:
- `telepon` → `telpon`
- `email` stripping
- buyer type alias mapping

Status: centralized in `Helper.php`

### 3. Gudang Resolution in Controllers
Repeated in:
- `src/controllers/DashboardController.php`
- `src/controllers/StokController.php`
- `src/controllers/PenjualanController.php`
- `src/controllers/KeuanganController.php`
- `src/controllers/LaporanController.php`

Status: partially centralized already through `AuthMiddleware::resolveGudang()`; some controllers still call it directly, which is acceptable but could be wrapped further if needed.

### 4. Permission Checks
Repeated across most controllers via `RoleMiddleware::requirePermission()`

Status: already standardized, not a current refactor target unless a higher-level controller base is introduced.

---

## 🔧 NEXT OPTIMIZATION TARGETS

### Phase 2: Shared Controller Helpers
Recommended next step if backend cleanup continues:

- Add a small controller helper for common response/error patterns
- Add a reusable gudang context helper for controllers that call `AuthMiddleware::resolveGudang()` plus `AuthMiddleware::isAllGudang()` together
- Normalize repeated query filter construction in report and list endpoints

### Phase 3: Service Query Cleanup
Completed in this pass for the highest-impact report paths:
- `src/services/PenjualanService.php`
- `src/services/KeuanganService.php`
- `src/services/ReturService.php`
- `src/services/ExportService.php`

Look for:
- duplicated SQL fragments
- repeated joins for similar report queries
- repeated balance/summary calculations

### Phase 4: Optional Base Controller
Only if the codebase continues to grow:
- `requirePermissionOrFail()` wrapper
- `resolveGudangContext()` wrapper
- `getJsonBody()` wrapper

This should stay optional. The current codebase does not require a large base-controller rewrite yet.

---

## 📋 VALIDATION

- `src/utils/Helper.php` syntax: OK
- `src/controllers/MasterDataController.php` syntax: OK
- `src/controllers/DashboardController.php` syntax: OK
- `src/services/StokService.php` syntax: OK
- `src/services/KeuanganService.php` syntax: OK
- `src/services/PenjualanService.php` syntax: OK
- `src/services/ReturService.php` syntax: OK
- `src/services/ExportService.php` syntax: OK

---

## 📂 KEY FILES

| File | Purpose | Status |
|------|---------|--------|
| `src/utils/Helper.php` | Shared request and gudang helpers | ✅ Updated |
| `src/controllers/MasterDataController.php` | Master data CRUD endpoints | ✅ Reduced duplication |
| `src/controllers/DashboardController.php` | Dashboard summary endpoint | ✅ Simplified |
| `src/services/StokService.php` | Inventory and stok masuk logic | ✅ Reduced duplication |
| `src/services/KeuanganService.php` | Hutang/piutang and summary queries | ✅ Reduced duplication |
| `src/services/PenjualanService.php` | Nota list and detail logic | ✅ Reduced duplication |
| `src/services/ReturService.php` | Retur list/detail logic | ✅ Reduced duplication |
| `src/services/ExportService.php` | CSV/XLSX report exports | ✅ Reduced duplication |
| `src/middleware/AuthMiddleware.php` | Auth + gudang resolution | ✅ Existing shared source of truth |

---

## 🔄 CURRENT STATUS

The backend is now in a better state than before this pass:

- shared gudang lookup exists
- payload normalization is centralized
- controller-level gudang resolver duplication was removed
- report/query scope branching was consolidated in core services

The next backend pass can focus on smaller remaining opportunities:
- repeated `AuthMiddleware::resolveGudang()` + `isAllGudang()` pairing in controllers
- optional base controller helpers for response/error shortcuts
- deeper query deduplication in `AuthService`, `StokOpnameService`, and the remaining report endpoints

---

*Last Updated: May 30, 2026*