-- =====================================================
-- MIGRATION: Fix Calculation Errors
-- Date: 2025-05-20
-- Description: Recalculate data yang mungkin salah karena bug sebelumnya
-- =====================================================

-- BACKUP TABLES FIRST!
-- CREATE TABLE produk_backup AS SELECT * FROM produk;
-- CREATE TABLE hutang_piutang_backup AS SELECT * FROM hutang_piutang;

-- =====================================================
-- 1. FIX NILAI STOK PRODUK
-- =====================================================

-- Recalculate nilai_stok berdasarkan stok_qty × harga_beli
-- Ini akan memperbaiki nilai stok yang salah karena bug weighted average

UPDATE produk 
SET nilai_stok = stok_qty * harga_beli,
    updated_at = NOW()
WHERE nilai_stok != (stok_qty * harga_beli)
  AND is_active = 1;

-- Log hasil
SELECT 
    'Produk dengan nilai stok diperbaiki' as description,
    COUNT(*) as count
FROM produk 
WHERE nilai_stok = (stok_qty * harga_beli)
  AND is_active = 1;

-- =====================================================
-- 2. FIX SISA HUTANG/PIUTANG
-- =====================================================

-- Recalculate sisa_hutang berdasarkan nominal - nominal_bayar
-- Ini akan memperbaiki sisa hutang yang tidak sinkron

UPDATE hutang_piutang 
SET sisa_hutang = nominal - COALESCE(nominal_bayar, 0),
    updated_at = NOW()
WHERE sisa_hutang != (nominal - COALESCE(nominal_bayar, 0))
  AND status != 'cancelled';

-- Update status berdasarkan sisa_hutang yang benar
UPDATE hutang_piutang 
SET status = CASE
    WHEN sisa_hutang <= 0 THEN 'lunas'
    WHEN COALESCE(nominal_bayar, 0) > 0 AND sisa_hutang > 0 THEN 'sebagian'
    ELSE 'open'
END,
updated_at = NOW()
WHERE status != 'cancelled';

-- Log hasil
SELECT 
    'Hutang/Piutang dengan sisa hutang diperbaiki' as description,
    COUNT(*) as count
FROM hutang_piutang 
WHERE sisa_hutang = (nominal - COALESCE(nominal_bayar, 0))
  AND status != 'cancelled';

-- =====================================================
-- 3. AUDIT DATA INTEGRITY
-- =====================================================

-- Check produk dengan nilai stok negatif (seharusnya tidak ada)
SELECT 
    'ALERT: Produk dengan nilai stok negatif' as alert_type,
    id,
    nama,
    stok_qty,
    nilai_stok,
    harga_beli
FROM produk 
WHERE nilai_stok < 0 OR stok_qty < 0;

-- Check hutang/piutang dengan sisa hutang negatif (seharusnya tidak ada)
SELECT 
    'ALERT: Hutang/Piutang dengan sisa negatif' as alert_type,
    id,
    jenis,
    nominal,
    nominal_bayar,
    sisa_hutang,
    status
FROM hutang_piutang 
WHERE sisa_hutang < 0 AND status != 'cancelled';

-- Check hutang/piutang dengan status tidak sesuai
SELECT 
    'ALERT: Hutang/Piutang dengan status tidak sesuai' as alert_type,
    id,
    jenis,
    nominal,
    nominal_bayar,
    sisa_hutang,
    status,
    CASE
        WHEN sisa_hutang <= 0 THEN 'lunas'
        WHEN COALESCE(nominal_bayar, 0) > 0 AND sisa_hutang > 0 THEN 'sebagian'
        ELSE 'open'
    END as status_seharusnya
FROM hutang_piutang 
WHERE status != CASE
    WHEN sisa_hutang <= 0 THEN 'lunas'
    WHEN COALESCE(nominal_bayar, 0) > 0 AND sisa_hutang > 0 THEN 'sebagian'
    ELSE 'open'
END
AND status != 'cancelled';

-- =====================================================
-- 4. SUMMARY REPORT
-- =====================================================

-- Summary produk
SELECT 
    'SUMMARY: Produk' as report_type,
    COUNT(*) as total_produk,
    SUM(stok_qty) as total_qty,
    SUM(nilai_stok) as total_nilai_stok,
    AVG(harga_beli) as avg_harga_beli
FROM produk 
WHERE is_active = 1;

-- Summary hutang/piutang
SELECT 
    'SUMMARY: Hutang/Piutang' as report_type,
    jenis,
    status,
    COUNT(*) as count,
    SUM(nominal) as total_nominal,
    SUM(nominal_bayar) as total_bayar,
    SUM(sisa_hutang) as total_sisa
FROM hutang_piutang 
WHERE status != 'cancelled'
GROUP BY jenis, status
ORDER BY jenis, status;

-- =====================================================
-- 5. VERIFICATION QUERIES
-- =====================================================

-- Verify: Semua produk nilai_stok = stok_qty × harga_beli
SELECT 
    'VERIFY: Produk nilai stok konsisten' as verification,
    COUNT(*) as total,
    SUM(CASE WHEN nilai_stok = (stok_qty * harga_beli) THEN 1 ELSE 0 END) as correct,
    SUM(CASE WHEN nilai_stok != (stok_qty * harga_beli) THEN 1 ELSE 0 END) as incorrect
FROM produk 
WHERE is_active = 1;

-- Verify: Semua hutang/piutang sisa_hutang = nominal - nominal_bayar
SELECT 
    'VERIFY: Hutang/Piutang sisa hutang konsisten' as verification,
    COUNT(*) as total,
    SUM(CASE WHEN sisa_hutang = (nominal - COALESCE(nominal_bayar, 0)) THEN 1 ELSE 0 END) as correct,
    SUM(CASE WHEN sisa_hutang != (nominal - COALESCE(nominal_bayar, 0)) THEN 1 ELSE 0 END) as incorrect
FROM hutang_piutang 
WHERE status != 'cancelled';

-- Verify: Semua hutang/piutang status sesuai dengan sisa_hutang
SELECT 
    'VERIFY: Hutang/Piutang status konsisten' as verification,
    COUNT(*) as total,
    SUM(CASE 
        WHEN status = CASE
            WHEN sisa_hutang <= 0 THEN 'lunas'
            WHEN COALESCE(nominal_bayar, 0) > 0 AND sisa_hutang > 0 THEN 'sebagian'
            ELSE 'open'
        END THEN 1 
        ELSE 0 
    END) as correct,
    SUM(CASE 
        WHEN status != CASE
            WHEN sisa_hutang <= 0 THEN 'lunas'
            WHEN COALESCE(nominal_bayar, 0) > 0 AND sisa_hutang > 0 THEN 'sebagian'
            ELSE 'open'
        END THEN 1 
        ELSE 0 
    END) as incorrect
FROM hutang_piutang 
WHERE status != 'cancelled';

-- =====================================================
-- 6. CLEANUP (Optional)
-- =====================================================

-- Hapus notifikasi duplikat stok minimum
-- DELETE n1 FROM notifikasi n1
-- INNER JOIN notifikasi n2 
-- WHERE n1.id > n2.id 
--   AND n1.tipe = n2.tipe 
--   AND n1.id_gudang = n2.id_gudang
--   AND n1.pesan = n2.pesan
--   AND n1.is_read = 0;

-- =====================================================
-- NOTES:
-- =====================================================
-- 1. BACKUP database sebelum menjalankan migration ini!
-- 2. Run di development environment dulu
-- 3. Verify hasil dengan query verification
-- 4. Jika ada data incorrect, investigate manual
-- 5. Baru deploy ke production setelah verified

-- =====================================================
-- ROLLBACK (Jika diperlukan):
-- =====================================================
-- UPDATE produk p
-- INNER JOIN produk_backup pb ON p.id = pb.id
-- SET p.nilai_stok = pb.nilai_stok,
--     p.updated_at = pb.updated_at;
--
-- UPDATE hutang_piutang hp
-- INNER JOIN hutang_piutang_backup hpb ON hp.id = hpb.id
-- SET hp.sisa_hutang = hpb.sisa_hutang,
--     hp.status = hpb.status,
--     hp.updated_at = hpb.updated_at;

