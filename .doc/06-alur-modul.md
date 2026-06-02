# 🔄 ALUR MODUL — Peace Seafood Business Flow

---

## 📥 MODUL 1: STOK (Inventory Management)

### **Alur Stok Masuk**

```
┌─────────────────────────────────────────────────────────────┐
│ ADMIN INPUT STOK MASUK                                      │
├─────────────────────────────────────────────────────────────┤
│ 1. Pilih supplier dari daftar                               │
│ 2. Pilih jenis ikan (link to produk)                        │
│ 3. Input qty (kg) & harga beli                              │
│ 4. Auto-calculate: total = qty × harga_beli                │
│ 5. Add catatan (optional)                                   │
│ 6. Save as PENDING                                          │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ CHECKER / ADMIN TIMBANG BARANG                              │
├─────────────────────────────────────────────────────────────┤
│ 1. View pending stok_masuk                                  │
│ 2. Input qty_actual (berat sebenarnya)                      │
│ 3. System auto-calculate: susut = qty_teoritis - qty_actual│
│ 4. Input alasan susut (jika ada)                            │
│ 5. Save timbangan                                           │
│ 6. Status stok_masuk → CONFIRMED                            │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ STOK UPDATE INVENTORY                                       │
├─────────────────────────────────────────────────────────────┤
│ 1. produk.stok_qty += qty_actual                            │
│ 2. produk.stok_value = stok_qty × harga_beli               │
│ 3. produk.harga_beli = harga baru (update)                 │
│ 4. Create history log                                       │
│ 5. Check apakah stok < minimum? → trigger alert            │
└─────────────────────────────────────────────────────────────┘
```

### **Detail per Field**

| Proses | Input | Output | Validasi |
|---|---|---|---|
| **Input Stok** | qty, harga_beli | Pending stok_masuk | qty > 0, harga > 0 |
| **Timbang** | qty_actual, alasan | Confirmed + timbangan | qty_actual > 0 |
| **Update** | - | Inventory updated | Stok > 0 |

---

## 📤 MODUL 2: PENJUALAN (Sales)

### **Alur Buat Nota Penjualan**

```
┌─────────────────────────────────────────────────────────────┐
│ ADMIN CREATE NOTA PENJUALAN                                 │
├─────────────────────────────────────────────────────────────┤
│ 1. Pilih pembeli dari daftar (atau add new)                 │
│ 2. Mulai input items:                                       │
│    a) Pilih produk dari inventory                           │
│    b) Input qty (jangan lebih dari stok)                    │
│    c) Harga jual (default dari current harga, bisa edit)    │
│    d) Auto-calculate: subtotal_item = qty × harga_jual     │
│ 3. Repeat step 2 untuk items lain                           │
│ 4. Add diskon (nominal atau persen)                         │
│ 5. Pajak auto-calculate (dari setting default atau input)   │
│ 6. Auto-calculate total:                                    │
│    Total = Σ(subtotal_item) - diskon + pajak               │
│ 7. Pilih pembayaran (CASH or HUTANG)                        │
│ 8. Add catatan (optional)                                   │
│ 9. Save as DRAFT                                            │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ REVIEW & FINALIZE NOTA                                      │
├─────────────────────────────────────────────────────────────┤
│ 1. Review semua items & total                               │
│ 2. Generate no_nota (auto-sequence: PS-YYMMDD-####)         │
│ 3. Set tanggal_nota = hari ini                              │
│ 4. Print preview                                            │
│ 5. Finalize → Status = FINAL                                │
│ 6. Lock from editing                                        │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ POST NOTA (Update Inventory & Hutang)                       │
├─────────────────────────────────────────────────────────────┤
│ 1. For each nota_detail:                                    │
│    - produk.stok_qty -= qty_terjual                         │
│    - produk.stok_value = stok_qty × harga_beli             │
│ 2. If pembayaran = HUTANG:                                  │
│    - Create hutang_piutang record (jenis='piutang')         │
│    - nominal = total nota                                   │
│    - Set jatuh_tempo (dari setting atau input)              │
│ 3. If pembayaran = CASH:                                    │
│    - No hutang_piutang record                               │
│ 4. Store harga_jual current (untuk cost of goods)           │
│ 5. Check alert: stok < minimum?                             │
│ 6. Create history log                                       │
└─────────────────────────────────────────────────────────────┘
```

### **Edit Nota Rules**
- Hanya bisa edit nota dengan status DRAFT
- Jika sudah FINAL: tidak bisa edit (hanya bisa cancel)
- Cancel nota: Revert semua changes (inventory, hutang)

---

## 🤝 MODUL 3: PENITIPAN (Consignment)

### **Alur Titipan Masuk**

```
┌─────────────────────────────────────────────────────────────┐
│ ADMIN TERIMA TITIPAN MASUK                                  │
├─────────────────────────────────────────────────────────────┤
│ 1. Pilih supplier_pengirim (gudang lain)                    │
│ 2. Input qty titipan (kg)                                   │
│ 3. Input harga_kesepakatan (harga jual yg disepakati)       │
│ 4. Input komisi_persen (% untuk penerima)                   │
│ 5. Save as titipan dengan status = MASUK                    │
└─────────────────────────────────────────────────────────────┘
                            ↓
        ┌─────────────────────────────┐
        │ SIAPA YANG JUAL?             │
        └─────────────────────────────┘
              ↙                     ↘
  ┌──────────────────────┐    ┌──────────────────────┐
  │ SUPPLIER PENGIRIM     │    │ GUDANG PENERIMA      │
  │ YANG JUAL             │    │ YANG JUAL            │
  └──────────────────────┘    └──────────────────────┘
         ↓                            ↓
```

### **Alur Penjualan Titipan (Opsi 1: Supplier Pengirim Jual)**

```
┌─────────────────────────────────────────────────────────────┐
│ SUPPLIER PENGIRIM JUAL TITIPANNYA                           │
├─────────────────────────────────────────────────────────────┤
│ 1. Laporan manual atau input ke sistem:                     │
│    - qty_terjual = berapa banyak yang sudah terjual         │
│    - total_jual = qty_terjual × harga_kesepakatan           │
│    - Calculate komisi = total_jual × komisi_persen          │
│ 2. Input titipan_penjualan                                  │
│    - penjual = 'supplier_pengirim'                          │
│    - status_pembayaran = PENDING (belum bayar ke gudang)    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ SETTLEMENT KOMISI (Bayar ke Gudang Penerima)                │
├─────────────────────────────────────────────────────────────┤
│ Per Setting Komisi Gudang:                                  │
│                                                              │
│ OPSI A: POTONG LANGSUNG                                     │
│ - Supplier bayar: total_jual - komisi                       │
│ - Gudang penerima dapat komisi langsung (cash)              │
│                                                              │
│ OPSI B: BAYAR TERPISAH                                      │
│ - Supplier bayar full: total_jual                           │
│ - Gudang terima piutang komisi (hutang_piutang)             │
│   ~ nominal = komisi                                        │
│   ~ jenis = 'piutang'                                       │
│   ~ id_pembeli = supplier_pengirim                          │
│   ~ status = OPEN (menunggu bayar)                          │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ UPDATE STATUS TITIPAN                                       │
├─────────────────────────────────────────────────────────────┤
│ - If qty_terjual < qty_titipan:                             │
│   → status = DIJUAL_SEBAGIAN                                │
│ - If qty_terjual = qty_titipan:                             │
│   → status = DIJUAL_SEMUA → SELESAI                         │
└─────────────────────────────────────────────────────────────┘
```

### **Alur Penjualan Titipan (Opsi 2: Gudang Penerima Jual)**

```
┌─────────────────────────────────────────────────────────────┐
│ GUDANG PENERIMA JUAL TITIPANNYA (via Sistem)                │
├─────────────────────────────────────────────────────────────┤
│ 1. Create nota penjualan seperti normal                     │
│ 2. Mark item sebagai dari TITIPAN (flag in nota_detail)     │
│ 3. Harga jual = harga_kesepakatan titipan                   │
│ 4. Post nota seperti normal                                 │
│ 5. Create titipan_penjualan record:                         │
│    - qty_terjual = qty dari nota                            │
│    - total_jual = total item (before pajak/diskon)          │
│    - penjual = 'gudang_penerima'                            │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ BAYAR SUPPLIER PENGIRIM                                     │
├─────────────────────────────────────────────────────────────┤
│ Per Setting Komisi:                                         │
│                                                              │
│ OPSI A: POTONG LANGSUNG                                     │
│ - Supplier dapat: total_jual - komisi                       │
│ - Gudang ambil komisi (cash)                                │
│                                                              │
│ OPSI B: BAYAR TERPISAH                                      │
│ - Create hutang_piutang (jenis='hutang')                    │
│ - nominal = total_jual - komisi                             │
│ - bayar_ke = supplier_pengirim                              │
│ - status = OPEN (menunggu pembayaran)                       │
│                                                              │
│ NOTE: Komisi tetap masuk pocket gudang penerima             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 MODUL 4: RETUR (Returns)

### **Alur Retur Stok (Barang Rusak/Return)**

```
┌─────────────────────────────────────────────────────────────┐
│ ADMIN INPUT RETUR STOK                                      │
├─────────────────────────────────────────────────────────────┤
│ 1. Pilih tipe retur = STOK                                  │
│ 2. Dari siapa = SUPPLIER atau PEMBELI                       │
│ 3. Pilih produk & qty retur                                 │
│ 4. WAJIB input alasan (damaged, wrong item, expired, etc)   │
│ 5. Save as PENDING                                          │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ ADMIN/BOZ APPROVE RETUR                                     │
├─────────────────────────────────────────────────────────────┤
│ 1. Review alasan & detail                                   │
│ 2. Approve atau Reject                                      │
│ 3. If APPROVED:                                             │
│    - produk.stok_qty -= qty_retur                           │
│    - produk.stok_value update                               │
│    - Status retur = APPROVED                                │
│ 4. If REJECTED:                                             │
│    - Status retur = REJECTED                                │
│    - Inventory tidak berubah                                │
└─────────────────────────────────────────────────────────────┘
```

### **Alur Retur Piutang (Adjustment Hutang)**

```
┌─────────────────────────────────────────────────────────────┐
│ ADMIN INPUT RETUR PIUTANG                                   │
├─────────────────────────────────────────────────────────────┤
│ 1. Pilih tipe retur = PIUTANG                               │
│ 2. Dari siapa = PEMBELI (klaim diskon/adjustment)           │
│ 3. Pilih nota yang di-retur (link to nota_detail)           │
│ 4. Input nominal retur (berapa yang diklaim)                │
│ 5. WAJIB input alasan (harga jelek, diskon, etc)            │
│ 6. Save as PENDING                                          │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ ADMIN/BOZ APPROVE RETUR PIUTANG                             │
├─────────────────────────────────────────────────────────────┤
│ 1. Review alasan & nominal                                  │
│ 2. Approve atau Reject                                      │
│ 3. If APPROVED:                                             │
│    - hutang_piutang.sisa_hutang -= nominal_retur            │
│    - Update status (open/sebagian/lunas)                    │
│    - Status retur = APPROVED                                │
│ 4. If REJECTED:                                             │
│    - Status retur = REJECTED                                │
│    - hutang_piutang tidak berubah                           │
└─────────────────────────────────────────────────────────────┘
```

---

## 💰 MODUL 5: KEUANGAN (Financial Management)

### **Alur Hutang/Piutang**

```
AUTO-CREATE HUTANG/PIUTANG:
├─ Nota dengan pembayaran = HUTANG
│  → Create hutang_piutang (jenis='piutang', id_pembeli=...)
├─ Penjualan titipan dengan komisi bayar_terpisah
│  → Create hutang_piutang (jenis='hutang', id_supplier=...)
└─ Retur piutang approved
   → Adjust hutang_piutang (kurangi sisa_hutang)

MANUAL CREATE HUTANG/PIUTANG:
├─ Admin input hutang ke supplier (tidak dari stok_masuk)
│  → Create hutang_piutang (jenis='hutang')
└─ Admin input piutang dari pembeli (tidak dari nota)
   → Create hutang_piutang (jenis='piutang')
```

### **Alur Pembayaran Hutang**

```
┌─────────────────────────────────────────────────────────────┐
│ ADMIN INPUT PEMBAYARAN HUTANG                               │
├─────────────────────────────────────────────────────────────┤
│ 1. Pilih hutang_piutang yang belum lunas                    │
│ 2. Input nominal_bayar (≤ sisa_hutang)                      │
│ 3. Input tanggal pembayaran                                 │
│ 4. Input bukti pembayaran (optional file)                   │
│ 5. Save pembayaran                                          │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ UPDATE HUTANG PIUTANG                                       │
├─────────────────────────────────────────────────────────────┤
│ 1. sisa_hutang = sisa_hutang - nominal_bayar                │
│ 2. nominal_bayar_total += nominal_bayar                     │
│ 3. If sisa_hutang = 0: status = LUNAS                       │
│ 4. Else if nominal_bayar_total > 0: status = SEBAGIAN       │
│ 5. Create history log                                       │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 MODUL 6: HARGA (Price Management)

### **Alur Set/Update Harga**

```
┌─────────────────────────────────────────────────────────────┐
│ BOZ / ADMIN SET HARGA BARU                                  │
├─────────────────────────────────────────────────────────────┤
│ 1. Pilih produk                                             │
│ 2. Input harga_beli_baru atau harga_jual_baru              │
│ 3. System:                                                  │
│    - Store harga_lama (from produk)                         │
│    - Create harga_history record                            │
│    - Update produk.harga_beli atau harga_jual               │
│    - Set changed_by = current user                          │
│ 4. History tersimpan dengan timestamp                       │
└─────────────────────────────────────────────────────────────┘
```

### **Harga History untuk Cost of Goods**

```
SAAT CREATE NOTA:
├─ Harga jual dalam nota_detail = current produk.harga_jual
├─ Ini adalah harga saat nota dibuat
└─ History hanya reference, nota gunakan snapshot

SAAT ANALISIS PROFIT:
├─ Cost of goods (COGS) = qty × harga_beli saat barang masuk
├─ Revenue = qty × harga_jual saat nota dibuat
├─ Profit = Revenue - COGS
└─ Bisa trace via harga_history table
```

---

## ✅ Key Business Rules per Modul

| Modul | Rule | Implementasi |
|---|---|---|
| **Stok** | Stok tidak boleh negatif | Server validation |
| **Stok** | Stok update hanya via timbangan | Approved only |
| **Penjualan** | Qty penjualan ≤ stok available | Client + Server check |
| **Penitipan** | Komisi opsi (potong/terpisah) | Per settings |
| **Retur** | WAJIB ada alasan | Mandatory field |
| **Hutang** | Auto-track jatuh_tempo | Alert for admin |
| **Harga** | History auto-create | Event-triggered |

---

**Next**: Baca `07-dashboard.md` untuk UI layout →

