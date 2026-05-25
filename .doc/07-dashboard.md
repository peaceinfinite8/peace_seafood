# 📊 DASHBOARD — Peace Seafood

---

## 🎯 Dashboard per Role

Setiap role memiliki dashboard yang customized sesuai kebutuhan mereka.

---

## 1️⃣ BOZ (PEMILIK) DASHBOARD

### **Layout**

```
┌─────────────────────────────────────────────────────────────┐
│ NAVBAR: Logo | Dark Mode Toggle | Settings | User Profile   │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ MULTI WAREHOUSE SELECTOR (jika aktif)                       │
│ [Dropdown: Semua Gudang / Gudang A / Gudang B / ...]        │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌───────────────────────────────────────────────────────────────────────┐
│ ROW 1: KEY METRICS (4 Cards)                                          │
├───────────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │
│  │ Total Stok   │  │ Penjualan    │  │ Hutang       │  │ Profit   │ │
│  │ Value        │  │ (Hari/Bulan) │  │ Supplier     │  │ Bulan    │ │
│  │ Rp 250M      │  │ Rp 50M       │  │ Rp 100M      │  │ Rp 15M   │ │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────┘ │
└───────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────┐
│ ROW 2: CHARTS (2 Columns)                                             │
├───────────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────┐  ┌────────────────────────────────┐    │
│  │ Sales Trend (Line)      │  │ Inventory by Type (Pie)        │    │
│  │ Last 7 days             │  │ - Ikan A: 40%, Ikan B: 35%, .. │    │
│  │ [Chart visualization]   │  │ [Chart visualization]          │    │
│  └─────────────────────────┘  └────────────────────────────────┘    │
└───────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────┐
│ ROW 3: ALERTS & TOP LISTS (2 Columns)                                │
├───────────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────┐  ┌────────────────────────────────┐    │
│  │ ALERTS & NOTIFICATIONS  │  │ TOP 5 SUPPLIERS               │    │
│  │ ⚠️ 3x Stok < minimum    │  │ 1. Supplier A - Rp 50M        │    │
│  │ 🔴 Hutang jatuh tempo   │  │ 2. Supplier B - Rp 40M        │    │
│  │ 📋 2x Retur pending     │  │ 3. ...                         │    │
│  │ [See all]               │  │ [View all]                     │    │
│  └─────────────────────────┘  └────────────────────────────────┘    │
└───────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────┐
│ ROW 4: RECENT TRANSACTIONS                                            │
├───────────────────────────────────────────────────────────────────────┤
│ Tabel: Nota | Pembeli | Tanggal | Total | Status                     │
│ PS-250517-0001 | Pembeli A | 17/05/2025 | Rp 5M | Paid                │
│ PS-250517-0002 | Pembeli B | 17/05/2025 | Rp 3M | Hutang              │
│ [View all]                                                             │
└───────────────────────────────────────────────────────────────────────┘
```

### **Widgets Detail**

**Key Metrics (Top Cards):**
- **Total Stok Value** = SUM(produk.stok_value) per warehouse/all
- **Penjualan Hari Ini** = SUM(nota.total) where tanggal = today
- **Penjualan Bulan Ini** = SUM(nota.total) where bulan = current
- **Hutang Supplier** = SUM(hutang_piutang.sisa_hutang) jenis='hutang'
- **Piutang Pembeli** = SUM(hutang_piutang.sisa_hutang) jenis='piutang'
- **Profit Bulan Ini** = Revenue - COGS (calculated)

**Charts:**
- **Sales Trend** (Line Chart): Last 7 days sales
- **Inventory by Type** (Pie Chart): % stok per jenis ikan
- **Profit Trend** (Bar Chart): Profit per minggu

**Alerts:**
- Stok < minimum (link to inventory module)
- Hutang jatuh tempo (link to keuangan module)
- Retur pending (link to retur module)
- Error atau maintenance notification

**Top Lists:**
- Top 5 suppliers by total hutang
- Top 5 buyers by total penjualan
- Bottom 5 produk by stok (critical stok)

---

## 2️⃣ ADMIN (OPERATOR) DASHBOARD

### **Layout**

```
┌─────────────────────────────────────────────────────────────┐
│ NAVBAR: Logo | Dark Mode Toggle | Logout | User Profile     │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ WAREHOUSE INFO (jika multi warehouse)                       │
│ Assigned: Gudang A | [Switch Gudang] (jika >1 gudang)      │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌───────────────────────────────────────────────────────────────────────┐
│ ROW 1: TODAY METRICS (4 Cards)                                        │
├───────────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │
│  │ Stok Hari   │  │ Nota Hari    │  │ Stok < Min   │  │ Hutang   │ │
│  │ Ini Masuk   │  │ Ini Dibuat   │  │ (Alert)      │  │ Supplier │ │
│  │ 100 kg      │  │ 5 nota       │  │ 3 items      │  │ Rp 50M   │ │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────┘ │
└───────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────┐
│ ROW 2: ACTION BUTTONS                                                 │
├───────────────────────────────────────────────────────────────────────┤
│  [+ Input Stok] [+ Buat Nota] [+ Retur] [+ Input Hutang] [Laporan]  │
└───────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────┐
│ ROW 3: CHARTS (2 Columns)                                             │
├───────────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────┐  ┌────────────────────────────────┐    │
│  │ Daily Sales (Today)     │  │ Stok by Type                   │    │
│  │ [Bar chart - by item]   │  │ [Horizontal bar - qty kg]      │    │
│  └─────────────────────────┘  └────────────────────────────────┘    │
└───────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────┐
│ ROW 4: QUICK ALERTS & TODO                                            │
├───────────────────────────────────────────────────────────────────────┤
│  ⚠️ STOK MENIPIS:                  📋 PENDING:                        │
│  - Ikan A: 5 kg (min: 20 kg)        - 2x Stok masuk pending timbang  │
│  - Ikan B: 10 kg (min: 50 kg)       - 1x Retur pending approval      │
│  [Details]                           [Details]                        │
└───────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────┐
│ ROW 5: RECENT TRANSACTIONS (Today)                                    │
├───────────────────────────────────────────────────────────────────────┤
│ Tabel: Tipe | Item | Qty | Pembeli/Supplier | Waktu                 │
│ STOK | Ikan A | +100 kg | Supplier X | 08:30                         │
│ NOTA | Ikan A | -50 kg | Pembeli Y | 10:15                           │
│ [View full history]                                                    │
└───────────────────────────────────────────────────────────────────────┘
```

### **Key Elements**

- **Stok Masuk Hari Ini**: Total qty stok yang di-input hari ini
- **Nota Hari Ini**: Jumlah nota yang dibuat hari ini + total nilai
- **Stok < Minimum**: Produk yang mencapai alert threshold
- **Hutang Supplier**: Top hutang yang belum dibayar
- **Action Buttons**: Quick access ke modul utama
- **Quick Alerts**: Critical items yang perlu perhatian
- **Recent Transactions**: Log transaksi real-time hari ini

---

## 3️⃣ CHECKER (PENGAWAS STOK) DASHBOARD

### **Layout**

```
┌─────────────────────────────────────────────────────────────┐
│ NAVBAR: Logo | Dark Mode Toggle | Logout | User Profile     │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ WAREHOUSE INFO                                               │
│ Assigned: Gudang A                                           │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌───────────────────────────────────────────────────────────────────────┐
│ ROW 1: STOK STATUS (4 Cards)                                          │
├───────────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │
│  │ Total Stok   │  │ Items        │  │ Stok < Min   │  │ Susut    │ │
│  │ (kg)         │  │ Terdaftar    │  │              │  │ Hari Ini │ │
│  │ 5000 kg      │  │ 25 items     │  │ 3 items      │  │ 10 kg    │ │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────┘ │
└───────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────┐
│ ROW 2: ACTION BUTTON                                                  │
├───────────────────────────────────────────────────────────────────────┤
│  [+ Input Timbangan] [Laporan Stok]                                   │
└───────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────┐
│ ROW 3: CHARTS (2 Columns)                                             │
├───────────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────┐  ┌────────────────────────────────┐    │
│  │ Stok Status             │  │ Susut Trend (7 days)           │    │
│  │ (Aman/Warning/Critical) │  │ [Line chart]                   │    │
│  │ [Color-coded bar]       │  │                                │    │
│  └─────────────────────────┘  └────────────────────────────────┘    │
└───────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────┐
│ ROW 4: STOK MONITORING TABLE                                          │
├───────────────────────────────────────────────────────────────────────┤
│ Item | Current Qty | Min | Status | Last Updated | Action             │
│ Ikan A | 50 kg | 20 kg | AMAN | 17/05 10:30 | [Detail]              │
│ Ikan B | 15 kg | 50 kg | ⚠️ WARNING | 17/05 10:00 | [Detail]        │
│ Ikan C | 5 kg | 30 kg | 🔴 CRITICAL | 17/05 08:00 | [Detail]       │
│ [Paginated]                                                            │
└───────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────┐
│ ROW 5: PENDING TIMBANGAN                                              │
├───────────────────────────────────────────────────────────────────────┤
│ Stok Masuk # | Item | Qty | Supplier | Waktu | [Timbang]             │
│ SM-001 | Ikan A | 100 kg | Supplier X | 2 jam lalu | [Input]         │
│ [Details]                                                              │
└───────────────────────────────────────────────────────────────────────┘
```

### **Key Elements**

- **Total Stok**: Sum of all produk qty
- **Items Terdaftar**: Total jenis ikan active
- **Stok < Minimum**: Alert indicator
- **Susut Hari Ini**: Total susut timbang hari ini
- **Stok Status Chart**: Green (aman) / Yellow (warning) / Red (critical)
- **Susut Trend**: 7-day trend line
- **Stok Monitoring Table**: Semua item dengan status & color-coding
- **Pending Timbangan**: Antrian stok masuk yang belum ditimbang

---

## 🎨 Common Elements (All Dashboards)

### **Navbar**
```
┌─────────────────────────────────────────────────────────────┐
│ LOGO | Menu Toggle | [Breadcrumb] | Dark Mode | Settings ⚙️ │
│                                            | Profile 👤 |    │
└─────────────────────────────────────────────────────────────┘
```

### **Color Coding**
- **🟢 Green (AMAN)**: Stok > minimum, No alerts
- **🟡 Yellow (WARNING)**: Stok < 1.5x minimum
- **🔴 Red (CRITICAL)**: Stok < minimum, Urgent action needed
- **⚪ Gray**: Inactive or archived

### **Responsive Design**
- **Desktop**: Full 4-column layout
- **Tablet**: 2-column layout
- **Mobile**: Single column, collapsible sections

### **Real-time Updates**
- Charts update every 5 minutes (via AJAX/Axios)
- Alerts push notification in-app
- Data refresh indicator ("Last updated: 2 mins ago")

---

**Next**: Baca `08-settings.md` untuk konfigurasi admin →

