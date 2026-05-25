# 👥 USER ROLES & PERMISSIONS

---

## 🎭 Role Structure

Peace Seafood memiliki 3 role utama dengan permission berbeda berdasarkan level akses.

---

## 1️⃣ BOZ (Pemilik/Owner)

### **Deskripsi**
- Pemilik gudang yang memiliki full access ke sistem
- Bisa setup konfigurasi gudang
- Bisa lihat laporan & analytics semua modul
- Bisa manage admin & checker
- Bisa akses settings lengkap

### **Access Menu**

| Menu | Access |
|---|---|
| Dashboard | ✅ Full (semua gudang jika multi-warehouse aktif) |
| Stok | ✅ View only |
| Penjualan | ✅ View only |
| Penitipan | ✅ View only |
| Retur | ✅ View only |
| Keuangan | ✅ View only |
| Laporan | ✅ Full |
| Master Data | ✅ View only |
| Settings | ✅ Full (Admin Panel) |
| User Management | ✅ Full (create, edit, delete admin & checker) |

### **Permission Detail**

**Stok Modul:**
- Lihat inventory current
- Lihat stok history (read-only)
- Lihat laporan stok by periode
- Export stok report

**Penjualan Modul:**
- Lihat semua nota
- Lihat customer & sales trend
- Lihat profit analysis
- Export sales report
- Tidak bisa create/edit nota (role admin)

**Keuangan Modul:**
- Lihat hutang/piutang lengkap
- Lihat aging analysis
- Lihat cash flow
- Export financial report
- Tidak bisa input pembayaran hutang (role admin)

**Settings:**
- Konfigurasi multi warehouse (aktif/nonaktif)
- Set stok minimum threshold
- Konfigurasi komisi penitipan
- Pilih notifikasi apa saja yang aktif
- Export database backup
- Konfigurasi onboarding wizard
- Manage user (create/edit/delete)
- Konfigurasi harga (frozen/dynamic)
- Set pajak default

---

## 2️⃣ ADMIN (Operator)

### **Deskripsi**
- Operator gudang yang menginput transaksi harian
- Input stok masuk, penjualan, hutang
- Di-assign ke gudang spesifik (jika multi-warehouse aktif)
- Tidak bisa akses settings

### **Access Menu**

| Menu | Access |
|---|---|
| Dashboard | ✅ Gudang assigned only |
| Stok | ✅ Create/Read/Update |
| Penjualan | ✅ Create/Read/Update (own transaksi) |
| Penitipan | ✅ Create/Read/Update (own transaksi) |
| Retur | ✅ Create/Read (own transaksi) |
| Keuangan | ✅ Create hutang/piutang, lihat laporan |
| Laporan | ✅ View laporan (gudang assigned) |
| Master Data | ✅ View only |
| Settings | ❌ No access |
| User Management | ❌ No access |

### **Permission Detail**

**Stok Modul:**
- Input stok masuk (jenis ikan, qty, harga)
- Edit stok masuk (belum approve)
- Lihat inventory current (gudang assigned)
- Lihat stok history
- Tidak bisa delete stok (hanya mark as cancelled)

**Penjualan Modul:**
- Create nota penjualan
- Edit nota (belum final)
- Input pembayaran (langsung/hutang)
- Print nota
- Generate nota PDF
- Tidak bisa delete nota (hanya cancel)
- Tidak bisa edit nota yang sudah locked

**Penitipan Modul:**
- Input titipan masuk
- Input penjualan titipan (jika assigned sebagai penjual)
- Lihat titipan status
- Generate settlement report
- Tidak bisa delete titipan

**Retur Modul:**
- Create retur (stok & piutang)
- Lihat retur history
- Tidak bisa delete retur (hanya cancel jika belum processing)

**Keuangan Modul:**
- Input hutang/piutang baru
- Input pembayaran hutang/piutang
- Lihat detail hutang/piutang (gudang assigned)
- Tidak bisa edit atau delete transaksi (lihat retur modul untuk retur piutang)

**Laporan:**
- Lihat laporan (gudang assigned only)
- Export laporan (PDF/Excel)
- Filter by periode

---

## 3️⃣ CHECKER (Pengawas Stok)

### **Deskripsi**
- Petugas pengawas stok yang melakukan verifikasi
- Input timbangan & susut
- Cek stok accuracy
- Di-assign ke gudang spesifik
- Akses limited hanya untuk stok checking

### **Access Menu**

| Menu | Access |
|---|---|
| Dashboard | ✅ Stok dashboard only |
| Stok | ✅ View + Input timbangan |
| Penjualan | ❌ No access |
| Penitipan | ❌ No access |
| Retur | ❌ No access |
| Keuangan | ❌ No access |
| Laporan | ✅ Stok report only |
| Master Data | ✅ View only |
| Settings | ❌ No access |
| User Management | ❌ No access |

### **Permission Detail**

**Stok Modul:**
- Lihat inventory current (gudang assigned)
- Input timbangan (berat actual, alasan selisih)
- Lihat stok history
- Lihat susut report
- Report anomali (stok tidak match)
- Tidak bisa input stok masuk
- Tidak bisa adjust stok manual (hanya admin)

**Laporan:**
- Lihat stok report (gudang assigned only)
- Lihat susut trend
- Export stok report (PDF/Excel)

---

## 🏢 Multi Warehouse Impact

### **Jika Multi Warehouse AKTIF**

**BOZ:**
- Multi-warehouse selector di dashboard
- Bisa lihat semua gudang data sekaligus (optional aggregated view)
- Bisa switch per gudang untuk detail

**Admin:**
- Assigned ke 1 atau more gudang
- Hanya lihat data gudang yang di-assign
- Dropdown warehouse selector untuk switch (kalau assigned >1)
- Nota/transaksi dibuat per warehouse

**Checker:**
- Assigned ke 1 atau more gudang
- Hanya lihat stok gudang yang di-assign
- Dropdown warehouse selector untuk switch (kalau assigned >1)

### **Jika Multi Warehouse NONAKTIF**

- Single warehouse only
- Admin & Checker tidak perlu warehouse selector
- BOZ tetap lihat semua (karena cuma 1 warehouse)
- Database tetap punya field `id_warehouse` (untuk future support)

---

## 🔑 Permission Matrix

```
┌─────────────────────┬──────────┬───────┬─────────┐
│ Feature             │ BOZ      │ ADMIN │ CHECKER │
├─────────────────────┼──────────┼───────┼─────────┤
│ Dashboard           │ ✅ FULL  │ ✅ R  │ ✅ STOK │
│ Stok Input          │ ❌       │ ✅ CRU│ ✅ TMBG │
│ Stok View           │ ✅ R     │ ✅ R  │ ✅ R    │
│ Penjualan Input     │ ❌       │ ✅ CR │ ❌      │
│ Penjualan View      │ ✅ R     │ ✅ R  │ ❌      │
│ Penitipan Input     │ ❌       │ ✅ CR │ ❌      │
│ Penitipan View      │ ✅ R     │ ✅ R  │ ❌      │
│ Retur Input         │ ❌       │ ✅ C  │ ❌      │
│ Hutang/Piutang      │ ✅ R     │ ✅ C  │ ❌      │
│ Laporan             │ ✅ FULL  │ ✅ R  │ ✅ STOK │
│ Settings            │ ✅ FULL  │ ❌    │ ❌      │
│ User Management     │ ✅ FULL  │ ❌    │ ❌      │
└─────────────────────┴──────────┴───────┴─────────┘

Legend:
✅ FULL = Full access (Read/Create/Update/Delete)
✅ CRU  = Create/Read/Update (tidak delete)
✅ CR   = Create/Read only
✅ C    = Create only
✅ R    = Read only
✅ TMBG = Timbangan input only
✅ STOK = Stok dashboard & report only
❌      = No access
```

---

## 🚪 Database Implementation

### **users table**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('bos', 'admin', 'checker'),
    id_gudang INT NULL,  -- NULL untuk BOZ, set untuk ADMIN/CHECKER
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Multi warehouse:
-- BOZ: id_gudang = NULL (lihat semua)
-- ADMIN: id_gudang = specific warehouse
-- CHECKER: id_gudang = specific warehouse
```

### **Permission Check di Backend**

```php
// Middleware check
function checkPermission($role, $action, $module) {
    $permissions = [
        'bos' => ['stok.view', 'penjualan.view', 'laporan.*', 'settings.*'],
        'admin' => ['stok.create', 'penjualan.create', 'hutang.create'],
        'checker' => ['stok.timbang', 'stok.view']
    ];
    
    return in_array("$module.$action", $permissions[$role] ?? []);
}

// Check warehouse access
function checkWarehouseAccess($userId, $warehouseId) {
    $user = User::find($userId);
    
    if ($user->role === 'bos') return true; // BOZ lihat semua
    if ($user->id_warehouse === $warehouseId) return true;
    
    return false;
}
```

---

## 📱 Frontend Implementation

### **Conditional Rendering**
```javascript
// Alpine.js check
x-show="currentUser.role === 'admin'"
x-show="currentUser.role !== 'checker'"
x-show="['bos', 'admin'].includes(currentUser.role)"

// Disable button
@click="userRole === 'checker' ? null : createNota()"
:disabled="userRole === 'checker'"
```

### **Warehouse Selector**
```html
<!-- Show untuk BOZ & ADMIN dengan >1 warehouse -->
<select x-show="userRole === 'bos' || multiWarehouse" 
        x-model="currentWarehouse"
        @change="fetchDashboard()">
    <option value="">-- Semua Gudang --</option>
    <template x-for="wh in warehouses">
        <option :value="wh.id" x-text="wh.name"></option>
    </template>
</select>
```

---

## ✅ Testing Checklist

- [ ] BOZ bisa lihat semua data & settings
- [ ] ADMIN tidak bisa akses settings
- [ ] CHECKER hanya lihat stok dashboard
- [ ] Multi warehouse selector work untuk BOZ
- [ ] ADMIN hanya lihat assigned warehouse
- [ ] CHECKER hanya lihat assigned warehouse
- [ ] Edit button hide untuk read-only
- [ ] Delete button hide untuk role tanpa delete permission
- [ ] Create button show hanya untuk authorized role

---

**Next**: Baca `02-tech-stack.md` untuk setup teknis →

