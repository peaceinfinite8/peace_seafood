# 📊 DATABASE SCHEMA — Peace Seafood

---

## 🏛️ Database Overview

**Database Name**: `peace_seafood`
**Charset**: `utf8mb4`
**Collation**: `utf8mb4_unicode_ci`
**Engine**: InnoDB

---

## 📋 Complete Table Structure

### **1. USERS TABLE**

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('bos', 'admin', 'checker') NOT NULL,
    id_gudang INT NULL,  -- NULL for BOZ, specific for ADMIN/CHECKER
    is_active TINYINT DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_gudang) REFERENCES gudang(id),
    INDEX idx_role (role),
    INDEX idx_gudang (id_gudang)
);
```

---

### **2. GUDANG TABLE**

```sql
CREATE TABLE gudang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_bos INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    kota VARCHAR(50) NOT NULL,
    telpon VARCHAR(20) NOT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_bos) REFERENCES users(id),
    INDEX idx_bos (id_bos),
    INDEX idx_nama (nama)
);
```

---

### **3. SUPPLIER TABLE**

```sql
CREATE TABLE supplier (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_gudang INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    nama_pemilik VARCHAR(100),
    kontak_person VARCHAR(100),
    telpon VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    kota VARCHAR(50),
    bank_name VARCHAR(50),
    bank_account VARCHAR(20),
    bank_owner VARCHAR(100),
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_gudang) REFERENCES gudang(id),
    INDEX idx_gudang (id_gudang),
    INDEX idx_nama (nama)
);
```

---

### **4. PEMBELI TABLE**

```sql
CREATE TABLE pembeli (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_gudang INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    telpon VARCHAR(20) NOT NULL,
    alamat TEXT,
    kota VARCHAR(50),
    tipe ENUM('retail', 'bulk', 'reseller') DEFAULT 'retail',
    kredit_limit DECIMAL(15,2) DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_gudang) REFERENCES gudang(id),
    INDEX idx_gudang (id_gudang),
    INDEX idx_nama (nama)
);
```

---

### **5. JENIS_IKAN TABLE**

```sql
CREATE TABLE jenis_ikan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL UNIQUE,
    deskripsi TEXT,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nama (nama)
);
```

---

### **6. PRODUK TABLE**

```sql
CREATE TABLE produk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_jenis_ikan INT NOT NULL,
    id_gudang INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    stok_qty DECIMAL(10,2) DEFAULT 0,  -- Current stock quantity (kg)
    stok_value DECIMAL(15,2) DEFAULT 0, -- Current stock value (harga x qty)
    harga_beli DECIMAL(15,2),           -- Last purchase price
    harga_jual DECIMAL(15,2),           -- Current selling price
    stok_minimum DECIMAL(10,2) DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_jenis_ikan) REFERENCES jenis_ikan(id),
    FOREIGN KEY (id_gudang) REFERENCES gudang(id),
    INDEX idx_jenis (id_jenis_ikan),
    INDEX idx_gudang (id_gudang),
    INDEX idx_nama (nama)
);
```

---

### **7. HARGA_HISTORY TABLE**

```sql
CREATE TABLE harga_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_produk INT NOT NULL,
    harga_lama DECIMAL(15,2),
    harga_baru DECIMAL(15,2) NOT NULL,
    tipe ENUM('beli', 'jual') NOT NULL,  -- Purchase or selling price
    reason VARCHAR(255),
    changed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_produk) REFERENCES produk(id),
    FOREIGN KEY (changed_by) REFERENCES users(id),
    INDEX idx_produk (id_produk),
    INDEX idx_tipe (tipe),
    INDEX idx_created (created_at)
);
```

---

### **8. STOK_MASUK TABLE**

```sql
CREATE TABLE stok_masuk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_gudang INT NOT NULL,
    id_produk INT NOT NULL,
    id_supplier INT NOT NULL,
    qty DECIMAL(10,2) NOT NULL,
    harga_beli DECIMAL(15,2) NOT NULL,
    total DECIMAL(15,2),
    status ENUM('pending', 'confirmed', 'rejected') DEFAULT 'pending',
    catatan TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_gudang) REFERENCES gudang(id),
    FOREIGN KEY (id_produk) REFERENCES produk(id),
    FOREIGN KEY (id_supplier) REFERENCES supplier(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_gudang (id_gudang),
    INDEX idx_produk (id_produk),
    INDEX idx_supplier (id_supplier),
    INDEX idx_status (status)
);
```

---

### **9. TIMBANGAN TABLE**

```sql
CREATE TABLE timbangan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_stok_masuk INT NOT NULL,
    id_produk INT NOT NULL,
    qty_teoritis DECIMAL(10,2) NOT NULL,
    qty_actual DECIMAL(10,2) NOT NULL,
    susut DECIMAL(10,2),  -- qty_teoritis - qty_actual
    persentase_susut DECIMAL(5,2),
    alasan_susut VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_stok_masuk) REFERENCES stok_masuk(id),
    FOREIGN KEY (id_produk) REFERENCES produk(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_stok_masuk (id_stok_masuk),
    INDEX idx_produk (id_produk)
);
```

---

### **10. NOTA TABLE**

```sql
CREATE TABLE nota (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_gudang INT NOT NULL,
    id_pembeli INT NOT NULL,
    no_nota VARCHAR(50) UNIQUE NOT NULL,
    tanggal_nota DATE NOT NULL,
    subtotal DECIMAL(15,2),
    diskon_nominal DECIMAL(15,2) DEFAULT 0,
    diskon_persen DECIMAL(5,2) DEFAULT 0,
    pajak DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2),
    pembayaran ENUM('cash', 'hutang') NOT NULL,
    status ENUM('draft', 'final', 'cancelled') DEFAULT 'draft',
    catatan TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_gudang) REFERENCES gudang(id),
    FOREIGN KEY (id_pembeli) REFERENCES pembeli(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_gudang (id_gudang),
    INDEX idx_pembeli (id_pembeli),
    INDEX idx_no_nota (no_nota),
    INDEX idx_tanggal (tanggal_nota),
    INDEX idx_status (status)
);
```

---

### **11. NOTA_DETAIL TABLE**

```sql
CREATE TABLE nota_detail (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_nota INT NOT NULL,
    id_produk INT NOT NULL,
    qty DECIMAL(10,2) NOT NULL,
    harga_jual DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_nota) REFERENCES nota(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id),
    INDEX idx_nota (id_nota),
    INDEX idx_produk (id_produk)
);
```

---

### **12. TITIPAN TABLE**

```sql
CREATE TABLE titipan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_gudang_penerima INT NOT NULL,
    id_supplier_pengirim INT NOT NULL,
    qty DECIMAL(10,2) NOT NULL,
    harga_kesepakatan DECIMAL(15,2) NOT NULL,
    komisi_persen DECIMAL(5,2),
    status ENUM('masuk', 'dijual_sebagian', 'dijual_semua', 'selesai') DEFAULT 'masuk',
    tanggal_masuk DATE NOT NULL,
    tanggal_selesai DATE NULL,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_gudang_penerima) REFERENCES gudang(id),
    FOREIGN KEY (id_supplier_pengirim) REFERENCES supplier(id),
    INDEX idx_gudang (id_gudang_penerima),
    INDEX idx_supplier (id_supplier_pengirim),
    INDEX idx_status (status)
);
```

---

### **13. TITIPAN_PENJUALAN TABLE**

```sql
CREATE TABLE titipan_penjualan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_titipan INT NOT NULL,
    id_nota INT,                          -- Link ke nota jika dijual via sistem
    qty_terjual DECIMAL(10,2) NOT NULL,
    total_jual DECIMAL(15,2) NOT NULL,
    komisi_nominal DECIMAL(15,2),
    penjual ENUM('supplier_pengirim', 'gudang_penerima'),
    status_pembayaran ENUM('pending', 'paid') DEFAULT 'pending',
    tanggal_jual DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_titipan) REFERENCES titipan(id),
    FOREIGN KEY (id_nota) REFERENCES nota(id),
    INDEX idx_titipan (id_titipan),
    INDEX idx_nota (id_nota)
);
```

---

### **14. RETUR TABLE**

```sql
CREATE TABLE retur (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_gudang INT NOT NULL,
    id_produk INT NOT NULL,
    id_nota INT NULL,                     -- NULL jika retur dari supplier, set jika dari penjualan
    tipe_retur ENUM('stok', 'piutang') NOT NULL,  -- Stok return atau piutang adjustment
    dari ENUM('supplier', 'pembeli'),
    qty DECIMAL(10,2) NOT NULL,
    harga DECIMAL(15,2),
    total DECIMAL(15,2),
    alasan TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    catatan TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_gudang) REFERENCES gudang(id),
    FOREIGN KEY (id_produk) REFERENCES produk(id),
    FOREIGN KEY (id_nota) REFERENCES nota(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_gudang (id_gudang),
    INDEX idx_tipe (tipe_retur),
    INDEX idx_status (status)
);
```

---

### **15. HUTANG_PIUTANG TABLE**

```sql
CREATE TABLE hutang_piutang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_gudang INT NOT NULL,
    jenis ENUM('hutang', 'piutang') NOT NULL,  -- Hutang to supplier, Piutang from buyer
    id_supplier INT NULL,                       -- If jenis = hutang
    id_pembeli INT NULL,                        -- If jenis = piutang
    nominal DECIMAL(15,2) NOT NULL,
    nominal_bayar DECIMAL(15,2) DEFAULT 0,
    sisa_hutang DECIMAL(15,2),
    jatuh_tempo DATE,
    status ENUM('open', 'sebagian', 'lunas') DEFAULT 'open',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_gudang) REFERENCES gudang(id),
    FOREIGN KEY (id_supplier) REFERENCES supplier(id),
    FOREIGN KEY (id_pembeli) REFERENCES pembeli(id),
    INDEX idx_gudang (id_gudang),
    INDEX idx_jenis (jenis),
    INDEX idx_status (status),
    INDEX idx_jatuh_tempo (jatuh_tempo)
);
```

---

### **16. BIAYA_OPERASIONAL TABLE**

```sql
CREATE TABLE biaya_operasional (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_gudang INT NOT NULL,
    kategori VARCHAR(50) NOT NULL,          -- Gaji, Listrik, Telpon, Transport, dll
    deskripsi TEXT,
    nominal DECIMAL(15,2) NOT NULL,
    tanggal_biaya DATE NOT NULL,
    bukti_file VARCHAR(255),                -- File name (optional)
    status ENUM('pending', 'approved') DEFAULT 'pending',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_gudang) REFERENCES gudang(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_gudang (id_gudang),
    INDEX idx_kategori (kategori),
    INDEX idx_tanggal (tanggal_biaya)
);
```

---

### **17. NOTIFIKASI TABLE**

```sql
CREATE TABLE notifikasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    tipe VARCHAR(50) NOT NULL,              -- 'stok_minimum', 'hutang_jatuh_tempo', 'error', dll
    judul VARCHAR(255) NOT NULL,
    pesan TEXT NOT NULL,
    reference_id INT,                       -- Link to related record (nota, stok_masuk, dll)
    reference_tipe VARCHAR(50),             -- 'nota', 'stok_masuk', dll
    is_read TINYINT DEFAULT 0,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_user) REFERENCES users(id),
    INDEX idx_user (id_user),
    INDEX idx_tipe (tipe),
    INDEX idx_is_read (is_read),
    INDEX idx_created (created_at)
);
```

---

### **18. SETTINGS TABLE**

```sql
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_gudang INT NOT NULL,
    kunci VARCHAR(100) NOT NULL,
    nilai TEXT,
    deskripsi TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_gudang) REFERENCES gudang(id),
    UNIQUE KEY unique_setting (id_gudang, kunci),
    INDEX idx_gudang (id_gudang)
);

-- Example values:
-- (id_gudang, 'multi_warehouse_aktif', '0/1')
-- (id_gudang, 'stok_minimum_threshold', '50')
-- (id_gudang, 'komisi_penitipan_tipe', 'potong/bayar_terpisah')
-- (id_gudang, 'notifikasi_stok_minimum', '1')
-- (id_gudang, 'notifikasi_hutang_jatuh_tempo', '1')
-- (id_gudang, 'pajak_default', '10')
```

---

## 🔗 Relationship Diagram

```
users (BOZ/ADMIN/CHECKER)
  ├─ gudang (warehouse)
  │   ├─ produk (products)
  │   │   ├─ jenis_ikan (fish type)
  │   │   ├─ harga_history (price history)
  │   │   ├─ stok_masuk (stock in)
  │   │   │   ├─ timbangan (weighing)
  │   │   │   └─ nota_detail
  │   │   ├─ titipan (consignment)
  │   │   ├─ retur (return)
  │   │   └─ hutang_piutang
  │   ├─ supplier
  │   │   ├─ stok_masuk
  │   │   └─ titipan
  │   ├─ pembeli
  │   │   └─ nota
  │   │       └─ nota_detail
  │   ├─ nota (sales)
  │   ├─ titipan_penjualan
  │   ├─ biaya_operasional
  │   ├─ notifikasi
  │   └─ settings
```

---

## ✅ Indexing Strategy

All important columns indexed for performance:
- Foreign keys: INDEXED
- Search fields (nama): INDEXED
- Status fields: INDEXED
- Date fields: INDEXED
- User filtering: INDEXED

---

**Next**: Baca `06-alur-modul.md` untuk business flow →

