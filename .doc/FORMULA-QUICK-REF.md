# 🧮 FORMULA QUICK REFERENCE
## Peace Seafood - Cheat Sheet

> **Quick reference untuk semua formula perhitungan. Print dan tempel di dinding!**

---

## 📦 STOK (INVENTORY)

### **Stok Masuk (Weighted Average)**
```
Nilai Baru = Nilai Lama + (Qty Masuk × Harga Beli Baru)
Qty Baru = Qty Lama + Qty Masuk
Harga Rata-rata = Nilai Baru / Qty Baru
```

**Example:**
```
100 kg @ Rp 50k = Rp 5M
+ 50 kg @ Rp 60k = Rp 3M
= 150 kg = Rp 8M
Avg = Rp 8M / 150 = Rp 53.333/kg
```

---

### **Stok Keluar (Penjualan)**
```
Nilai Keluar = Qty Keluar × Harga Rata-rata
Nilai Baru = Nilai Lama - Nilai Keluar
Qty Baru = Qty Lama - Qty Keluar
Harga Rata-rata = TETAP (tidak berubah)
```

**Example:**
```
150 kg @ Rp 53.333 = Rp 8M
- 30 kg
COGS = 30 × Rp 53.333 = Rp 1.6M
= 120 kg = Rp 6.4M
Avg = Rp 53.333/kg (TETAP)
```

---

## 🔄 RETUR

### **Retur Stok (Barang Kembali)**
```
Qty Baru = Qty Lama + Qty Retur  ← TAMBAH!
Nilai Tambahan = Qty Retur × Harga Rata-rata
Nilai Baru = Nilai Lama + Nilai Tambahan
```

**Example:**
```
120 kg @ Rp 53.333 = Rp 6.4M
+ 10 kg retur
= 130 kg = Rp 6.933M
```

---

### **Retur Piutang (Adjustment)**
```
Nominal Baru = Nominal Lama - Nominal Retur
Sisa Hutang Baru = Sisa Hutang Lama - Nominal Retur

Status:
- 'lunas' jika Sisa <= 0
- 'sebagian' jika Bayar > 0 dan Sisa > 0
- 'open' jika Bayar = 0
```

**Example:**
```
Hutang: Rp 10M
Bayar: Rp 3M
Sisa: Rp 7M
Retur: Rp 2M
= Nominal: Rp 8M, Sisa: Rp 5M
Status: 'sebagian'
```

---

## 💰 HUTANG/PIUTANG

### **Pembayaran**
```
Sisa Baru = Sisa Lama - Nominal Bayar
Total Bayar = Total Bayar Lama + Nominal Bayar

Status:
- 'lunas' jika Sisa <= 0
- 'sebagian' jika Total Bayar > 0 dan Sisa > 0
- 'open' jika Total Bayar = 0
```

**Example:**
```
Hutang: Rp 10M
Bayar: Rp 3M
Sisa: Rp 7M
Bayar lagi: Rp 4M
= Total: Rp 7M, Sisa: Rp 3M
Status: 'sebagian'
```

---

## 🤝 PENITIPAN

### **Komisi (PENTING!)**
```
Harga Kesepakatan = Nominal Total / Qty Total
Total Kesepakatan = Qty Terjual × Harga Kesepakatan
Komisi = Total Kesepakatan × Komisi % / 100
```

**⚠️ BUKAN dari harga jual aktual!**

**Example:**
```
Titipan: 100 kg @ Rp 100k = Rp 10M
Komisi: 5%
Jual: 10 kg @ Rp 120k (lebih tinggi)

Total kesepakatan = 10 × Rp 100k = Rp 1M
Komisi = Rp 1M × 5% = Rp 50k
(BUKAN Rp 1.2M × 5% = Rp 60k!)
```

---

### **Pembayaran Penitipan**

**Potong Langsung:**
```
Bayar ke Pengirim = Total Jual - Komisi
Penerima Dapat = Komisi (cash)
```

**Bayar Terpisah:**
```
Bayar ke Pengirim = Total Jual (full)
Piutang Komisi = Komisi (dari pengirim)
```

---

## 📊 PENJUALAN

### **Nota Calculation**
```
Subtotal Item = Qty × Harga Jual
Subtotal = Σ(Subtotal Item)
Total = Subtotal - Diskon + Pajak
```

**Example:**
```
Item 1: 20 kg × Rp 70k = Rp 1.4M
Item 2: 15 kg × Rp 90k = Rp 1.35M
Subtotal = Rp 2.75M
Diskon = Rp 200k
Total = Rp 2.55M
```

---

### **Profit Calculation**
```
COGS = Σ(Qty × Harga Beli Rata-rata)
Revenue = Σ(Qty × Harga Jual)
Gross Profit = Revenue - COGS
Margin % = (Profit / Revenue) × 100%
```

**Example:**
```
Jual: 30 kg @ Rp 70k = Rp 2.1M
COGS: 30 kg @ Rp 53.333 = Rp 1.6M
Profit = Rp 2.1M - Rp 1.6M = Rp 500k
Margin = (Rp 500k / Rp 2.1M) × 100% = 23.8%
```

---

## ✅ VALIDATION RULES

### **Stok:**
```
✓ Nilai Stok = Qty × Harga Rata-rata
✓ Harga Rata-rata berubah hanya saat stok masuk
✓ Tidak ada nilai negatif
```

### **Hutang/Piutang:**
```
✓ Sisa Hutang = Nominal - Total Bayar
✓ Status sesuai dengan sisa hutang
✓ History tercatat semua
```

### **Penitipan:**
```
✓ Komisi dari harga kesepakatan
✓ BUKAN dari harga jual aktual
✓ Qty tersisa = Qty total - Qty terjual
```

---

## 🚨 COMMON MISTAKES

### **❌ SALAH:**
```
// Stok masuk
nilai_stok = qty_baru × harga_beli_baru  ← SALAH!

// Retur stok
stok_baru = stok_lama - qty_retur  ← SALAH!

// Komisi
komisi = total_jual × komisi_persen  ← SALAH!

// Retur piutang
nominal_baru = max(nominal_bayar, nominal - retur)  ← SALAH!
```

### **✅ BENAR:**
```
// Stok masuk (weighted average)
nilai_stok = nilai_lama + (qty_masuk × harga_beli)
harga_avg = nilai_stok / qty_stok

// Retur stok (TAMBAH, bukan kurang)
stok_baru = stok_lama + qty_retur

// Komisi (dari kesepakatan)
komisi = (qty × harga_kesepakatan) × komisi_persen

// Retur piutang
nominal_baru = nominal_lama - nominal_retur
sisa_baru = sisa_lama - nominal_retur
```

---

## 🔍 QUICK DEBUG

### **Nilai Stok Tidak Sesuai?**
```sql
SELECT 
    nama,
    stok_qty,
    nilai_stok,
    harga_beli,
    (stok_qty * harga_beli) as expected
FROM produk 
WHERE nilai_stok != (stok_qty * harga_beli);
```

### **Sisa Hutang Tidak Sesuai?**
```sql
SELECT 
    id,
    nominal,
    nominal_bayar,
    sisa_hutang,
    (nominal - nominal_bayar) as expected
FROM hutang_piutang 
WHERE sisa_hutang != (nominal - nominal_bayar);
```

### **Status Tidak Sesuai?**
```sql
SELECT 
    id,
    sisa_hutang,
    status,
    CASE
        WHEN sisa_hutang <= 0 THEN 'lunas'
        WHEN nominal_bayar > 0 THEN 'sebagian'
        ELSE 'open'
    END as expected
FROM hutang_piutang 
WHERE status != expected;
```

---

## 📱 MOBILE VERSION

### **Stok:**
```
Masuk: Nilai += Qty × Harga
Keluar: Nilai -= Qty × Avg
Retur: Nilai += Qty × Avg (TAMBAH!)
```

### **Hutang:**
```
Bayar: Sisa -= Bayar
Retur: Sisa -= Retur
Status: Sisa=0→lunas, Bayar>0→sebagian
```

### **Komisi:**
```
Komisi = Qty × (Total/QtyTotal) × %
BUKAN dari harga jual!
```

---

## 🎯 KEY TAKEAWAYS

1. **Weighted Average** untuk nilai stok
2. **Retur stok TAMBAH** inventory
3. **Komisi dari kesepakatan** bukan harga jual
4. **Sisa hutang = Nominal - Bayar**
5. **COGS dari harga rata-rata** saat keluar

---

**Print this page and keep it handy!**

**Version:** 1.0
**Date:** 2025-05-20

