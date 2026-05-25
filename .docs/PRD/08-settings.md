# ⚙️ SETTINGS — Peace Seafood Admin Panel

---

## 📋 Settings Overview

Settings di admin panel memungkinkan BOZ mengkonfigurasi berbagai aspek aplikasi tanpa perlu coding. Semua setting tersimpan di tabel `settings`.

---

## 🔧 Kategori Settings

### **1. GENERAL SETTINGS**

#### **Informasi Gudang**

```
- Nama Gudang
- Alamat
- Kota
- Nomor Telepon
- Email
- Logo Upload
```

#### **Default Values**

```
- Mata Uang (default: IDR)
- Format Tanggal (default: DD/MM/YYYY)
- Format Angka (default: 1.000.000)
- Timezone (default: Asia/Jakarta)
- Pajak Default (%)
- Biaya Operasional Default
```

---

### **2. MULTI WAREHOUSE (Opsi)**

#### **Pengaturan Multi Gudang**

```
Setting: multi_warehouse_aktif
┌─────────────────────────────────────────────┐
│ ✅ Aktifkan Multi Warehouse                  │
│ [ENABLE / DISABLE]                           │
│                                              │
│ Jika diaktifkan:                             │
│ - BOZ bisa lihat semua gudang di dashboard  │
│ - Admin & Checker di-assign ke gudang       │
│ - Switch warehouse selector di navbar        │
│                                              │
│ Default: NONAKTIF (single warehouse)         │
└─────────────────────────────────────────────┘
```

#### **Warehouse List (jika multi aktif)**

```
Tabel: Nama Gudang | Alamat | Admin Assigned | Status

[+] Add Warehouse
- Bikin warehouse baru
- Assign admin ke warehouse
- Set initial products

Edit / Delete warehouse
- Hanya bisa edit alamat, telpon, dll
- Tidak bisa delete jika ada transaksi
```

---

### **3. INVENTORY ALERTS**

#### **Stok Minimum Threshold**

```
Setting: stok_minimum_threshold (global)
┌─────────────────────────────────────────────┐
│ Default Stok Minimum (kg): [50] kg          │
│ * Bisa override per produk di Master Data   │
│                                              │
│ Notifikasi Trigger:                         │
│ - Alert di dashboard                        │
│ - In-app notification untuk Admin            │
│ - Export report untuk Checker                │
└─────────────────────────────────────────────┘
```

#### **Susut Alert**

```
Setting: susut_alert_threshold (%)
┌─────────────────────────────────────────────┐
│ Alert jika susut > [5] % dari qty teoritis  │
│                                              │
│ Auto-generated alert untuk Checker:          │
│ "Susut 7% - Investigate reason"              │
└─────────────────────────────────────────────┘
```

---

### **4. NOTIFIKASI IN-APP**

#### **Notification Preferences**

```
Setting: notifikasi_stok_minimum (active/inactive)
┌─────────────────────────────────────────────┐
│ ✅ Notifikasi Stok Menipis                  │
│ Trigger: Ketika stok < threshold             │
│ Penerima: Admin & Checker                    │
└─────────────────────────────────────────────┘

Setting: notifikasi_hutang_jatuh_tempo (active/inactive)
┌─────────────────────────────────────────────┐
│ ✅ Notifikasi Hutang Jatuh Tempo            │
│ Trigger: 3 hari sebelum jatuh_tempo         │
│ Penerima: Admin & BOZ                        │
└─────────────────────────────────────────────┘

Setting: notifikasi_retur_pending (active/inactive)
┌─────────────────────────────────────────────┐
│ ✅ Notifikasi Retur Pending Approval        │
│ Trigger: Ada retur dengan status = pending  │
│ Penerima: BOZ                                │
└─────────────────────────────────────────────┘

Setting: notifikasi_error_system (active/inactive)
┌─────────────────────────────────────────────┐
│ ✅ Notifikasi Error System                  │
│ Trigger: Ketika ada error di transaction    │
│ Penerima: BOZ & Admin                        │
└─────────────────────────────────────────────┘
```

---

### **5. KOMISI PENITIPAN**

#### **Metode Komisi**

```
Setting: komisi_penitipan_tipe
┌─────────────────────────────────────────────┐
│ Bagaimana menghitung komisi penitipan?      │
│                                              │
│ ⭕ POTONG LANGSUNG (default)                │
│    - Supplier bayar: total - komisi         │
│    - Gudang dapat komisi langsung (cash)    │
│                                              │
│ ⭕ BAYAR TERPISAH                           │
│    - Supplier bayar full amount              │
│    - Gudang can claim komisi as hutang      │
│                                              │
│ [PILIH OPSI]                                 │
└─────────────────────────────────────────────┘
```

#### **Komisi Percentage**

```
Setting: komisi_penitipan_persen
┌─────────────────────────────────────────────┐
│ Komisi Default untuk Penitipan: [5] %       │
│ * Bisa override per transaksi titipan       │
│                                              │
│ Contoh:                                      │
│ Total penjualan: Rp 1.000.000                │
│ Komisi (5%): Rp 50.000                       │
│ Supplier dapat: Rp 950.000                   │
└─────────────────────────────────────────────┘
```

---

### **6. PRICING & FINANCIAL**

#### **Harga Pengaturan**

```
Setting: harga_locked_untuk_perubahan
┌─────────────────────────────────────────────┐
│ Siapa bisa ubah harga?                      │
│ ⭕ BOZ ONLY (default)                        │
│ ⭕ BOZ & ADMIN                              │
│ ⭕ SEMUA USER (risky)                        │
│                                              │
│ [PILIH OPSI]                                 │
└─────────────────────────────────────────────┘
```

#### **Pajak Default**

```
Setting: pajak_default_persen
┌─────────────────────────────────────────────┐
│ Pajak untuk Nota Penjualan: [10] %          │
│ * Bisa override per nota                    │
│                                              │
│ Auto-calculate di nota:                      │
│ Total = subtotal + pajak                     │
└─────────────────────────────────────────────┘
```

#### **Jatuh Tempo Default**

```
Setting: jatuh_tempo_default_hari
┌─────────────────────────────────────────────┐
│ Periode Kredit Default: [30] hari           │
│ * Bisa override per nota                    │
│                                              │
│ Auto-calculate:                              │
│ Jatuh Tempo = Tanggal Nota + 30 hari        │
└─────────────────────────────────────────────┘
```

---

### **7. EXPORT & BACKUP**

#### **Database Backup**

```
Setting: backup_otomatis (active/inactive)
┌─────────────────────────────────────────────┐
│ ✅ Otomatis Backup Database                 │
│ Frekuensi: Harian / Mingguan / Bulanan      │
│ Waktu: [23:00] (jam server)                 │
│ Retensi: [30] hari (auto-delete old backup) │
│                                              │
│ Manual Backup:                               │
│ [BACKUP SEKARANG] button                     │
│ - Generate .sql file                         │
│ - Auto-download ke computer                 │
└─────────────────────────────────────────────┘
```

#### **Export Default Format**

```
Setting: export_default_format
┌─────────────────────────────────────────────┐
│ Format default saat export:                  │
│ ⭕ PDF (untuk cetak)                         │
│ ⭕ EXCEL (untuk analisis)                    │
│ ⭕ BOTH (user pilih)                         │
│                                              │
│ [PILIH OPSI]                                 │
└─────────────────────────────────────────────┘
```

#### **Export Permission**

```
Setting: siapa_bisa_export
┌─────────────────────────────────────────────┐
│ Siapa yang boleh export data?                │
│ ⭕ BOZ ONLY (default - secure)              │
│ ⭕ BOZ & ADMIN                              │
│ ⭕ SEMUA (permissive)                        │
│                                              │
│ [PILIH OPSI]                                 │
└─────────────────────────────────────────────┘
```

---

### **8. ONBOARDING & USER MANAGEMENT**

#### **Onboarding Wizard**

```
Setting: onboarding_wizard_aktif
┌─────────────────────────────────────────────┐
│ ✅ Setup Wizard untuk User Baru              │
│ Tampilkan wizard saat pertama login:         │
│ - Set nama gudang                            │
│ - Add supplier utama                         │
│ - Add produk utama                           │
│ - Invite admin & checker                     │
│                                              │
│ [ENABLE / DISABLE]                           │
└─────────────────────────────────────────────┘
```

#### **User Management Interface**

```
Tabel: User List
┌─────────────────────────────────────────────┐
│ Nama | Email | Role | Warehouse | Status    │
├─────────────────────────────────────────────┤
│ Admin A | admin@... | ADMIN | Gudang A | ✅ │
│ Checker B | check@... | CHECKER | Gudang A | ✅│
│ ...                                          │
│                                              │
│ [+ Tambah User] [Edit] [Delete]              │
└─────────────────────────────────────────────┘

Add/Edit User Form:
- Nama
- Email (unique)
- Password (generate atau manual)
- Role (BOZ/ADMIN/CHECKER)
- Warehouse (jika not BOZ)
- Status (Active/Inactive)
```

---

### **9. KEAMANAN (Security)**

#### **Session Timeout**

```
Setting: session_timeout_menit
┌─────────────────────────────────────────────┐
│ Auto-logout setelah inaktif: [30] menit     │
│                                              │
│ Range: 15-120 menit                          │
└─────────────────────────────────────────────┘
```

#### **Password Policy**

```
Setting: password_policy_strength
┌─────────────────────────────────────────────┐
│ Minimum password length: [8] karakter       │
│ ✅ Require uppercase (A-Z)                  │
│ ✅ Require lowercase (a-z)                  │
│ ✅ Require numbers (0-9)                    │
│ ✅ Require special char (!@#$%^&*)          │
│                                              │
│ [SAVE]                                       │
└─────────────────────────────────────────────┘
```

#### **Two-Factor Authentication (2FA)**

```
Setting: 2fa_aktif
┌─────────────────────────────────────────────┐
│ ✅ Aktifkan 2FA untuk BOZ                    │
│ Metode: Email OTP / Google Authenticator    │
│                                              │
│ [ENABLE / DISABLE]                           │
└─────────────────────────────────────────────┘
```

---

### **10. API & INTEGRATION**

#### **API Key Management**

```
Setting: api_keys
┌─────────────────────────────────────────────┐
│ Kelola API keys untuk integrasi external    │
│                                              │
│ Tabel: Key | Created | Expires | Status     │
│ key_xxxxx | 01/01/2025 | Never | Active    │
│ key_yyyyy | 15/02/2025 | Expired | Inactive │
│                                              │
│ [+ Generate New Key] [Revoke] [Regenerate]  │
└─────────────────────────────────────────────┘
```

#### **Webhook Configuration**

```
Setting: webhook_url
┌─────────────────────────────────────────────┐
│ External system webhook URL:                │
│ [https://external-system.com/webhook]       │
│                                              │
│ Event triggers:                              │
│ ☐ Nota created                              │
│ ☐ Stok updated                              │
│ ☐ Hutang created                            │
│                                              │
│ [TEST WEBHOOK] [SAVE]                        │
└─────────────────────────────────────────────┘
```

---

## 💾 Settings Storage & Implementation

### **Database Structure**

```sql
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_gudang INT NOT NULL,
    kunci VARCHAR(100) NOT NULL,
    nilai TEXT,
    deskripsi TEXT,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_setting (id_gudang, kunci),
    FOREIGN KEY (id_gudang) REFERENCES gudang(id)
);

-- Example inserts:
INSERT INTO settings VALUES
(1, 'stok_minimum_threshold', '50', 'Default stok minimum'),
(1, 'pajak_default_persen', '10', 'Pajak default untuk nota'),
(1, 'komisi_penitipan_tipe', 'potong', 'Metode komisi'),
(1, 'jatuh_tempo_default_hari', '30', 'Default payment period');
```

### **PHP Implementation**

```php
// Load settings
$settings = $db->query(
    "SELECT kunci, nilai FROM settings WHERE id_gudang = ?"
)->fetchAll();

// Convert to array
$config = array_column($settings, 'nilai', 'kunci');

// Access
$stok_min = $config['stok_minimum_threshold'] ?? 50;
$pajak = $config['pajak_default_persen'] ?? 10;
```

### **Frontend Implementation**

```javascript
// Load settings via API
const settings = await fetch('/api/settings').then(r => r.json());

// Store in Alpine
x-data="{ settings: {{ json_encode($settings) }} }"

// Access
:disabled="settings.multi_warehouse_aktif === '0'"
```

---

## ✅ Default Settings (First Time Setup)

```
multi_warehouse_aktif: 0 (disabled)
stok_minimum_threshold: 50
susut_alert_threshold: 5
komisi_penitipan_tipe: potong
komisi_penitipan_persen: 5
pajak_default_persen: 10
jatuh_tempo_default_hari: 30
session_timeout_menit: 30
password_policy_strength: on
onboarding_wizard_aktif: 1 (enabled)
backup_otomatis: 1
export_permission: 0 (BOZ only)
```

---

**Next**: Baca `09-business-rules.md` untuk aturan bisnis →
