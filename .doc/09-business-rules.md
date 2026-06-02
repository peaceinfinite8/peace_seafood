# 📋 BUSINESS RULES — Peace Seafood

---

## 🎯 Core Business Rules

Aturan-aturan bisnis yang wajib diimplementasikan dalam aplikasi.

---

## 📦 STOK (Inventory Rules)

### **Rule 1: Stok Tidak Boleh Negatif**
- Server WAJIB validate: qty penjualan ≤ stok current
- Client validate: disable button jika qty > stok
- Error message: "Stok tidak mencukupi! Stok tersedia: XX kg"

### **Rule 2: Update Stok Hanya Via Timbangan**
- Stok masuk hanya jadi CONFIRMED setelah ditimbang
- Admin tidak bisa langsung confirm stok_masuk
- Checker yang timbang, baru admin lihat di inventory

### **Rule 3: Stok Minimum Alert**
- Trigger: qty_current < threshold
- Show alert di dashboard untuk Admin & Checker
- Link ke inventory module untuk quick action

### **Rule 4: Harga Beli Auto-Update**
- Saat stok masuk confirmed: produk.harga_beli = harga stok_masuk terbaru
- History harga tercatat di harga_history
- COGS calculation gunakan harga_beli saat barang masuk

### **Rule 5: Stok Value Calculation**
- stok_value = qty × harga_beli (inventory value)
- Update otomatis saat ada perubahan qty atau harga_beli

---

## 💰 PENJUALAN (Sales Rules)

### **Rule 1: Nota Number Auto-Generate**
- Format: PS-YYMMDD-XXXX (Peace Seafood - Date - Sequence)
- Example: PS-250517-0001, PS-250517-0002
- Per gudang, per hari

### **Rule 2: Harga Jual Snapshot**
- Saat create nota: nota_detail.harga_jual = current produk.harga_jual
- Jika harga berubah setelahnya, nota_detail harga tetap (locked)
- Untuk COGS calculation yang akurat

### **Rule 3: Diskon & Pajak Calculation**
```
subtotal_items = Σ(qty × harga_jual) per item
subtotal = Σ subtotal_items

total = subtotal - diskon_nominal + pajak
  OR
total = subtotal × (1 - diskon_persen/100) + pajak
```

### **Rule 4: Pembayaran & Hutang**
- Jika pembayaran = CASH:
  - status pembayaran = PAID
  - Tidak create hutang_piutang record
  
- Jika pembayaran = HUTANG:
  - Create hutang_piutang (jenis='piutang', id_pembeli=X)
  - nominal = total nota
  - Set jatuh_tempo = tanggal_nota + jatuh_tempo_days
  - Status piutang = OPEN

### **Rule 5: Cancel Nota**
- Hanya bisa cancel nota dengan status DRAFT atau FINAL
- Saat cancel:
  - inventory update revert (qty kembali masuk)
  - hutang_piutang deleted atau marked cancelled
  - Status nota = CANCELLED
  - Create audit log

### **Rule 6: Stok Alert Saat Penjualan**
- Setelah post nota, check: stok_current < threshold?
- If yes: trigger alert untuk admin

---

## 🤝 PENITIPAN (Consignment Rules)

### **Rule 1: Titipan Status Flow**
```
MASUK → DIJUAL_SEBAGIAN → DIJUAL_SEMUA → SELESAI
```

### **Rule 2: Komisi Calculation**
```
OPSI A: POTONG LANGSUNG
- Pembayaran ke supplier: total_jual - komisi
- Gudang dapat: komisi (langsung cash)

OPSI B: BAYAR TERPISAH
- Pembayaran ke supplier: total_jual (full)
- Komisi dicatat sebagai hutang ke supplier (defer payment)
```

### **Rule 3: Komisi Formula**
- komisi = total_jual × komisi_persen / 100
- Contoh: Total Rp 1.000.000, komisi 5% → Rp 50.000

### **Rule 4: Titipan Settlement**
- Bisa generate settlement report per periode
- Report berisi: qty masuk, qty terjual, total jual, komisi, pembayaran
- Status: Open / Settled

### **Rule 5: Titipan Quantity Tracking**
- qty_titipan = jumlah awal yang dititipkan
- qty_terjual = cumulative quantity sold
- qty_sisa = qty_titipan - qty_terjual
- Jika qty_sisa > 0 tetap OPEN status

---

## 🔄 RETUR (Return Rules)

### **Rule 1: Retur Stok**
- Kurangi inventory saat APPROVED
- Alasan WAJIB input (dropdown atau text)
- Bisa partial atau full return

### **Rule 2: Retur Piutang**
- Adjust hutang_piutang saat APPROVED
- Nominal retur = kurangi dari sisa_hutang
- Update status piutang (open/sebagian/lunas)

### **Rule 3: Retur Approval Workflow**
```
PENDING → (APPROVE / REJECT) → (APPROVED / REJECTED)
```

### **Rule 4: Retur Reason Required**
- Alasan retur WAJIB input (tidak boleh kosong)
- Dropdown: Damaged / Wrong Item / Quality Issue / Client Request / Other
- If Other: text input mandatory

### **Rule 5: Retur Timestamp**
- Catat waktu retur di-create, di-approve, dll
- created_by = user yang create
- Untuk audit trail

---

## 💳 HUTANG/PIUTANG (Financial Rules)

### **Rule 1: Hutang Jatuh Tempo**
- Jatuh tempo = tanggal nota + jatuh_tempo_days (dari setting)
- Alert trigger 3 hari sebelum jatuh tempo
- Status: OPEN → SEBAGIAN (ada pembayaran) → LUNAS

### **Rule 2: Pembayaran Partial**
```
Contoh: Hutang Rp 1.000.000
Pembayaran 1: Rp 300.000 → sisa Rp 700.000 (status: SEBAGIAN)
Pembayaran 2: Rp 400.000 → sisa Rp 300.000 (status: SEBAGIAN)
Pembayaran 3: Rp 300.000 → sisa Rp 0 (status: LUNAS)
```

### **Rule 3: Hutang History**
- Setiap pembayaran tercatat dengan:
  - Tanggal pembayaran
  - Nominal bayar
  - Bukti (file upload optional)
  - User yang input
- Bisa generate laporan aging

### **Rule 4: Multiple Hutang per Supplier**
- Supplier bisa punya banyak hutang record
- Report: Aggregated by supplier
- Aging: Sort by jatuh_tempo

---

## 🏷️ HARGA (Price Rules)

### **Rule 1: Harga Dinamis**
- Harga beli & jual bisa berubah kapan saja
- Setiap perubahan tercatat di harga_history
- Nota menggunakan harga snapshot, bukan current harga

### **Rule 2: Harga History Tracking**
```
tabel harga_history:
- id_produk
- harga_lama
- harga_baru
- tipe (beli/jual)
- changed_by (user)
- created_at
```

### **Rule 3: Harga Change Permission**
- Settable di admin panel: siapa bisa ubah harga
- Default: BOZ ONLY
- Change log untuk audit

### **Rule 4: COGS Calculation**
```
Profit = Revenue - COGS

Revenue = nota_detail.qty × nota_detail.harga_jual
COGS = stok_masuk.qty × stok_masuk.harga_beli (saat masuk)

Note: Gunakan harga saat barang masuk, bukan current harga
```

---

## 📊 LAPORAN (Report Rules)

### **Rule 1: Periode Filter**
- Laporan harus bisa filter by:
  - Hari (today)
  - Range tanggal
  - Bulan & Tahun
  - Tahun

### **Rule 2: Data Agregation**
- Laporan aggregasi data, bukan raw transactions
- Example: Total penjualan per hari, per pembeli, per produk

### **Rule 3: Profit Calculation dalam Laporan**
```
Profit = Revenue - COGS - Operasional

Revenue = Σ nota.total
COGS = Σ (qty_terjual × harga_beli_saat_masuk)
Operasional = Σ biaya_operasional
```

### **Rule 4: Export Format**
- PDF: Formatted, printable, dengan logo & watermark
- Excel: Raw data + formulas untuk further analysis
- Tabel include: Header (gudang, periode), Data, Footer (total)

---

## 🔐 UMUM (General Rules)

### **Rule 1: Audit Trail**
- Setiap transaksi catat: user, waktu, action
- Delete tidak ada, hanya mark as cancelled/archived
- Report perubahan untuk audit

### **Rule 2: Data Consistency**
- Semua query gunakan prepared statements (prevent SQL injection)
- Transaction handling untuk multi-step operations
- Rollback jika ada error tengah jalan

### **Rule 3: Validation Layer**
- Client-side: Real-time validation UX
- Server-side: Strict validation security
- Error message: User-friendly (tidak technical)

### **Rule 4: Timezone Consistency**
- Semua timestamp dalam WIB (Asia/Jakarta)
- Database store in UTC, convert saat display
- Report gunakan WIB time

### **Rule 5: Currency Formatting**
- Display: Rp 1.000.000 (dot separator)
- Store: numeric (no formatting)
- Calculate: numeric only

---

## ✅ Validation Rules per Form

| Form | Field | Validation | Error Message |
|---|---|---|---|
| **Nota** | pembeli | Required | Pembeli wajib dipilih |
| **Nota** | items | Min 1 | Minimal 1 item |
| **Nota** | qty | > 0, ≤ stok | Qty invalid atau stok kurang |
| **Nota** | total | > 0 | Total harus > 0 |
| **Retur** | alasan | Required | Alasan retur wajib diisi |
| **Retur** | qty | > 0, ≤ outstanding | Qty invalid |
| **Stok Masuk** | qty | > 0 | Qty wajib > 0 |
| **Stok Masuk** | harga | > 0 | Harga wajib > 0 |
| **Hutang** | nominal | > 0 | Nominal wajib > 0 |
| **Pembayaran** | nominal | > 0, ≤ sisa | Pembayaran invalid |

---

**Next**: Baca `10-ui-guidelines.md` untuk design system →

