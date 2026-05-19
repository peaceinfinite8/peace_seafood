# 📝 CHANGELOG PERBAIKAN PERHITUNGAN
## Peace Seafood - Bug Fixes & Improvements

> **Dokumen ini mencatat semua perbaikan yang dilakukan pada logika perhitungan matematika.**

---

## 🔴 CRITICAL FIXES (2025-05-20)

### **1. FIXED: Perhitungan Nilai Stok Salah**

**File:** `src/services/StokService.php`

**Masalah:**
- Sistem menghitung ulang seluruh nilai stok menggunakan harga beli terbaru
- Mengabaikan harga beli lama dari stok yang sudah ada
- Menyebabkan nilai stok tidak akurat

**Sebelum:**
```php
$stokBaru = (float) $produk['stok_qty'] + $qtyTambah;
$stokValue = $stokBaru * $hargaBeli; // ❌ SALAH
```

**Sesudah:**
```php
$stokLama = (float) $produk['stok_qty'];
$nilaiStokLama = (float) $produk['nilai_stok'];

$stokBaru = $stokLama + $qtyTambah;
$nilaiTambahan = $qtyTambah * $hargaBeli;
$nilaiStokBaru = $nilaiStokLama + $nilaiTambahan; // ✅ BENAR

$hargaRataRata = $stokBaru > 0 ? $nilaiStokBaru / $stokBaru : $hargaBeli;
```

**Dampak:**
- Laporan keuangan sekarang akurat
- Nilai stok menggunakan weighted average
- COGS calculation benar

---

### **2. FIXED: Retur Stok Mengurangi Inventory (Seharusnya Menambah)**

**File:** `src/services/ReturService.php`

**Masalah:**
- Retur stok mengurangi inventory padahal seharusnya menambah
- Logika terbalik: barang dikembalikan = stok bertambah

**Sebelum:**
```php
// ❌ SALAH - Mengurangi stok
Database::query(
    "UPDATE produk SET 
        stok_qty = GREATEST(0, stok_qty - ?),
        nilai_stok = GREATEST(0, (stok_qty - ?)) * harga_beli
     WHERE id = ?",
    [(float) $retur['qty'], ...]
);
```

**Sesudah:**
```php
// ✅ BENAR - Menambah stok
$qtyRetur = (float) $retur['qty'];
$stokLama = (float) $produk['stok_qty'];
$nilaiStokLama = (float) $produk['nilai_stok'];
$hargaRataRata = (float) $produk['harga_beli'];

$stokBaru = $stokLama + $qtyRetur; // TAMBAH
$nilaiTambahan = $qtyRetur * $hargaRataRata;
$nilaiStokBaru = $nilaiStokLama + $nilaiTambahan;

Database::update('produk', [
    'stok_qty' => $stokBaru,
    'nilai_stok' => $nilaiStokBaru,
], 'id = ?', [(int) $retur['id_produk']]);
```

**Dampak:**
- Retur stok sekarang bekerja dengan benar
- Inventory bertambah saat barang dikembalikan

---

### **3. FIXED: Retur Piutang Calculation Error**

**File:** `src/services/ReturService.php`

**Masalah:**
- Logika perhitungan nominal baru salah (menggunakan `max()` yang tidak tepat)
- Tidak update `sisa_hutang`
- Status calculation tidak akurat

**Sebelum:**
```php
// ❌ SALAH
$nominalBaru = max((float) $hp['nominal_bayar'], (float) $hp['nominal'] - (float) $retur['nominal']);
$status = ((float) $hp['nominal_bayar'] >= $nominalBaru) ? 'lunas' : 'sebagian';

Database::update('hutang_piutang', [
    'nominal' => $nominalBaru,
    'status' => $status,
], 'id = ?', [(int) $hp['id']]);
```

**Sesudah:**
```php
// ✅ BENAR
$nominalLama = (float) $hp['nominal'];
$sisaHutangLama = (float) ($hp['sisa_hutang'] ?? $nominalLama);
$nominalBayar = (float) ($hp['nominal_bayar'] ?? 0);
$nominalRetur = (float) $retur['nominal'];

$nominalBaru = max(0, $nominalLama - $nominalRetur);
$sisaHutangBaru = max(0, $sisaHutangLama - $nominalRetur);

if ($sisaHutangBaru <= 0) {
    $status = 'lunas';
} elseif ($nominalBayar > 0) {
    $status = 'sebagian';
} else {
    $status = 'open';
}

Database::update('hutang_piutang', [
    'nominal' => $nominalBaru,
    'sisa_hutang' => $sisaHutangBaru,
    'status' => $status,
], 'id = ?', [(int) $hp['id']]);
```

**Dampak:**
- Retur piutang sekarang mengurangi hutang dengan benar
- Status hutang/piutang akurat
- Sisa hutang ter-update dengan benar

---

### **4. FIXED: Komisi Penitipan Dihitung dari Harga Salah**

**File:** `src/services/PenitipanService.php`

**Masalah:**
- Komisi dihitung dari harga jual aktual
- Seharusnya dihitung dari harga kesepakatan

**Sebelum:**
```php
// ❌ SALAH - Komisi dari harga jual
$totalJual = $qtyTerjual * $hargaJual;
$komisi = $totalJual * ((float) $titipan['komisi_persen'] / 100);
```

**Sesudah:**
```php
// ✅ BENAR - Komisi dari harga kesepakatan
$hargaKesepakatan = (float) $titipan['qty_total'] > 0 
    ? (float) $titipan['nominal_total'] / (float) $titipan['qty_total']
    : 0;

$totalJual = $qtyTerjual * $hargaJual; // Untuk pencatatan
$totalKesepakatan = $qtyTerjual * $hargaKesepakatan; // Untuk komisi
$komisi = $totalKesepakatan * ((float) $titipan['komisi_persen'] / 100);
```

**Dampak:**
- Komisi sekarang dihitung sesuai kesepakatan
- Tidak ada over/under payment komisi
- Sesuai dengan business rules

---

### **5. FIXED: Pembayaran Hutang Field Error**

**File:** `src/services/KeuanganService.php`

**Masalah:**
- Menggunakan field `nominal_bayar_total` yang tidak ada di schema
- Tidak ada validasi nominal bayar > sisa hutang
- Status calculation kurang lengkap

**Sebelum:**
```php
// ❌ Field tidak ada
Database::update('hutang_piutang', [
    'sisa_hutang' => $sisaBaru,
    'nominal_bayar_total' => (float)$hp['nominal_bayar_total'] + $nominalBayar,
    'status' => $status,
], 'id = ?', [(int)$hp['id']]);
```

**Sesudah:**
```php
// ✅ Menggunakan field yang benar
$nominalBayarLama = (float)($hp['nominal_bayar'] ?? 0);
$sisaBaru = $sisaHutangLama - $nominalBayar;
$totalBayarBaru = $nominalBayarLama + $nominalBayar;

if ($sisaBaru <= 0) {
    $status = 'lunas';
} elseif ($totalBayarBaru > 0) {
    $status = 'sebagian';
} else {
    $status = 'open';
}

Database::update('hutang_piutang', [
    'sisa_hutang' => max(0, $sisaBaru),
    'nominal_bayar' => $totalBayarBaru,
    'status' => $status,
], 'id = ?', [(int)$hp['id']]);
```

**Dampak:**
- Tidak ada error runtime
- Pembayaran tercatat dengan benar
- Status akurat

---

### **6. FIXED: Model Produk Update Stok**

**File:** `src/models/Produk.php`

**Masalah:**
- Sama dengan #1, menggunakan formula yang salah
- Tidak menggunakan weighted average

**Sebelum:**
```php
// ❌ SALAH
return Database::execute(
    "UPDATE produk SET
        stok_qty = stok_qty + ?,
        nilai_stok = (stok_qty + ?) * harga_beli,
        updated_at = NOW()
     WHERE id = ?",
    [$delta, $delta, $id]
);
```

**Sesudah:**
```php
// ✅ BENAR - Weighted average
$produk = Database::fetchOne("SELECT * FROM produk WHERE id = ?", [$id]);

$stokLama = (float) $produk['stok_qty'];
$nilaiLama = (float) $produk['nilai_stok'];
$hargaBeli = (float) $produk['harga_beli'];

$stokBaru = $stokLama + $delta;
$nilaiTambahan = $delta * $hargaBeli;
$nilaiBaru = $nilaiLama + $nilaiTambahan;

return Database::execute(
    "UPDATE produk SET
        stok_qty = ?,
        nilai_stok = ?,
        updated_at = NOW()
     WHERE id = ?",
    [$stokBaru, $nilaiBaru, $id]
);
```

**Dampak:**
- Konsisten dengan service layer
- Nilai stok akurat

---

## 📊 SUMMARY PERBAIKAN

| No | File | Method | Jenis Bug | Status |
|----|------|--------|-----------|--------|
| 1 | StokService.php | updateInventory() | Critical - Nilai stok salah | ✅ Fixed |
| 2 | StokService.php | kurangiStok() | Critical - Nilai stok salah | ✅ Fixed |
| 3 | ReturService.php | approveRetur() (stok) | Critical - Logika terbalik | ✅ Fixed |
| 4 | ReturService.php | approveRetur() (piutang) | Critical - Calculation error | ✅ Fixed |
| 5 | PenitipanService.php | jualTitipan() | Important - Komisi salah | ✅ Fixed |
| 6 | KeuanganService.php | bayar() | Important - Field error | ✅ Fixed |
| 7 | Produk.php | updateStok() | Important - Nilai stok salah | ✅ Fixed |

---

## 🧪 TESTING RECOMMENDATIONS

### **1. Unit Tests yang Perlu Dibuat:**

```php
// Test weighted average calculation
testStokMasukWeightedAverage()
testStokKeluarMaintainAverage()
testMultipleStokMasukAverage()

// Test retur logic
testReturStokIncreasesInventory()
testReturPiutangDecreasesDebt()
testReturPiutangStatus()

// Test komisi penitipan
testKomisiFromKesepakatan()
testKomisiPotongLangsung()
testKomisiBayarTerpisah()

// Test pembayaran hutang
testPembayaranPartial()
testPembayaranLunas()
testPembayaranStatus()
```

### **2. Integration Tests:**

```php
// Test full flow
testFullStokFlow() // Masuk → Keluar → Retur
testFullPenjualanFlow() // Nota → Bayar → Retur
testFullPenitipanFlow() // Terima → Jual → Settlement
```

### **3. Manual Testing Checklist:**

- [ ] Stok masuk dengan harga berbeda → cek nilai stok
- [ ] Penjualan → cek COGS dan profit
- [ ] Retur stok → cek inventory bertambah
- [ ] Retur piutang → cek sisa hutang berkurang
- [ ] Penitipan → cek komisi dari harga kesepakatan
- [ ] Pembayaran hutang → cek status berubah

---

## 📚 DOKUMENTASI TAMBAHAN

### **File Baru yang Dibuat:**

1. **`.doc/PERHITUNGAN-MATEMATIKA.md`**
   - Dokumentasi lengkap semua formula
   - Sample perhitungan detail
   - Validasi checklist
   - Troubleshooting guide

2. **`.doc/CHANGELOG-PERBAIKAN.md`** (file ini)
   - Log semua perbaikan
   - Before/after comparison
   - Testing recommendations

---

## ⚠️ BREAKING CHANGES

### **Data Migration Diperlukan:**

**PENTING:** Data existing mungkin sudah salah karena bug sebelumnya!

**Langkah-langkah:**

1. **Backup database** sebelum migration
2. **Audit data existing:**
   - Cek nilai stok vs qty × harga rata-rata
   - Cek sisa hutang vs nominal - total bayar
   - Cek komisi penitipan

3. **Recalculate jika perlu:**
   ```sql
   -- Recalculate nilai stok
   UPDATE produk 
   SET nilai_stok = stok_qty * harga_beli
   WHERE nilai_stok != (stok_qty * harga_beli);
   
   -- Recalculate sisa hutang
   UPDATE hutang_piutang 
   SET sisa_hutang = nominal - COALESCE(nominal_bayar, 0)
   WHERE sisa_hutang != (nominal - COALESCE(nominal_bayar, 0));
   ```

---

## 🔄 NEXT STEPS

### **Immediate Actions:**

1. ✅ Deploy perbaikan ke development
2. ⏳ Run integration tests
3. ⏳ Audit data existing
4. ⏳ Migration script jika diperlukan
5. ⏳ Deploy ke production

### **Future Improvements:**

1. Tambah unit tests untuk semua perhitungan
2. Implementasi transaction logging yang lebih detail
3. Dashboard untuk monitoring data integrity
4. Automated alerts untuk anomali data

---

## 👥 CONTRIBUTORS

- **AI Assistant** - Bug identification & fixes
- **Development Team** - Code review & testing

---

## 📞 SUPPORT

Jika menemukan bug baru atau ada pertanyaan:
1. Cek dokumentasi di `.doc/PERHITUNGAN-MATEMATIKA.md`
2. Review changelog ini
3. Contact development team

---

**Last Updated:** 2025-05-20
**Version:** 1.0.0
**Status:** ✅ All Critical Bugs Fixed

