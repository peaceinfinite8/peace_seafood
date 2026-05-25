# 🧪 TESTING GUIDE - Perhitungan Matematika
## Peace Seafood - Manual & Automated Testing

> **Panduan lengkap untuk testing semua perhitungan matematika yang telah diperbaiki.**

---

## 📋 TABLE OF CONTENTS

1. [Pre-Testing Checklist](#pre-testing-checklist)
2. [Manual Testing Scenarios](#manual-testing-scenarios)
3. [Automated Testing](#automated-testing)
4. [Data Validation](#data-validation)
5. [Performance Testing](#performance-testing)
6. [Regression Testing](#regression-testing)

---

## ✅ PRE-TESTING CHECKLIST

### **Environment Setup:**

- [ ] Database backup dibuat
- [ ] Development environment ready
- [ ] Test data prepared
- [ ] Dokumentasi perhitungan dibaca (`.docs/changes/calculations-math.md`)
- [ ] Changelog dipahami (`.docs/changes/calculations-changelog.md`)

### **Test Data Requirements:**

```sql
-- Minimal test data:
- 2 Gudang
- 3 Supplier
- 3 Pembeli
- 5 Jenis Ikan
- 10 Produk
- 1 User per role (Bos, Admin, Checker)
```

---

## 🎯 MANUAL TESTING SCENARIOS

### **SCENARIO 1: Stok Masuk - Weighted Average**

**Objective:** Verify weighted average calculation

**Steps:**

1. **Initial State:**
   ```
   Produk: Ikan Tuna
   Stok: 0 kg
   Nilai: Rp 0
   ```

2. **Action 1: Stok Masuk Pertama**
   ```
   Input:
   - Qty: 100 kg
   - Harga beli: Rp 50.000/kg
   
   Expected Result:
   - Stok: 100 kg
   - Nilai: Rp 5.000.000
   - Harga rata-rata: Rp 50.000/kg
   ```

3. **Action 2: Timbang**
   ```
   Input:
   - Qty actual: 98 kg (susut 2 kg)
   
   Expected Result:
   - Stok: 98 kg
   - Nilai: Rp 4.900.000
   - Harga rata-rata: Rp 50.000/kg
   ```

4. **Action 3: Stok Masuk Kedua**
   ```
   Input:
   - Qty: 50 kg
   - Harga beli: Rp 60.000/kg
   
   Expected Result:
   - Stok: 148 kg (98 + 50)
   - Nilai: Rp 7.900.000 (4.900.000 + 3.000.000)
   - Harga rata-rata: Rp 53.378/kg (7.900.000 / 148)
   ```

5. **Verification:**
   ```sql
   SELECT 
       nama,
       stok_qty,
       nilai_stok,
       harga_beli,
       ROUND(nilai_stok / stok_qty, 0) as calculated_avg
   FROM produk 
   WHERE nama = 'Ikan Tuna';
   
   -- Expected:
   -- stok_qty: 148
   -- nilai_stok: 7900000
   -- harga_beli: 53378 (atau 53379, tergantung rounding)
   -- calculated_avg: 53378
   ```

**✅ PASS Criteria:**
- Nilai stok = nilai lama + nilai baru
- Harga rata-rata = nilai stok / qty stok
- Tidak ada nilai negatif

---

### **SCENARIO 2: Penjualan - COGS Calculation**

**Objective:** Verify COGS menggunakan harga rata-rata

**Steps:**

1. **Initial State:**
   ```
   Produk: Ikan Tuna
   Stok: 148 kg
   Nilai: Rp 7.900.000
   Harga rata-rata: Rp 53.378/kg
   ```

2. **Action: Create Nota Penjualan**
   ```
   Input:
   - Produk: Ikan Tuna
   - Qty: 30 kg
   - Harga jual: Rp 70.000/kg
   
   Expected Result:
   - Stok baru: 118 kg (148 - 30)
   - COGS: Rp 1.601.340 (30 × 53.378)
   - Nilai stok baru: Rp 6.298.660 (7.900.000 - 1.601.340)
   - Harga rata-rata: Rp 53.378/kg (TETAP)
   - Revenue: Rp 2.100.000 (30 × 70.000)
   - Profit: Rp 498.660 (2.100.000 - 1.601.340)
   ```

3. **Verification:**
   ```sql
   SELECT 
       p.nama,
       p.stok_qty,
       p.nilai_stok,
       p.harga_beli,
       nd.qty,
       nd.harga_jual,
       nd.subtotal as revenue,
       (nd.qty * p.harga_beli) as cogs,
       (nd.subtotal - (nd.qty * p.harga_beli)) as profit
   FROM nota_detail nd
   JOIN produk p ON nd.id_produk = p.id
   WHERE nd.id_nota = [NOTA_ID];
   
   -- Expected:
   -- stok_qty: 118
   -- nilai_stok: 6298660
   -- harga_beli: 53378 (tidak berubah)
   -- cogs: 1601340
   -- profit: 498660
   ```

**✅ PASS Criteria:**
- Stok berkurang sesuai qty
- Nilai stok berkurang = qty × harga rata-rata
- Harga rata-rata tidak berubah
- COGS = qty × harga rata-rata

---

### **SCENARIO 3: Retur Stok - Inventory Increase**

**Objective:** Verify retur stok menambah inventory

**Steps:**

1. **Initial State:**
   ```
   Produk: Ikan Tuna
   Stok: 118 kg
   Nilai: Rp 6.298.660
   Harga rata-rata: Rp 53.378/kg
   ```

2. **Action: Create Retur Stok**
   ```
   Input:
   - Produk: Ikan Tuna
   - Qty: 10 kg
   - Alasan: Barang rusak
   
   Expected Result (setelah approve):
   - Stok baru: 128 kg (118 + 10) ← TAMBAH
   - Nilai tambahan: Rp 533.780 (10 × 53.378)
   - Nilai stok baru: Rp 6.832.440 (6.298.660 + 533.780)
   - Harga rata-rata: Rp 53.378/kg (TETAP)
   ```

3. **Verification:**
   ```sql
   SELECT 
       p.nama,
       p.stok_qty,
       p.nilai_stok,
       p.harga_beli,
       r.qty as qty_retur,
       r.status
   FROM retur r
   JOIN produk p ON r.id_produk = p.id
   WHERE r.id = [RETUR_ID];
   
   -- Expected:
   -- stok_qty: 128 (bertambah!)
   -- nilai_stok: 6832440
   -- status: approved
   ```

**✅ PASS Criteria:**
- Stok BERTAMBAH (bukan berkurang)
- Nilai stok bertambah = qty retur × harga rata-rata
- Harga rata-rata tidak berubah

---

### **SCENARIO 4: Retur Piutang - Debt Reduction**

**Objective:** Verify retur piutang mengurangi hutang

**Steps:**

1. **Initial State:**
   ```
   Hutang/Piutang:
   - Nominal: Rp 10.000.000
   - Sudah bayar: Rp 3.000.000
   - Sisa hutang: Rp 7.000.000
   - Status: 'sebagian'
   ```

2. **Action: Create Retur Piutang**
   ```
   Input:
   - Nominal retur: Rp 2.000.000
   - Alasan: Potongan kualitas
   
   Expected Result (setelah approve):
   - Nominal baru: Rp 8.000.000 (10.000.000 - 2.000.000)
   - Sisa hutang baru: Rp 5.000.000 (7.000.000 - 2.000.000)
   - Total bayar: Rp 3.000.000 (tidak berubah)
   - Status: 'sebagian' (masih ada sisa)
   ```

3. **Verification:**
   ```sql
   SELECT 
       hp.nominal,
       hp.nominal_bayar,
       hp.sisa_hutang,
       hp.status,
       r.nominal as nominal_retur,
       r.status as retur_status
   FROM hutang_piutang hp
   JOIN retur r ON r.id_nota = hp.id_nota
   WHERE r.id = [RETUR_ID];
   
   -- Expected:
   -- nominal: 8000000
   -- sisa_hutang: 5000000
   -- status: 'sebagian'
   -- retur_status: 'approved'
   ```

**✅ PASS Criteria:**
- Nominal berkurang sesuai retur
- Sisa hutang berkurang sesuai retur
- Status sesuai dengan sisa hutang
- History tercatat

---

### **SCENARIO 5: Penitipan - Komisi Calculation**

**Objective:** Verify komisi dari harga kesepakatan

**Steps:**

1. **Action 1: Terima Titipan**
   ```
   Input:
   - Qty: 100 kg
   - Harga kesepakatan: Rp 100.000/kg
   - Nominal total: Rp 10.000.000
   - Komisi: 5%
   ```

2. **Action 2: Jual Titipan**
   ```
   Input:
   - Qty terjual: 10 kg
   - Harga jual: Rp 120.000/kg (lebih tinggi!)
   
   Expected Result:
   - Total jual: Rp 1.200.000 (10 × 120.000)
   - Harga kesepakatan: Rp 100.000/kg (dari data titipan)
   - Total kesepakatan: Rp 1.000.000 (10 × 100.000)
   - Komisi: Rp 50.000 (1.000.000 × 5%) ← BUKAN Rp 60.000!
   ```

3. **Verification:**
   ```sql
   SELECT 
       t.qty_total,
       t.nominal_total,
       (t.nominal_total / t.qty_total) as harga_kesepakatan,
       tp.qty as qty_terjual,
       tp.harga_jual,
       tp.nominal as total_jual,
       tp.komisi_nominal,
       t.komisi_persen,
       -- Verify komisi calculation
       ((t.nominal_total / t.qty_total) * tp.qty * t.komisi_persen / 100) as expected_komisi
   FROM titipan_penjualan tp
   JOIN titipan t ON tp.id_titipan = t.id
   WHERE tp.id = [PENJUALAN_ID];
   
   -- Expected:
   -- harga_kesepakatan: 100000
   -- total_jual: 1200000
   -- komisi_nominal: 50000 (bukan 60000!)
   -- expected_komisi: 50000
   ```

**✅ PASS Criteria:**
- Komisi dihitung dari harga kesepakatan
- Komisi BUKAN dari harga jual aktual
- Selisih harga jadi keuntungan penerima

---

### **SCENARIO 6: Pembayaran Hutang - Status Update**

**Objective:** Verify pembayaran update status dengan benar

**Steps:**

1. **Initial State:**
   ```
   Hutang:
   - Nominal: Rp 10.000.000
   - Sudah bayar: Rp 0
   - Sisa hutang: Rp 10.000.000
   - Status: 'open'
   ```

2. **Action 1: Pembayaran Pertama**
   ```
   Input:
   - Nominal bayar: Rp 3.000.000
   
   Expected Result:
   - Total bayar: Rp 3.000.000
   - Sisa hutang: Rp 7.000.000
   - Status: 'sebagian' (sudah ada pembayaran)
   ```

3. **Action 2: Pembayaran Kedua**
   ```
   Input:
   - Nominal bayar: Rp 7.000.000
   
   Expected Result:
   - Total bayar: Rp 10.000.000
   - Sisa hutang: Rp 0
   - Status: 'lunas' (sisa = 0)
   ```

4. **Verification:**
   ```sql
   SELECT 
       hp.nominal,
       hp.nominal_bayar,
       hp.sisa_hutang,
       hp.status,
       COUNT(hph.id) as jumlah_pembayaran,
       SUM(hph.nominal) as total_dari_history
   FROM hutang_piutang hp
   LEFT JOIN hutang_piutang_history hph ON hph.id_hutang_piutang = hp.id
   WHERE hp.id = [HUTANG_ID]
   GROUP BY hp.id;
   
   -- Expected:
   -- nominal: 10000000
   -- nominal_bayar: 10000000
   -- sisa_hutang: 0
   -- status: 'lunas'
   -- jumlah_pembayaran: 2
   -- total_dari_history: 10000000
   ```

**✅ PASS Criteria:**
- Sisa hutang = nominal - total bayar
- Status 'open' → 'sebagian' → 'lunas'
- History tercatat semua
- Total dari history = nominal_bayar

---

## 🤖 AUTOMATED TESTING

### **Unit Test Examples (PHPUnit):**

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\StokService;

class StokServiceTest extends TestCase
{
    /**
     * Test weighted average calculation
     */
    public function testWeightedAverageCalculation()
    {
        // Arrange
        $stokService = new StokService();
        
        // Initial stock: 100 kg @ Rp 50.000
        $this->createInitialStock(100, 50000);
        
        // Act: Add 50 kg @ Rp 60.000
        $stokService->inputStokMasuk([
            'id_produk' => 1,
            'id_supplier' => 1,
            'qty' => 50,
            'harga_beli' => 60000,
        ], 1, 1);
        
        // Assert
        $produk = $this->getProduk(1);
        $this->assertEquals(150, $produk['stok_qty']);
        $this->assertEquals(8000000, $produk['nilai_stok']);
        $this->assertEquals(53333, round($produk['harga_beli']));
    }
    
    /**
     * Test retur stok increases inventory
     */
    public function testReturStokIncreasesInventory()
    {
        // Arrange
        $returService = new ReturService();
        $initialStock = 120;
        
        // Act: Retur 10 kg
        $idRetur = $returService->createRetur([
            'tipe' => 'stok',
            'id_produk' => 1,
            'qty' => 10,
            'alasan' => 'Barang rusak',
        ], 1, 1);
        
        $returService->approveRetur($idRetur, 1, 1);
        
        // Assert
        $produk = $this->getProduk(1);
        $this->assertEquals(130, $produk['stok_qty']); // Increased!
        $this->assertGreaterThan($initialStock, $produk['stok_qty']);
    }
    
    /**
     * Test komisi from kesepakatan price
     */
    public function testKomisiFromKesepakatan()
    {
        // Arrange
        $penitipanService = new PenitipanService();
        
        // Titipan: 100 kg @ Rp 100.000, komisi 5%
        $idTitipan = $penitipanService->createTitipan([
            'pembeli_id' => 1,
            'jumlah' => 100,
            'harga_titip' => 100000,
            'komisi_persen' => 5,
        ], 1, 1);
        
        // Act: Jual 10 kg @ Rp 120.000 (higher price)
        $penitipanService->jualTitipan($idTitipan, 1, [
            'jumlah_terjual' => 10,
            'harga_jual' => 120000,
        ], 1);
        
        // Assert
        $penjualan = $this->getTitipanPenjualan($idTitipan);
        $this->assertEquals(50000, $penjualan['komisi_nominal']); // NOT 60000!
        $this->assertEquals(1200000, $penjualan['nominal']); // Total jual
    }
}
```

---

## 📊 DATA VALIDATION

### **SQL Validation Queries:**

```sql
-- 1. Validate nilai stok consistency
SELECT 
    'Produk dengan nilai stok tidak konsisten' as issue,
    id,
    nama,
    stok_qty,
    nilai_stok,
    harga_beli,
    (stok_qty * harga_beli) as expected_nilai,
    (nilai_stok - (stok_qty * harga_beli)) as difference
FROM produk 
WHERE ABS(nilai_stok - (stok_qty * harga_beli)) > 1 -- Allow 1 rupiah rounding
  AND is_active = 1;

-- 2. Validate sisa hutang consistency
SELECT 
    'Hutang/Piutang dengan sisa tidak konsisten' as issue,
    id,
    jenis,
    nominal,
    nominal_bayar,
    sisa_hutang,
    (nominal - COALESCE(nominal_bayar, 0)) as expected_sisa,
    (sisa_hutang - (nominal - COALESCE(nominal_bayar, 0))) as difference
FROM hutang_piutang 
WHERE sisa_hutang != (nominal - COALESCE(nominal_bayar, 0))
  AND status != 'cancelled';

-- 3. Validate status consistency
SELECT 
    'Hutang/Piutang dengan status tidak sesuai' as issue,
    id,
    jenis,
    sisa_hutang,
    nominal_bayar,
    status,
    CASE
        WHEN sisa_hutang <= 0 THEN 'lunas'
        WHEN COALESCE(nominal_bayar, 0) > 0 THEN 'sebagian'
        ELSE 'open'
    END as expected_status
FROM hutang_piutang 
WHERE status != CASE
    WHEN sisa_hutang <= 0 THEN 'lunas'
    WHEN COALESCE(nominal_bayar, 0) > 0 THEN 'sebagian'
    ELSE 'open'
END
AND status != 'cancelled';

-- 4. Validate negative values
SELECT 
    'Data dengan nilai negatif' as issue,
    'produk' as table_name,
    id,
    stok_qty,
    nilai_stok
FROM produk 
WHERE stok_qty < 0 OR nilai_stok < 0

UNION ALL

SELECT 
    'Data dengan nilai negatif' as issue,
    'hutang_piutang' as table_name,
    id,
    nominal,
    sisa_hutang
FROM hutang_piutang 
WHERE nominal < 0 OR sisa_hutang < 0;
```

---

## ⚡ PERFORMANCE TESTING

### **Load Testing Scenarios:**

```bash
# Test 1: Concurrent stok masuk
# 10 users, 100 requests each
ab -n 1000 -c 10 -p stok_masuk.json \
   -T application/json \
   http://localhost/api/stok/masuk

# Test 2: Concurrent nota creation
ab -n 500 -c 5 -p nota.json \
   -T application/json \
   http://localhost/api/penjualan/nota

# Test 3: Concurrent pembayaran
ab -n 200 -c 5 -p bayar.json \
   -T application/json \
   http://localhost/api/keuangan/bayar
```

**Expected Performance:**
- Response time < 500ms for single operations
- Response time < 2s for complex calculations
- No deadlocks or race conditions
- Data consistency maintained under load

---

## 🔄 REGRESSION TESTING

### **Regression Test Checklist:**

After every code change, run these tests:

- [ ] Stok masuk weighted average
- [ ] Stok keluar COGS calculation
- [ ] Retur stok increases inventory
- [ ] Retur piutang decreases debt
- [ ] Komisi from kesepakatan
- [ ] Pembayaran status update
- [ ] Nota calculation (subtotal, diskon, total)
- [ ] Multi-gudang data isolation
- [ ] Permission checks per role

---

## 📝 TEST REPORT TEMPLATE

```markdown
# Test Report - [Date]

## Environment
- Database: MySQL 8.0
- PHP: 8.2
- Server: XAMPP

## Test Summary
- Total Tests: X
- Passed: Y
- Failed: Z
- Skipped: W

## Failed Tests
1. Test Name: [Name]
   - Expected: [Expected Result]
   - Actual: [Actual Result]
   - Root Cause: [Analysis]
   - Fix: [Solution]

## Performance Metrics
- Average Response Time: Xms
- Max Response Time: Yms
- Throughput: Z req/s

## Data Integrity
- Produk nilai stok: ✅ Consistent
- Hutang/Piutang sisa: ✅ Consistent
- Status: ✅ Consistent
- Negative values: ✅ None found

## Recommendations
- [Recommendation 1]
- [Recommendation 2]
```

---

## 🎓 TRAINING MATERIALS

### **For QA Team:**

1. Read `.docs/changes/calculations-math.md` thoroughly
2. Understand weighted average concept
3. Practice manual calculations
4. Run all scenarios at least once
5. Document any issues found

### **For Developers:**

1. Review `.docs/changes/calculations-changelog.md`
2. Understand the fixes made
3. Write unit tests for new features
4. Run regression tests before commit
5. Update documentation if needed

---

**Last Updated:** 2025-05-20
**Version:** 1.0
**Status:** ✅ Ready for Testing

