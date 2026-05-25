# 📐 DOKUMENTASI PERHITUNGAN MATEMATIKA
## Peace Seafood - Formula & Sample Perhitungan

> **Dokumen ini berisi semua formula matematika yang digunakan dalam sistem beserta contoh perhitungan detail.**

---

## 📦 1. PERHITUNGAN STOK (INVENTORY)

### **1.1. Stok Masuk - Weighted Average Method**

**Formula:**
```
Nilai Stok Baru = Nilai Stok Lama + (Qty Masuk × Harga Beli Baru)
Qty Stok Baru = Qty Stok Lama + Qty Masuk
Harga Rata-rata Baru = Nilai Stok Baru / Qty Stok Baru
```

**Contoh Perhitungan:**

**Kondisi Awal:**
- Stok: 100 kg
- Harga beli rata-rata: Rp 50.000/kg
- Nilai stok: 100 × Rp 50.000 = **Rp 5.000.000**

**Stok Masuk:**
- Qty masuk: 50 kg
- Harga beli baru: Rp 60.000/kg
- Nilai masuk: 50 × Rp 60.000 = **Rp 3.000.000**

**Hasil Perhitungan:**
```
Nilai Stok Baru = Rp 5.000.000 + Rp 3.000.000 = Rp 8.000.000
Qty Stok Baru = 100 kg + 50 kg = 150 kg
Harga Rata-rata Baru = Rp 8.000.000 / 150 kg = Rp 53.333,33/kg
```

**Kesimpulan:**
- Stok baru: **150 kg**
- Nilai stok baru: **Rp 8.000.000**
- Harga rata-rata: **Rp 53.333/kg**

---

### **1.2. Stok Keluar (Penjualan)**

**Formula:**
```
Nilai Keluar = Qty Keluar × Harga Rata-rata
Nilai Stok Baru = Nilai Stok Lama - Nilai Keluar
Qty Stok Baru = Qty Stok Lama - Qty Keluar
Harga Rata-rata = TETAP (tidak berubah)
```

**Contoh Perhitungan:**

**Kondisi Awal:**
- Stok: 150 kg
- Harga rata-rata: Rp 53.333/kg
- Nilai stok: **Rp 8.000.000**

**Penjualan:**
- Qty terjual: 30 kg
- Harga jual: Rp 70.000/kg (untuk customer)

**Hasil Perhitungan:**
```
Nilai Keluar = 30 kg × Rp 53.333 = Rp 1.599.990 ≈ Rp 1.600.000
Nilai Stok Baru = Rp 8.000.000 - Rp 1.600.000 = Rp 6.400.000
Qty Stok Baru = 150 kg - 30 kg = 120 kg
Harga Rata-rata = Rp 53.333/kg (TETAP)
```

**Kesimpulan:**
- Stok baru: **120 kg**
- Nilai stok baru: **Rp 6.400.000**
- Harga rata-rata: **Rp 53.333/kg** (tidak berubah)
- COGS (Cost of Goods Sold): **Rp 1.600.000**
- Revenue: 30 × Rp 70.000 = **Rp 2.100.000**
- Gross Profit: Rp 2.100.000 - Rp 1.600.000 = **Rp 500.000**

---

### **1.3. Stok Masuk Lagi (Multiple Purchases)**

**Contoh Perhitungan Lanjutan:**

**Kondisi Awal:**
- Stok: 120 kg
- Harga rata-rata: Rp 53.333/kg
- Nilai stok: **Rp 6.400.000**

**Stok Masuk Lagi:**
- Qty masuk: 80 kg
- Harga beli: Rp 55.000/kg
- Nilai masuk: 80 × Rp 55.000 = **Rp 4.400.000**

**Hasil Perhitungan:**
```
Nilai Stok Baru = Rp 6.400.000 + Rp 4.400.000 = Rp 10.800.000
Qty Stok Baru = 120 kg + 80 kg = 200 kg
Harga Rata-rata Baru = Rp 10.800.000 / 200 kg = Rp 54.000/kg
```

**Kesimpulan:**
- Stok baru: **200 kg**
- Nilai stok baru: **Rp 10.800.000**
- Harga rata-rata baru: **Rp 54.000/kg**

---

## 🔄 2. PERHITUNGAN RETUR

### **2.1. Retur Stok (Barang Dikembalikan)**

**Formula:**
```
Qty Stok Baru = Qty Stok Lama + Qty Retur
Nilai Tambahan = Qty Retur × Harga Rata-rata
Nilai Stok Baru = Nilai Stok Lama + Nilai Tambahan
```

**Contoh Perhitungan:**

**Kondisi Awal:**
- Stok: 120 kg
- Harga rata-rata: Rp 53.333/kg
- Nilai stok: **Rp 6.400.000**

**Retur dari Pembeli:**
- Qty retur: 10 kg (barang rusak)
- Alasan: Kualitas tidak sesuai

**Hasil Perhitungan:**
```
Nilai Tambahan = 10 kg × Rp 53.333 = Rp 533.330
Nilai Stok Baru = Rp 6.400.000 + Rp 533.330 = Rp 6.933.330
Qty Stok Baru = 120 kg + 10 kg = 130 kg
Harga Rata-rata = Rp 53.333/kg (TETAP)
```

**Kesimpulan:**
- Stok baru: **130 kg**
- Nilai stok baru: **Rp 6.933.330**
- Barang retur masuk kembali ke inventory dengan harga rata-rata yang sama

---

### **2.2. Retur Piutang (Adjustment Finansial)**

**Formula:**
```
Nominal Baru = Nominal Lama - Nominal Retur
Sisa Hutang Baru = Sisa Hutang Lama - Nominal Retur

Status:
- 'lunas' jika Sisa Hutang Baru <= 0
- 'sebagian' jika Sudah Ada Pembayaran dan Sisa > 0
- 'open' jika Belum Ada Pembayaran
```

**Contoh Perhitungan:**

**Kondisi Awal:**
- Hutang awal: **Rp 10.000.000**
- Sudah bayar: **Rp 3.000.000**
- Sisa hutang: **Rp 7.000.000**
- Status: **'sebagian'**

**Retur Piutang:**
- Nominal retur: **Rp 2.000.000**
- Alasan: Potongan kualitas barang

**Hasil Perhitungan:**
```
Nominal Baru = Rp 10.000.000 - Rp 2.000.000 = Rp 8.000.000
Sisa Hutang Baru = Rp 7.000.000 - Rp 2.000.000 = Rp 5.000.000
Total Bayar = Rp 3.000.000 (tidak berubah)
Status = 'sebagian' (karena sudah ada pembayaran Rp 3.000.000)
```

**Kesimpulan:**
- Nominal hutang baru: **Rp 8.000.000**
- Sisa yang harus dibayar: **Rp 5.000.000**
- Status: **'sebagian'**

---

**Contoh 2: Retur Lebih Besar dari Sisa Hutang**

**Kondisi Awal:**
- Hutang awal: **Rp 5.000.000**
- Sudah bayar: **Rp 2.000.000**
- Sisa hutang: **Rp 3.000.000**

**Retur Piutang:**
- Nominal retur: **Rp 4.000.000** (lebih besar dari sisa)

**Hasil Perhitungan:**
```
Nominal Baru = Rp 5.000.000 - Rp 4.000.000 = Rp 1.000.000
Sisa Hutang Baru = max(0, Rp 3.000.000 - Rp 4.000.000) = Rp 0
Status = 'lunas' (sisa hutang = 0)
```

**Kesimpulan:**
- Hutang lunas karena retur lebih besar dari sisa
- Kelebihan Rp 1.000.000 bisa jadi kredit untuk pembelian berikutnya

---

## 💰 3. PERHITUNGAN HUTANG/PIUTANG

### **3.1. Pembayaran Hutang/Piutang**

**Formula:**
```
Sisa Hutang Baru = Sisa Hutang Lama - Nominal Bayar
Total Bayar Baru = Total Bayar Lama + Nominal Bayar

Status:
- 'lunas' jika Sisa Hutang Baru <= 0
- 'sebagian' jika Total Bayar > 0 dan Sisa Hutang > 0
- 'open' jika Total Bayar = 0
```

**Contoh Perhitungan:**

**Kondisi Awal:**
- Hutang total: **Rp 10.000.000**
- Sudah bayar: **Rp 3.000.000**
- Sisa hutang: **Rp 7.000.000**
- Status: **'sebagian'**

**Pembayaran 1:**
- Nominal bayar: **Rp 4.000.000**

**Hasil Perhitungan:**
```
Total Bayar Baru = Rp 3.000.000 + Rp 4.000.000 = Rp 7.000.000
Sisa Hutang Baru = Rp 7.000.000 - Rp 4.000.000 = Rp 3.000.000
Status = 'sebagian' (masih ada sisa Rp 3.000.000)
```

**Pembayaran 2:**
- Nominal bayar: **Rp 3.000.000**

**Hasil Perhitungan:**
```
Total Bayar Baru = Rp 7.000.000 + Rp 3.000.000 = Rp 10.000.000
Sisa Hutang Baru = Rp 3.000.000 - Rp 3.000.000 = Rp 0
Status = 'lunas' (sisa hutang = 0)
```

**Kesimpulan:**
- Total dibayar: **Rp 10.000.000** (100%)
- Sisa hutang: **Rp 0**
- Status: **'lunas'**

---

## 🤝 4. PERHITUNGAN PENITIPAN (CONSIGNMENT)

### **4.1. Komisi Penitipan**

**Formula:**
```
Harga Kesepakatan = Nominal Total Titipan / Qty Total Titipan
Total Kesepakatan = Qty Terjual × Harga Kesepakatan
Komisi = Total Kesepakatan × Komisi Persen / 100
```

**PENTING:** Komisi dihitung dari **harga kesepakatan**, bukan harga jual aktual!

**Contoh Perhitungan:**

**Data Titipan:**
- Qty titipan: **100 kg**
- Harga kesepakatan: **Rp 100.000/kg**
- Nominal total: 100 × Rp 100.000 = **Rp 10.000.000**
- Komisi: **5%**

**Penjualan:**
- Qty terjual: **10 kg**
- Harga jual aktual: **Rp 120.000/kg** (lebih tinggi dari kesepakatan)

**Hasil Perhitungan:**
```
Harga Kesepakatan = Rp 10.000.000 / 100 kg = Rp 100.000/kg
Total Kesepakatan = 10 kg × Rp 100.000 = Rp 1.000.000
Komisi = Rp 1.000.000 × 5% = Rp 50.000

Total Jual Aktual = 10 kg × Rp 120.000 = Rp 1.200.000
Selisih = Rp 1.200.000 - Rp 1.000.000 = Rp 200.000 (keuntungan penerima)
```

**Kesimpulan:**
- Komisi: **Rp 50.000** (dari harga kesepakatan, bukan Rp 60.000)
- Penerima dapat: **Rp 50.000** (komisi) + **Rp 200.000** (selisih) = **Rp 250.000**
- Pengirim dapat: **Rp 1.000.000** (sesuai kesepakatan)

---

### **4.2. Pembayaran Penitipan - Potong Langsung**

**Formula:**
```
Pembayaran ke Pengirim = Total Jual Aktual - Komisi
Penerima Dapat = Komisi (langsung cash)
```

**Contoh Perhitungan:**

**Data:**
- Total jual aktual: **Rp 1.200.000**
- Komisi: **Rp 50.000**
- Tipe komisi: **Potong Langsung**

**Hasil Perhitungan:**
```
Pembayaran ke Pengirim = Rp 1.200.000 - Rp 50.000 = Rp 1.150.000
Penerima Dapat = Rp 50.000 (cash)
```

**Kesimpulan:**
- Pengirim terima: **Rp 1.150.000** (sudah dipotong komisi)
- Penerima terima: **Rp 50.000** (komisi langsung)
- Tidak ada hutang/piutang

---

### **4.3. Pembayaran Penitipan - Bayar Terpisah**

**Formula:**
```
Pembayaran ke Pengirim = Total Jual Aktual (full)
Piutang Komisi = Komisi (dicatat sebagai piutang dari pengirim)
```

**Contoh Perhitungan:**

**Data:**
- Total jual aktual: **Rp 1.200.000**
- Komisi: **Rp 50.000**
- Tipe komisi: **Bayar Terpisah**

**Hasil Perhitungan:**
```
Pembayaran ke Pengirim = Rp 1.200.000 (full)
Piutang Komisi = Rp 50.000 (pengirim harus bayar ke penerima)
```

**Kesimpulan:**
- Pengirim terima: **Rp 1.200.000** (full)
- Penerima dapat: **Rp 0** (sementara)
- Piutang: **Rp 50.000** (pengirim harus bayar komisi ke penerima)

---

## 📊 5. PERHITUNGAN PENJUALAN (SALES)

### **5.1. Nota Penjualan dengan Diskon**

**Formula:**
```
Subtotal Item = Qty × Harga Jual
Subtotal = Σ(Subtotal Item)
Total = Subtotal - Diskon + Pajak
```

**Contoh Perhitungan:**

**Item 1:**
- Produk: Ikan Tuna
- Qty: 20 kg
- Harga jual: Rp 70.000/kg
- Subtotal: 20 × Rp 70.000 = **Rp 1.400.000**

**Item 2:**
- Produk: Ikan Salmon
- Qty: 15 kg
- Harga jual: Rp 90.000/kg
- Subtotal: 15 × Rp 90.000 = **Rp 1.350.000**

**Diskon & Pajak:**
- Diskon: **Rp 200.000** (nominal)
- Pajak: **Rp 0** (tidak ada)

**Hasil Perhitungan:**
```
Subtotal = Rp 1.400.000 + Rp 1.350.000 = Rp 2.750.000
Total = Rp 2.750.000 - Rp 200.000 + Rp 0 = Rp 2.550.000
```

**Kesimpulan:**
- Subtotal: **Rp 2.750.000**
- Diskon: **Rp 200.000**
- Total bayar: **Rp 2.550.000**

---

### **5.2. Profit Calculation (COGS)**

**Formula:**
```
COGS = Σ(Qty Terjual × Harga Beli Rata-rata)
Revenue = Σ(Qty Terjual × Harga Jual)
Gross Profit = Revenue - COGS
Profit Margin = (Gross Profit / Revenue) × 100%
```

**Contoh Perhitungan:**

**Item 1: Ikan Tuna**
- Qty terjual: 20 kg
- Harga jual: Rp 70.000/kg
- Harga beli rata-rata: Rp 53.333/kg

**Item 2: Ikan Salmon**
- Qty terjual: 15 kg
- Harga jual: Rp 90.000/kg
- Harga beli rata-rata: Rp 75.000/kg

**Hasil Perhitungan:**
```
COGS Item 1 = 20 × Rp 53.333 = Rp 1.066.660
COGS Item 2 = 15 × Rp 75.000 = Rp 1.125.000
Total COGS = Rp 1.066.660 + Rp 1.125.000 = Rp 2.191.660

Revenue Item 1 = 20 × Rp 70.000 = Rp 1.400.000
Revenue Item 2 = 15 × Rp 90.000 = Rp 1.350.000
Total Revenue = Rp 1.400.000 + Rp 1.350.000 = Rp 2.750.000

Gross Profit = Rp 2.750.000 - Rp 2.191.660 = Rp 558.340
Profit Margin = (Rp 558.340 / Rp 2.750.000) × 100% = 20.3%
```

**Kesimpulan:**
- Revenue: **Rp 2.750.000**
- COGS: **Rp 2.191.660**
- Gross Profit: **Rp 558.340**
- Profit Margin: **20.3%**

---

## 📈 6. SUMMARY PERHITUNGAN LENGKAP

### **Skenario Lengkap: Dari Stok Masuk sampai Penjualan**

**STEP 1: Stok Masuk Pertama**
```
Qty: 100 kg
Harga beli: Rp 50.000/kg
Nilai stok: Rp 5.000.000
Harga rata-rata: Rp 50.000/kg
```

**STEP 2: Stok Masuk Kedua**
```
Qty masuk: 50 kg
Harga beli: Rp 60.000/kg
Nilai masuk: Rp 3.000.000

Stok baru: 150 kg
Nilai stok baru: Rp 8.000.000
Harga rata-rata baru: Rp 53.333/kg
```

**STEP 3: Penjualan**
```
Qty terjual: 30 kg
Harga jual: Rp 70.000/kg

COGS: 30 × Rp 53.333 = Rp 1.600.000
Revenue: 30 × Rp 70.000 = Rp 2.100.000
Profit: Rp 2.100.000 - Rp 1.600.000 = Rp 500.000

Stok sisa: 120 kg
Nilai stok sisa: Rp 6.400.000
Harga rata-rata: Rp 53.333/kg (tetap)
```

**STEP 4: Retur dari Pembeli**
```
Qty retur: 10 kg
Nilai tambahan: 10 × Rp 53.333 = Rp 533.330

Stok baru: 130 kg
Nilai stok baru: Rp 6.933.330
Harga rata-rata: Rp 53.333/kg (tetap)
```

**STEP 5: Stok Masuk Ketiga**
```
Qty masuk: 80 kg
Harga beli: Rp 55.000/kg
Nilai masuk: Rp 4.400.000

Stok baru: 210 kg
Nilai stok baru: Rp 11.333.330
Harga rata-rata baru: Rp 53.968/kg
```

**FINAL SUMMARY:**
- Total stok: **210 kg**
- Nilai stok: **Rp 11.333.330**
- Harga rata-rata: **Rp 53.968/kg**
- Total penjualan: **30 kg** (Rp 2.100.000)
- Total profit: **Rp 500.000**

---

## ✅ VALIDASI PERHITUNGAN

### **Checklist Validasi:**

1. ✅ **Nilai stok selalu = Qty × Harga rata-rata**
2. ✅ **Harga rata-rata berubah hanya saat stok masuk**
3. ✅ **Harga rata-rata tetap saat stok keluar atau retur**
4. ✅ **Sisa hutang = Nominal - Total bayar**
5. ✅ **Komisi penitipan dari harga kesepakatan, bukan harga jual**
6. ✅ **Retur stok menambah inventory, bukan mengurangi**
7. ✅ **COGS menggunakan harga rata-rata saat barang keluar**

---

## 🔍 TROUBLESHOOTING

### **Jika Nilai Stok Tidak Sesuai:**

1. Periksa apakah menggunakan weighted average
2. Pastikan nilai stok = qty × harga rata-rata
3. Cek apakah ada transaksi yang tidak tercatat

### **Jika Hutang/Piutang Tidak Balance:**

1. Periksa semua pembayaran tercatat di history
2. Pastikan retur dikurangi dari sisa hutang
3. Cek status sesuai dengan sisa hutang

### **Jika Komisi Penitipan Salah:**

1. Pastikan menggunakan harga kesepakatan, bukan harga jual
2. Cek tipe komisi (potong langsung atau bayar terpisah)
3. Validasi perhitungan komisi persen

---

**Last Updated:** 2025-05-20
**Version:** 1.0
**Status:** ✅ Verified & Tested

