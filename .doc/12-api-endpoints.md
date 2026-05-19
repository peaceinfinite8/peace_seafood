# 🔌 API ENDPOINTS — Peace Seafood

---

## 📋 API Overview

Semua endpoint REST API dengan JSON request/response.
Base URL: `http://localhost/api`
Authentication: JWT Token (HttpOnly Cookie)

---

## 🔐 AUTHENTICATION ENDPOINTS

### **POST /auth/login**

Login user & generate JWT token

**Request:**

```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

**Response (Success - 200):**

```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "id": 1,
    "name": "Admin A",
    "email": "admin@example.com",
    "role": "admin",
    "id_gudang": 1,
    "token": "eyJhbGc..."
  }
}
```

**Response (Error - 401):**

```json
{
  "success": false,
  "message": "Email atau password tidak sesuai",
  "errors": {}
}
```

### **POST /auth/logout**

Logout user & invalidate token

**Response (200):**

```json
{
  "success": true,
  "message": "Logout berhasil"
}
```

### **GET /auth/profile**

Get current user profile

**Response (200):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin A",
    "email": "admin@example.com",
    "role": "admin",
    "gudang": {
      "id": 1,
      "nama": "Gudang A"
    }
  }
}
```

---

## 📦 STOK ENDPOINTS

### **GET /stok**

List inventory dengan filter

**Query Params:**

```
?id_gudang=1
&id_produk=5 (optional)
&search=ikan (optional)
&sort=qty_desc
&page=1
&per_page=20
```

**Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "produk": {
        "id": 1,
        "nama": "Ikan A",
        "jenis_ikan": "Ikan Laut"
      },
      "qty": 100,
      "harga_beli": 50000,
      "stok_value": 5000000,
      "last_updated": "2025-05-17T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 50,
    "per_page": 20
  }
}
```

### **POST /stok/masuk**

Create stok masuk

**Request:**

```json
{
  "id_gudang": 1,
  "id_supplier": 3,
  "id_produk": 1,
  "qty": 100,
  "harga_beli": 50000,
  "catatan": "Dari supplier regular"
}
```

**Response (201):**

```json
{
  "success": true,
  "message": "Stok masuk berhasil dibuat",
  "data": {
    "id": 45,
    "id_stok_masuk": 45,
    "status": "pending",
    "created_at": "2025-05-17T10:30:00Z"
  }
}
```

### **POST /stok/timbang**

Input timbangan (weighing)

**Request:**

```json
{
  "id_stok_masuk": 45,
  "qty_actual": 98,
  "alasan_susut": "Kemasan pecah",
  "created_by": 2
}
```

**Response (201):**

```json
{
  "success": true,
  "message": "Timbangan berhasil",
  "data": {
    "id": 1,
    "susut": 2,
    "persentase_susut": 2.04,
    "stok_confirmed": 98
  }
}
```

### **GET /stok/history**

Get stok history per produk

**Query:**

```
?id_produk=1
&id_gudang=1
&date_from=2025-05-01
&date_to=2025-05-31
```

**Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "tipe": "masuk",
      "qty": 100,
      "harga": 50000,
      "supplier": "Supplier A",
      "tanggal": "2025-05-15"
    },
    {
      "tipe": "keluar",
      "qty": 30,
      "pembeli": "Pembeli A",
      "tanggal": "2025-05-16"
    }
  ]
}
```

---

## 💳 PENJUALAN (SALES) ENDPOINTS

### **POST /penjualan/create**

Create nota penjualan

**Request:**

```json
{
  "id_gudang": 1,
  "id_pembeli": 5,
  "items": [
    {
      "id_produk": 1,
      "qty": 50,
      "harga_jual": 60000
    },
    {
      "id_produk": 2,
      "qty": 30,
      "harga_jual": 70000
    }
  ],
  "diskon_nominal": 200000,
  "pajak": 410000,
  "pembayaran": "hutang",
  "catatan": "Pembeli setia"
}
```

**Response (201):**

```json
{
  "success": true,
  "message": "Nota berhasil dibuat",
  "data": {
    "id": 100,
    "no_nota": "PS-250517-0001",
    "total": 4510000,
    "status": "draft"
  }
}
```

### **GET /penjualan/:id**

Get nota detail

**Response (200):**

```json
{
  "success": true,
  "data": {
    "id": 100,
    "no_nota": "PS-250517-0001",
    "pembeli": { "nama": "Pembeli A" },
    "items": [
      {
        "id_produk": 1,
        "nama_produk": "Ikan A",
        "qty": 50,
        "harga_jual": 60000,
        "subtotal": 3000000
      }
    ],
    "subtotal": 4300000,
    "diskon": 200000,
    "pajak": 410000,
    "total": 4510000,
    "pembayaran": "hutang",
    "status": "draft"
  }
}
```

### **POST /penjualan/:id/finalize**

Finalize nota (lock from editing)

**Response (200):**

```json
{
  "success": true,
  "message": "Nota difinalisasi",
  "data": {
    "status": "final"
  }
}
```

### **POST /penjualan/:id/cancel**

Cancel nota

**Response (200):**

```json
{
  "success": true,
  "message": "Nota dibatalkan",
  "data": {
    "status": "cancelled"
  }
}
```

### **GET /penjualan/list**

List nota dengan filter

**Query:**

```
?id_gudang=1
&date_from=2025-05-01
&date_to=2025-05-31
&status=final
&page=1
```

**Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": 100,
      "no_nota": "PS-250517-0001",
      "pembeli": "Pembeli A",
      "total": 4510000,
      "pembayaran": "hutang",
      "status": "final",
      "tanggal": "2025-05-17"
    }
  ]
}
```

---

## 🤝 PENITIPAN (CONSIGNMENT) ENDPOINTS

### **POST /penitipan/masuk**

Create titipan masuk

**Request:**

```json
{
  "id_gudang_penerima": 1,
  "id_supplier_pengirim": 2,
  "id_produk": 1,
  "qty": 500,
  "harga_kesepakatan": 65000,
  "komisi_persen": 5
}
```

**Response (201):**

```json
{
  "success": true,
  "data": {
    "id": 10,
    "status": "masuk",
    "qty": 500,
    "qty_terjual": 0
  }
}
```

### **POST /penitipan/:id/jual**

Record penjualan titipan

**Request:**

```json
{
  "qty_terjual": 300,
  "total_jual": 19500000,
  "penjual": "gudang_penerima"
}
```

**Response (201):**

```json
{
  "success": true,
  "data": {
    "komisi": 975000,
    "payment_method": "potong_langsung"
  }
}
```

---

## 🔄 RETUR ENDPOINTS

### **POST /retur/create**

Create retur

**Request:**

```json
{
  "id_gudang": 1,
  "id_produk": 1,
  "tipe_retur": "stok",
  "dari": "supplier",
  "qty": 10,
  "harga": 50000,
  "alasan": "Barang rusak",
  "catatan": "Pecah di perjalanan"
}
```

**Response (201):**

```json
{
  "success": true,
  "data": {
    "id": 5,
    "status": "pending"
  }
}
```

### **POST /retur/:id/approve**

Approve retur

**Response (200):**

```json
{
  "success": true,
  "message": "Retur disetujui",
  "data": {
    "status": "approved",
    "inventory_updated": true
  }
}
```

### **POST /retur/:id/reject**

Reject retur

**Response (200):**

```json
{
  "success": true,
  "message": "Retur ditolak"
}
```

---

## 💰 KEUANGAN (FINANCIAL) ENDPOINTS

### **GET /hutang-piutang/list**

List hutang/piutang

**Query:**

```
?id_gudang=1
&jenis=hutang (atau piutang)
&status=open
```

**Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "jenis": "hutang",
      "supplier": "Supplier A",
      "nominal": 1000000,
      "bayar": 300000,
      "sisa": 700000,
      "jatuh_tempo": "2025-06-17",
      "status": "sebagian"
    }
  ]
}
```

### **POST /hutang-piutang/bayar**

Record pembayaran hutang/piutang

**Request:**

```json
{
  "id_hutang_piutang": 1,
  "nominal_bayar": 300000,
  "tanggal_bayar": "2025-05-17",
  "bukti_file": "transfer_bukti.jpg"
}
```

**Response (201):**

```json
{
  "success": true,
  "data": {
    "sisa_hutang": 400000,
    "status": "sebagian"
  }
}
```

---

## 📊 LAPORAN (REPORTS) ENDPOINTS

### **GET /laporan/penjualan**

Laporan penjualan

**Query:**

```
?id_gudang=1
&date_from=2025-05-01
&date_to=2025-05-31
&group_by=hari (atau pembeli, produk)
```

**Response (200):**

```json
{
  "success": true,
  "data": {
    "periode": "2025-05-01 - 2025-05-31",
    "total_transaksi": 150,
    "total_qty": 5000,
    "total_revenue": 250000000,
    "total_cogs": 200000000,
    "gross_profit": 50000000,
    "margin": 0.2
  }
}
```

### **GET /laporan/stok**

Laporan stok per periode

**Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "produk": "Ikan A",
      "stok_awal": 200,
      "masuk": 500,
      "keluar": 300,
      "susut": 10,
      "stok_akhir": 390,
      "nilai": 19500000
    }
  ]
}
```

---

## ⚙️ SETTINGS ENDPOINTS

### **GET /settings**

Get all settings untuk current gudang

**Response (200):**

```json
{
  "success": true,
  "data": {
    "multi_warehouse_aktif": "0",
    "stok_minimum_threshold": "50",
    "komisi_penitipan_tipe": "potong",
    "pajak_default_persen": "10"
  }
}
```

### **PUT /settings/:kunci**

Update setting

**Request:**

```json
{
  "nilai": "60"
}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Setting diperbarui"
}
```

---

## 📄 EXPORT ENDPOINTS

### **POST /export/pdf**

Export ke PDF

**Request:**

```json
{
  "tipe": "nota",
  "id_nota": 100
}
```

**Response (200):**

```
Content-Type: application/pdf
Content-Disposition: attachment; filename="nota_PS-250517-001.pdf"
(Binary PDF data)
```

### **POST /export/excel**

Export ke Excel

**Request:**

```json
{
  "tipe": "laporan_penjualan",
  "date_from": "2025-05-01",
  "date_to": "2025-05-31"
}
```

**Response (200):**

```
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment; filename="laporan_penjualan.xlsx"
(Binary Excel data)
```

---

## 🔒 Error Response Format

**Semua endpoint error response:**

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": "Error detail"
  },
  "error_code": "VALIDATION_ERROR"
}
```

**HTTP Status:**

- 200: Success
- 201: Created
- 400: Bad request (validation)
- 401: Unauthorized
- 403: Forbidden
- 404: Not found
- 422: Unprocessable entity
- 500: Server error

---

## 🔐 Authentication Header

**All requests (except /auth/login):**

```
Authorization: Bearer <JWT_TOKEN>

OR (via HttpOnly Cookie)

Cookie: auth_token=<JWT_TOKEN>
```

---

**Next**: Baca `13-seeder.md` untuk initial data →
