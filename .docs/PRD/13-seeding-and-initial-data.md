# 🌱 SEEDER & INITIAL DATA — Peace Seafood

---

## 📌 Seeder Purpose

Seeder menciptakan data awal yang diperlukan untuk setup aplikasi first-time dan untuk testing/development.

---

## 🚀 How to Run Seeder

```bash
# Run all seeders
php cli/seeder.php

# Run specific seeder
php cli/seeder.php UserSeeder
php cli/seeder.php SupplierSeeder
```

---

## 👥 USER SEEDER

```sql
-- Default BOZ (Pemilik)
INSERT INTO users (name, email, password, role, id_gudang, is_active)
VALUES (
  'Bos Gudang',
  'bos@example.com',
  '$2y$10$...(hashed password: password123)',
  'bos',
  NULL,
  1
);

-- Default Admin
INSERT INTO users (name, email, password, role, id_gudang, is_active)
VALUES (
  'Admin Gudang A',
  'admin@example.com',
  '$2y$10$...(hashed)',
  'admin',
  1,
  1
);

-- Default Checker
INSERT INTO users (name, email, password, role, id_gudang, is_active)
VALUES (
  'Checker Gudang A',
  'checker@example.com',
  '$2y$10$...(hashed)',
  'checker',
  1,
  1
);

-- Default Test User (Development)
INSERT INTO users (name, email, password, role, id_gudang, is_active)
VALUES (
  'Test User',
  'test@example.com',
  '$2y$10$...(hashed: test123)',
  'admin',
  1,
  1
);
```

**Default Credentials:**

```
BOZ:
Email: bos@example.com
Password: password123

Admin:
Email: admin@example.com
Password: password123

Checker:
Email: checker@example.com
Password: password123

Test:
Email: test@example.com
Password: test123
```

---

## 🏢 GUDANG SEEDER

```sql
INSERT INTO gudang (id_bos, nama, alamat, kota, telpon, is_active)
VALUES
(1, 'Gudang A - Pusat', 'Jl. Merdeka No. 1', 'Jakarta', '021-12345678', 1),
(1, 'Gudang B - Cabang', 'Jl. Sudirman No. 5', 'Jakarta', '021-87654321', 1),
(1, 'Gudang C - Satellite', 'Jl. Thamrin No. 10', 'Tangerang', '021-55555555', 1);
```

---

## 🐟 JENIS_IKAN SEEDER

```sql
INSERT INTO jenis_ikan (nama, deskripsi, is_active)
VALUES
('Ikan Laut Segar', 'Ikan laut dari nelayan', 1),
('Ikan Darat Segar', 'Ikan dari kolam/budidaya', 1),
('Ikan Beku', 'Ikan yang sudah dibekukan', 1),
('Ikan Olahan', 'Ikan yang sudah diproses', 1),
('Seafood Lainnya', 'Udang, cumi, dll', 1);
```

---

## 📦 PRODUK SEEDER

```sql
INSERT INTO produk (id_jenis_ikan, id_gudang, nama, deskripsi, harga_beli, harga_jual, stok_minimum, is_active)
VALUES
(1, 1, 'Ikan A - Segar', 'Ikan laut segar berkualitas', 50000, 65000, 50, 1),
(1, 1, 'Ikan B - Segar', 'Ikan laut segar pilihan', 60000, 75000, 50, 1),
(1, 1, 'Ikan C - Segar', 'Ikan laut segar premium', 75000, 95000, 30, 1),
(2, 1, 'Ikan Nila - Segar', 'Ikan nila dari kolam', 35000, 45000, 100, 1),
(3, 1, 'Ikan A - Beku', 'Ikan laut beku', 45000, 55000, 50, 1),
(5, 1, 'Udang Windu', 'Udang windu segar', 120000, 150000, 25, 1);

-- Repeat untuk gudang lain (id_gudang = 2, 3)
```

---

## 🏪 SUPPLIER SEEDER

```sql
INSERT INTO supplier (id_gudang, nama, nama_pemilik, kontak_person, telpon, alamat, kota, bank_name, bank_account, bank_owner, is_active)
VALUES
(1, 'Supplier Laut Jaya', 'Budi Santoso', 'Adi', '0812-11111111', 'Jl. Pelabuhan No.1', 'Jakarta', 'BCA', '1234567890', 'PT LAUT JAYA', 1),
(1, 'Supplier Ikan Nusa', 'Siti Nurhaliza', 'Rini', '0812-22222222', 'Jl. Perikanan No.5', 'Depok', 'BRI', '0987654321', 'CV IKAN NUSA', 1),
(1, 'Supplier Seafood Indah', 'Ahmad Wijaya', 'Bambang', '0812-33333333', 'Jl. Pelindo No.10', 'Medan', 'Mandiri', '1111111111', 'PT SEAFOOD INDAH', 1),
(1, 'Supplier Premium Fish', 'Tina Wijaya', 'Supri', '0812-44444444', 'Jl. Dermaga No.15', 'Surabaya', 'BNI', '2222222222', 'PT PREMIUM FISH', 1);

-- Repeat untuk gudang lain
```

---

## 👥 PEMBELI SEEDER

```sql
INSERT INTO pembeli (id_gudang, nama, telpon, alamat, kota, tipe, kredit_limit, is_active)
VALUES
(1, 'PT Restoran Mewah', '021-11111111', 'Jl. Gatot Subroto', 'Jakarta', 'bulk', 50000000, 1),
(1, 'Pasar Senen', '021-22222222', 'Pasar Senen', 'Jakarta', 'retail', 10000000, 1),
(1, 'Hotel Grand Indonesia', '021-33333333', 'Jl. Thamrin', 'Jakarta', 'bulk', 100000000, 1),
(1, 'Toko Ikan Segar', '021-44444444', 'Jl. Hayam Wuruk', 'Jakarta', 'retail', 5000000, 1),
(1, 'Restoran Seafood', '021-55555555', 'Jl. Blora', 'Jakarta', 'bulk', 30000000, 1),
(1, 'Pasar Tradisional', '021-66666666', 'Pasar Tanah Abang', 'Jakarta', 'retail', 8000000, 1);

-- Repeat untuk gudang lain
```

---

## 📊 STOK_MASUK SEEDER (Test Data)

```sql
-- Stok masuk 7 hari terakhir
INSERT INTO stok_masuk (id_gudang, id_produk, id_supplier, qty, harga_beli, status, created_by, created_at)
VALUES
(1, 1, 1, 500, 50000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1, 2, 1, 300, 60000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 3, 1, 200, 75000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 4, 2, 1000, 35000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 1, 1, 300, 50000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 2, 2, 250, 60000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 1, 3, 400, 50000, 'confirmed', 2, DATE_SUB(NOW(), INTERVAL 1 DAY));
```

---

## 📝 TIMBANGAN SEEDER (Test Data)

```sql
-- Timbangan data (auto-confirm stok)
INSERT INTO timbangan (id_stok_masuk, id_produk, qty_teoritis, qty_actual, alasan_susut, created_by, created_at)
VALUES
(1, 1, 500, 495, 'Kemasan pecah sedikit', 3, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 2, 300, 298, 'Evaporasi', 3, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(3, 3, 200, 200, NULL, 3, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, 4, 1000, 990, 'Kualitas jelek 10kg', 3, DATE_SUB(NOW(), INTERVAL 4 DAY));
```

---

## 💳 NOTA SEEDER (Test Data)

```sql
-- Nota penjualan 30 hari terakhir (untuk test laporan)
INSERT INTO nota (id_gudang, id_pembeli, no_nota, tanggal_nota, subtotal, diskon_nominal, pajak, total, pembayaran, status, created_by, created_at)
VALUES
(1, 1, 'PS-250420-0001', '2025-04-20', 5000000, 200000, 480000, 5280000, 'cash', 'final', 2, '2025-04-20 10:00:00'),
(1, 2, 'PS-250421-0001', '2025-04-21', 2500000, 0, 250000, 2750000, 'hutang', 'final', 2, '2025-04-21 14:30:00'),
(1, 1, 'PS-250422-0001', '2025-04-22', 3000000, 150000, 285000, 3135000, 'cash', 'final', 2, '2025-04-22 09:15:00'),
-- More data for each day up to today
```

---

## 🛒 NOTA_DETAIL SEEDER (Test Data)

```sql
-- Link nota dengan produk items
INSERT INTO nota_detail (id_nota, id_produk, qty, harga_jual, subtotal)
VALUES
(1, 1, 50, 65000, 3250000),
(1, 2, 30, 75000, 2250000),
(2, 4, 100, 45000, 4500000),
-- ... more items per nota
```

---

## 💰 HUTANG_PIUTANG SEEDER (Test Data)

```sql
-- Hutang ke supplier
INSERT INTO hutang_piutang (id_gudang, jenis, id_supplier, id_pembeli, nominal, nominal_bayar, jatuh_tempo, status)
VALUES
(1, 'hutang', 1, NULL, 5000000, 2000000, DATE_ADD(NOW(), INTERVAL 30 DAY), 'sebagian'),
(1, 'hutang', 2, NULL, 3000000, 0, DATE_ADD(NOW(), INTERVAL 30 DAY), 'open'),

-- Piutang dari pembeli
(1, 'piutang', NULL, 1, 8000000, 5000000, DATE_ADD(NOW(), INTERVAL 30 DAY), 'sebagian'),
(1, 'piutang', NULL, 2, 4000000, 0, DATE_ADD(NOW(), INTERVAL 15 DAY), 'open');
```

---

## ⚙️ SETTINGS SEEDER (Default)

```sql
INSERT INTO settings (id_gudang, kunci, nilai, deskripsi)
VALUES
(1, 'multi_warehouse_aktif', '0', 'Multi warehouse feature'),
(1, 'stok_minimum_threshold', '50', 'Default stok minimum'),
(1, 'susut_alert_threshold', '5', 'Alert threshold %'),
(1, 'komisi_penitipan_tipe', 'potong', 'Komisi method'),
(1, 'komisi_penitipan_persen', '5', 'Komisi %'),
(1, 'pajak_default_persen', '10', 'Pajak default'),
(1, 'jatuh_tempo_default_hari', '30', 'Jatuh tempo hari'),
(1, 'session_timeout_menit', '30', 'Session timeout'),
(1, 'onboarding_wizard_aktif', '1', 'Onboarding wizard'),
(1, 'backup_otomatis', '1', 'Auto backup');
```

---

## 🔄 HARGA_HISTORY SEEDER

```sql
-- Track harga changes
INSERT INTO harga_history (id_produk, harga_lama, harga_baru, tipe, reason, changed_by, created_at)
VALUES
(1, 45000, 50000, 'beli', 'Harga naik supplier', 1, DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 60000, 65000, 'jual', 'Adjustment market', 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2, 55000, 60000, 'beli', 'Kualitas lebih baik', 1, DATE_SUB(NOW(), INTERVAL 7 DAY));
```

---

## 🏃 Seeder PHP Implementation

```php
<?php
// cli/seeder.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

class Seeder {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function seedUsers() {
        echo "Seeding users...\n";

        $sql = "INSERT INTO users (name, email, password, role, id_gudang, is_active) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $users = [
            ['Bos Gudang', 'bos@example.com', password_hash('password123', PASSWORD_BCRYPT), 'bos', null, 1],
            ['Admin A', 'admin@example.com', password_hash('password123', PASSWORD_BCRYPT), 'admin', 1, 1],
            ['Checker A', 'checker@example.com', password_hash('password123', PASSWORD_BCRYPT), 'checker', 1, 1],
        ];

        foreach ($users as $user) {
            $stmt->execute($user);
        }

        echo "✓ Users seeded\n";
    }

    public function seedAll() {
        try {
            $this->seedUsers();
            echo "✓ All seeders completed\n";
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
}

$seeder = new Seeder($pdo);
$seeder->seedAll();
?>
```

---

## ✅ Development vs Production

### **Development Seeder** (Include test data)

- Sample users (BOZ, Admin, Checker)
- Sample gudang, supplier, pembeli
- 30 days test transactions
- Test hutang/piutang
- For testing & development

### **Production Seeder** (Minimal data only)

- Only main user (BOZ)
- Konfigurasi gudang
- Settings defaults
- NO test transactions
- Clean for production start

---

**Next**: Baca `14-error-handling.md` untuk error management →
