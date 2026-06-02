# 🔔 NOTIFIKASI — Peace Seafood In-App Notification System

---

## 📢 Notification Types

Semua notifikasi in-app (WhatsApp di-hold dulu per request).

---

## 📋 NOTIFICATION TYPES

### **1. STOK ALERTS**

**Trigger**: Stok < minimum threshold

```
Tipe: stok_minimum_alert
Judul: ⚠️ Stok Menipis - Ikan A
Pesan: Stok Ikan A tinggal 15 kg (minimum: 50 kg)
Penerima: Admin, Checker
Link: /stok/detail/1
Action: [Lihat Detail] [Dismiss]
```

**Setting**: `notifikasi_stok_minimum` (active/inactive)

```php
// Trigger after weighting & inventory update
if ($produk['stok_qty'] < $produk['stok_minimum']) {
    NotificationService::create([
        'tipe' => 'stok_minimum_alert',
        'for_roles' => ['admin', 'checker'],
        'reference_id' => $produk['id'],
        'reference_tipe' => 'produk'
    ]);
}
```

---

### **2. HUTANG JATUH TEMPO**

**Trigger**: 3 hari sebelum jatuh tempo hutang

```
Tipe: hutang_jatuh_tempo_reminder
Judul: 💰 Hutang Jatuh Tempo - Supplier A
Pesan: Hutang ke Supplier A jatuh tempo dalam 3 hari (20/06/2025)
       Nominal: Rp 1.000.000
Penerima: BOZ, Admin
Link: /keuangan/hutang-piutang/1
Action: [Lihat Detail] [Bayar Sekarang] [Dismiss]
```

**Setting**: `notifikasi_hutang_jatuh_tempo` (active/inactive)

```php
// Daily cron job
$hutang = $db->query(
    "SELECT * FROM hutang_piutang
     WHERE jenis='hutang' AND jatuh_tempo = DATE_ADD(NOW(), INTERVAL 3 DAY)"
)->fetchAll();

foreach ($hutang as $h) {
    NotificationService::create([
        'tipe' => 'hutang_jatuh_tempo_reminder',
        'for_roles' => ['bos', 'admin'],
        'reference_id' => $h['id']
    ]);
}
```

---

### **3. RETUR PENDING**

**Trigger**: Ada retur dengan status pending

```
Tipe: retur_pending_approval
Judul: 📋 Retur Menunggu Persetujuan
Pesan: Retur #RET-001 dari Supplier A menunggu persetujuan
       Item: Ikan A (10 kg)
       Alasan: Barang rusak
Penerima: BOZ
Link: /retur/detail/1
Action: [Setujui] [Tolak] [Lihat Detail]
```

**Setting**: `notifikasi_retur_pending` (active/inactive)

```php
// Trigger when retur created
NotificationService::create([
    'tipe' => 'retur_pending_approval',
    'for_roles' => ['bos'],
    'reference_id' => $retur_id,
    'reference_tipe' => 'retur'
]);
```

---

### **4. ERROR SYSTEM**

**Trigger**: Ada error di transaksi

```
Tipe: transaction_error
Judul: 🔴 Error Transaksi
Pesan: Gagal post nota #PS-250517-001
       Error: Stok tidak mencukupi
Penerima: BOZ, Admin (user yang buat)
Link: /penjualan/detail/100
Action: [Lihat Detail] [Retry]
```

**Setting**: `notifikasi_error_system` (active/inactive)

```php
try {
    postNota($nota_id);
} catch (Exception $e) {
    NotificationService::create([
        'tipe' => 'transaction_error',
        'for_roles' => ['bos', 'admin'],
        'pesan' => "Gagal post nota: " . $e->getMessage(),
        'reference_id' => $nota_id
    ]);
}
```

---

### **5. SISTEM MAINTENANCE**

**Trigger**: Sistem maintenance / update

```
Tipe: system_maintenance
Judul: 🔧 Sistem Maintenance
Pesan: Sistem akan maintenance 22:00 - 23:00 WIB
       Durasi: ~1 jam
Penerima: Semua user
```

```php
NotificationService::create([
    'tipe' => 'system_maintenance',
    'for_roles' => ['bos', 'admin', 'checker'],
    'pesan' => 'Sistem akan maintenance 22:00-23:00 WIB'
]);
```

---

## 💾 DATABASE STRUCTURE

```sql
CREATE TABLE notifikasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    tipe VARCHAR(50) NOT NULL,
    judul VARCHAR(255) NOT NULL,
    pesan TEXT NOT NULL,
    reference_id INT,
    reference_tipe VARCHAR(50),
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

## 🔌 API ENDPOINTS

### **GET /notifikasi**

Get notifikasi untuk user

**Query:**

```
?unread_only=1  (optional)
&page=1
&per_page=10
```

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tipe": "stok_minimum_alert",
      "judul": "⚠️ Stok Menipis - Ikan A",
      "pesan": "Stok Ikan A tinggal 15 kg",
      "is_read": 0,
      "created_at": "2025-05-17T10:30:00Z",
      "link": "/stok/detail/1"
    }
  ],
  "unread_count": 5,
  "total": 25
}
```

### **POST /notifikasi/:id/read**

Mark notifikasi as read

**Response:**

```json
{
  "success": true,
  "data": {
    "is_read": 1,
    "read_at": "2025-05-17T10:35:00Z"
  }
}
```

### **POST /notifikasi/read-all**

Mark all notifikasi as read

**Response:**

```json
{
  "success": true,
  "message": "Semua notifikasi ditandai sebagai dibaca"
}
```

---

## 🎨 FRONTEND DISPLAY

### **Notification Bell (Navbar)**

```html
<div class="notification-bell" x-data="{ unread: 0, list: [] }">
  <!-- Bell Icon with Badge -->
  <button @click="toggleDropdown()" class="btn-icon relative">
    <i class="lucide-bell w-6 h-6"></i>
    <span x-show="unread > 0" class="badge badge-danger absolute top-0 right-0">
      <span x-text="unread"></span>
    </span>
  </button>

  <!-- Dropdown List -->
  <div
    class="notification-dropdown"
    x-show="showDropdown"
    @click.away="showDropdown = false"
  >
    <div class="dropdown-header">
      <h4>Notifikasi</h4>
      <button @click="markAllRead()" class="btn-link">Tandai Semua</button>
    </div>

    <div class="dropdown-body max-h-96 overflow-y-auto">
      <template x-for="notif in list" :key="notif.id">
        <div class="notification-item" :class="{ 'unread': !notif.is_read }">
          <div class="flex-1">
            <h5 x-text="notif.judul"></h5>
            <p x-text="notif.pesan" class="text-sm text-secondary"></p>
            <span
              class="text-xs text-secondary"
              x-text="formatTime(notif.created_at)"
            ></span>
          </div>
          <div class="flex gap-2">
            <a :href="notif.link" class="btn btn-sm btn-primary"> Lihat </a>
            <button @click="markRead(notif.id)" class="btn btn-sm">
              <i class="lucide-x w-4 h-4"></i>
            </button>
          </div>
        </div>
      </template>
    </div>

    <div class="dropdown-footer">
      <a href="/notifikasi" class="btn btn-link w-full text-center">
        Lihat Semua Notifikasi
      </a>
    </div>
  </div>
</div>

<script>
  const notifDropdown = {
    showDropdown: false,

    async init() {
      this.fetchNotifications();
      // Poll every 10 seconds
      setInterval(() => this.fetchNotifications(), 10000);
    },

    async fetchNotifications() {
      const response = await fetch("/api/notifikasi");
      const data = await response.json();
      this.list = data.data;
      this.unread = data.unread_count;
    },

    toggleDropdown() {
      this.showDropdown = !this.showDropdown;
    },

    async markRead(notifId) {
      await fetch(`/api/notifikasi/${notifId}/read`, { method: "POST" });
      this.fetchNotifications();
    },

    async markAllRead() {
      await fetch("/api/notifikasi/read-all", { method: "POST" });
      this.fetchNotifications();
    },

    formatTime(timestamp) {
      // Format relative time (2 mins ago, 1 hour ago, etc)
      return new Date(timestamp).toLocaleDateString("id-ID");
    },
  };
</script>
```

---

## 🔄 NOTIFICATION SERVICE

```php
<?php
class NotificationService {
    public static function create($data) {
        $roles = $data['for_roles'] ?? [];

        // Get users with specified roles
        $users = self::getUsersByRoles($roles, $data['id_gudang'] ?? null);

        foreach ($users as $user) {
            $stmt = Database::prepare(
                "INSERT INTO notifikasi (id_user, tipe, judul, pesan, reference_id, reference_tipe, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())"
            );

            $stmt->execute([
                $user['id'],
                $data['tipe'],
                $data['judul'],
                $data['pesan'],
                $data['reference_id'] ?? null,
                $data['reference_tipe'] ?? null
            ]);
        }
    }

    private static function getUsersByRoles($roles, $warehouse_id = null) {
        $placeholders = implode(',', array_fill(0, count($roles), '?'));

        $sql = "SELECT id FROM users WHERE role IN ($placeholders)";

        if ($warehouse_id && !in_array('bos', $roles)) {
            $sql .= " OR (id_gudang = $warehouse_id AND role IN ('admin', 'checker'))";
        }

        $stmt = Database::prepare($sql);
        $stmt->execute($roles);
        return $stmt->fetchAll();
    }

    public static function markAsRead($notification_id) {
        $stmt = Database::prepare(
            "UPDATE notifikasi SET is_read = 1, read_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$notification_id]);
    }
}
?>
```

---

## ✅ Notification Settings

```
Default settings di admin panel:

notifikasi_stok_minimum: ON
notifikasi_hutang_jatuh_tempo: ON
notifikasi_retur_pending: ON
notifikasi_error_system: ON

User bisa customize di profile settings (future feature)
```

---

**Next**: Baca `17-retur.md` untuk alur retur detail →
