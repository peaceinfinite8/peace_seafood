# 📋 RETUR — Peace Seafood Return Management

---

## 🎯 Retur Overview

Sistem pengembalian untuk 2 kasus:

1. **Retur Stok**: Barang fisik dikembalikan, inventory diatasi
2. **Retur Piutang**: Tidak ada barang dikembalikan, hutang/piutang diatasi

---

## 📊 RETUR STOK FLOW

### **1. CREATION (Checker/Admin)**

```
Input Retur Stok
├─ Select Produk
├─ Enter Qty (tidak bisa melebihi stok tersedia)
├─ Select Reason (Barang rusak / Kadaluarsa / Expired / Tidak sesuai / Lainnya)
├─ Optional: Foto/bukti kerusakan
└─ Submit

Status: PENDING
```

**Database:**

```sql
INSERT INTO retur (
    id_gudang,
    id_produk,
    id_nota,  -- NULL untuk retur stok langsung (bukan dari penjualan)
    tipe,     -- 'stok' atau 'piutang'
    qty,
    alasan,
    status,
    created_by,
    created_at
) VALUES (...)
```

---

### **2. APPROVAL (BOZ)**

```
Review Retur Stok
├─ Lihat detail produk
├─ Lihat quantity & reason
├─ Lihat foto/bukti (jika ada)
└─ [Approve] [Reject]

If Approve:
  Status: APPROVED
  Trigger: Inventory +qty_retur
  Log: Retur stok Ikan A +50kg disetujui Bos

If Reject:
  Status: REJECTED
  Reason: Optional (misal "Barang sebenarnya OK")
  Log: Retur stok ditolak Bos
```

---

### **3. POSTING (Auto)**

```
On Approve:
├─ Update produk stok_qty += qty_retur
├─ Update produk nilai_stok
├─ Create retur_history log
├─ Mark retur status = POSTED
└─ Send notification to submitter

Inventory Effect:
  Before: Ikan A = 100kg (value: 5M)
  Retur: 50kg @ 50k/kg = value 2.5M
  After: Ikan A = 150kg (value: 7.5M)
```

---

## 💰 RETUR PIUTANG FLOW

### **1. CREATION (Admin)**

```
Input Retur Piutang
├─ Select Supplier/Pembeli (hutang/piutang?
├─ Enter Alasan:
│  ├─ Potongan kualitas
│  ├─ Gratis/marketing
│  ├─ Recall produk
│  └─ Lainnya
├─ Enter Nominal (berapa yg diretur)
└─ Submit

Status: PENDING
```

**Database:**

```sql
INSERT INTO retur (
    id_gudang,
    id_supplier,  -- Jika hutang
    id_pembeli,   -- Jika piutang
    tipe,         -- 'piutang'
    nominal,      -- Amount, not qty
    alasan,
    status,
    created_by,
    created_at
) VALUES (...)
```

---

### **2. APPROVAL (BOZ)**

```
Review Retur Piutang
├─ Lihat detail pihak (supplier/pembeli)
├─ Lihat alasan & nominal
└─ [Approve] [Reject]

If Approve:
  Status: APPROVED
  Trigger: Hutang/Piutang adjustment
  Log: Retur piutang dari Supplier A -1M disetujui Bos

If Reject:
  Status: REJECTED
```

---

### **3. POSTING (Auto)**

```
On Approve:
├─ If hutang_retur:
│  └─ Cari hutang_piutang paling lama yg outstanding
│      └─ Kurang sisa_hutang -= nominal_retur
│         (Jika nominal_retur > sisa_hutang, uang kelebihan jadi "kredit" untuk pembelian berikut)
│
├─ If piutang_retur:
│  └─ Cari piutang_piutang paling lama yg outstanding
│      └─ Kurang sisa_piutang -= nominal_retur
│         (Jika nominal_retur > sisa_piutang, uang kelebihan jadi "kredit" untuk pembelian berikut)
│
├─ Create retur_history log
├─ Mark retur status = POSTED
└─ Send notification
```

**Example:**

```
Supplier A hutang:
  - Nota 1: Rp 5.000.000 (30 hari)
  - Nota 2: Rp 3.000.000 (15 hari)
  - Total: Rp 8.000.000

Retur: Rp 2.000.000 (goods return / potongan kualitas)

After retur:
  - Nota 1: Rp 3.000.000 (dikurang Rp 2M)
  - Nota 2: Rp 3.000.000 (unchanged)
  - Total: Rp 6.000.000
```

---

## 📱 RETUR STATUS

```
PENDING → (Approve/Reject) → APPROVED/REJECTED → (Auto) → POSTED

Timeline:
PENDING: Menunggu persetujuan BOZ (average: 24 jam)
APPROVED: BOZ sudah setuju, sistem akan auto-post
REJECTED: BOZ tidak setujui
POSTED: Sudah di-post ke inventory/hutang
```

---

## 🔍 RETUR LIST & DETAIL

### **GET /retur/list**

**Query:**

```
?tipe=stok|piutang
&status=pending|approved|rejected|posted
&id_produk=1  (untuk retur stok)
&page=1
&per_page=10
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "tipe": "stok",
      "produk": {
        "id": 1,
        "nama": "Ikan A",
        "unit": "kg"
      },
      "qty": 50,
      "alasan": "Barang rusak",
      "status": "PENDING",
      "created_by": "Checker A",
      "created_at": "2025-05-17T10:30:00Z"
    }
  ]
}
```

### **GET /retur/:id**

**Response:**

```json
{
  "data": {
    "id": 1,
    "tipe": "stok",
    "produk": {
      "id": 1,
      "nama": "Ikan A",
      "harga_beli": 50000,
      "stok_qty": 100
    },
    "qty": 50,
    "value": 2500000,
    "alasan": "Barang rusak",
    "foto_bukti": "uploads/retur/xxxxx.jpg",
    "status": "PENDING",
    "created_by": "Checker A",
    "created_at": "2025-05-17T10:30:00Z",
    "approved_by": null,
    "approved_at": null
  }
}
```

---

## ✍️ RETUR FORM (Frontend)

### **Retur Stok Form**

```html
<form x-data="{ tipe: 'stok' }" @submit.prevent="submitRetur()">
  <div class="form-group">
    <label>Tipe Retur</label>
    <select x-model="tipe" required>
      <option value="stok">Retur Stok</option>
      <option value="piutang">Retur Piutang</option>
    </select>
  </div>

  <!-- If STOK -->
  <div x-show="tipe === 'stok'">
    <div class="form-group">
      <label>Produk*</label>
      <select name="id_produk" required>
        <option value="">-- Pilih Produk --</option>
        <option value="1">Ikan A (Stok: 100kg)</option>
        <option value="2">Ikan B (Stok: 50kg)</option>
      </select>
    </div>

    <div class="form-group">
      <label>Qty*</label>
      <input type="number" name="qty" min="1" required />
      <span class="help-text">Stok tersedia: 100kg</span>
    </div>

    <div class="form-group">
      <label>Alasan*</label>
      <select name="alasan" required>
        <option value="">-- Pilih Alasan --</option>
        <option value="barang_rusak">Barang Rusak</option>
        <option value="kadaluarsa">Kadaluarsa</option>
        <option value="tidak_sesuai">Tidak Sesuai Pesanan</option>
        <option value="lainnya">Lainnya</option>
      </select>
    </div>

    <div class="form-group">
      <label>Foto Bukti</label>
      <input type="file" name="foto" accept="image/*" />
    </div>
  </div>

  <!-- If PIUTANG -->
  <div x-show="tipe === 'piutang'">
    <div class="form-group">
      <label>Pihak (Hutang/Piutang)*</label>
      <select name="pihak" required>
        <option value="">-- Pilih --</option>
        <option value="supplier">Hutang ke Supplier</option>
        <option value="pembeli">Piutang dari Pembeli</option>
      </select>
    </div>

    <div class="form-group" x-show="pihak === 'supplier'">
      <label>Supplier*</label>
      <select name="id_supplier" required>
        <option value="">-- Pilih Supplier --</option>
        <option value="1">Supplier Laut Jaya (Hutang: 5M)</option>
      </select>
    </div>

    <div class="form-group" x-show="pihak === 'pembeli'">
      <label>Pembeli*</label>
      <select name="id_pembeli" required>
        <option value="">-- Pilih Pembeli --</option>
        <option value="1">PT Restoran Mewah (Piutang: 8M)</option>
      </select>
    </div>

    <div class="form-group">
      <label>Nominal*</label>
      <input type="number" name="nominal" required />
      <span class="help-text">Nominal yang diretur</span>
    </div>

    <div class="form-group">
      <label>Alasan*</label>
      <select name="alasan" required>
        <option value="">-- Pilih Alasan --</option>
        <option value="potongan_kualitas">Potongan Kualitas</option>
        <option value="gratis_marketing">Gratis/Marketing</option>
        <option value="recall_produk">Recall Produk</option>
        <option value="lainnya">Lainnya</option>
      </select>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Submit Retur</button>
    <button type="reset" class="btn btn-secondary">Reset</button>
  </div>
</form>
```

---

## 🔐 RETUR PERMISSIONS

| Action           | BOZ | Admin | Checker |
| ---------------- | --- | ----- | ------- |
| Create (Stok)    | ✓   | ✓     | ✓       |
| Create (Piutang) | ✓   | ✓     | ✗       |
| View List        | ✓   | ✓     | ✓       |
| View Detail      | ✓   | ✓     | ✓       |
| Approve          | ✓   | ✗     | ✗       |
| Reject           | ✓   | ✗     | ✗       |

---

## 🔄 RETUR API ENDPOINTS

### **POST /retur**

Create retur

### **GET /retur/list**

Get retur list

### **GET /retur/:id**

Get retur detail

### **POST /retur/:id/approve**

Approve retur (BOZ only)

```json
Request:
{
  "notes": "Setuju dikembalikan"
}

Response:
{
  "success": true,
  "message": "Retur disetujui dan di-post"
}
```

### **POST /retur/:id/reject**

Reject retur (BOZ only)

```json
Request:
{
  "alasan_reject": "Barang sebenarnya OK, hati-hati next time"
}
```

---

## 📋 RETUR HISTORY

```sql
CREATE TABLE retur_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_retur INT NOT NULL,
    status_lama VARCHAR(50),
    status_baru VARCHAR(50),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_retur) REFERENCES retur(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

---

**Next**: Baca schema JSON files untuk modularized database definitions →
