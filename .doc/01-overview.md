# 🎯 PEACE SEAFOOD — PROJECT OVERVIEW

---

## 📋 Ringkasan Project

**Peace Seafood** adalah aplikasi web modern untuk manajemen gudang ikan dengan fitur lengkap mencakup:
- **Inventory Management** → stok masuk/keluar, penimbangan, susut
- **Sales Module** → penjualan retail & bulk dengan nota
- **Consignment** → penitipan barang dari gudang lain
- **Returns** → retur stok & retur piutang
- **Financial Management** → hutang/piutang, biaya operasional
- **Reports & Export** → PDF, Excel dengan custom format
- **Multi-warehouse** → support multiple gudang dengan opsi aktivasi
- **Role-based Access** → Bos, Admin, Checker dengan permission berbeda
- **Real-time Alerts** → stok menipis, hutang jatuh tempo, error system

---

## 🎭 Target User & Role

### **1. BOZ (Bos Gudang) - Pemilik**
- Lihat overview semua gudang (jika multi-gudang aktif)
- Dashboard ringkasan: stok, penjualan, hutang, profit
- Akses settings lengkap (konfigurasi sistem)
- Lihat laporan & export data
- **Permission**: Full access (owner level)

### **2. Admin - Operator**
- Manage stok (masuk/keluar, timbangan)
- Input nota penjualan
- Input hutang/piutang
- Manage supplier & pembeli
- Lihat dashboard operasional (stok, penjualan, hutang)
- **Assigned**: Per gudang spesifik
- **Permission**: Read/write operasional data

### **3. Checker - Pengawas Stok**
- Input timbangan & susut
- Check stok accuracy
- Lihat dashboard stok saja
- Report susut & anomali
- **Assigned**: Per gudang spesifik
- **Permission**: Read-only + timbangan input

---

## 🛠️ Modul Utama

### **1. Modul Stok**
- **Stok Masuk**: Dari supplier dengan detail (jenis, qty, harga)
- **Penimbangan**: Cek berat aktual vs teoritis, input susut
- **Adjustment**: Manual adjust stok jika ada error
- **History**: Trace semua perubahan stok

### **2. Modul Penjualan**
- **Create Nota**: Input item + qty + harga (auto-calculate subtotal)
- **Diskon**: Bisa per item atau per nota
- **Payment**: Langsung atau hutang
- **Print**: Generate nota fisik siap cetak
- **Cancel/Edit**: Bisa edit nota belum lock

### **3. Modul Penitipan**
- **Titip Masuk**: Terima ikan dari gudang lain dengan harga kesepakatan
- **Penjualan Titipan**: Jual ikan titipan (bisa oleh pengirim atau penerima)
- **Komisi**: Opsi potong langsung atau bayar terpisah
- **Settlement**: Laporan titipan + komisi per periode
- **Status Tracking**: Masuk → Dijual → Selesai

### **4. Modul Retur**
- **Retur Stok**: Kembalikan barang yang rusak/tidak sesuai
- **Retur Piutang**: Adjust hutang/piutang saat ada retur
- **Reason**: Catat alasan retur untuk analisis
- **Partial/Full**: Support retur sebagian atau full

### **5. Modul Keuangan**
- **Hutang/Piutang**: Track detail per supplier/pembeli
- **History Transaksi**: Catat setiap perubahan hutang/piutang
- **Pembayaran**: Input pembayaran hutang/piutang
- **Aging Report**: Laporan hutang by periode (jatuh tempo)

### **6. Modul Master Data**
- **Supplier**: Manage supplier dengan kontak & info bank
- **Pembeli**: Manage pembeli reguler
- **Jenis Ikan**: Manage tipe ikan yang dijual
- **Produk**: Link jenis ikan dengan harga & stok
- **Harga History**: Track perubahan harga per produk

### **7. Modul Laporan & Export**
- **Dashboard Reports**: Summary per periode (harian, mingguan, bulanan)
- **Stok Report**: Stok current + history movement
- **Sales Report**: Total penjualan, diskon, profit
- **Financial Report**: Hutang, piutang, cash flow
- **Export PDF**: Format profesional dengan logo & watermark
- **Export Excel**: Data detail untuk analisis lanjutan

---

## 📊 Dashboard per Role

### **BOZ (Pemilik)**
- Total overview semua gudang (dengan opsi switch gudang)
- Widget: Total stok value, penjualan hari/bulan, profit, hutang top 5
- Chart: Sales trend, inventory by jenis ikan, profit margin
- Settings button → access admin panel
- Multi-gudang selector (jika aktif)

### **Admin**
- Gudang spesifik overview
- Widget: Stok hari ini, penjualan hari ini, top suppliers, top buyers
- Chart: Inventory movement, sales by produk, buyer distribution
- Action buttons: Input stok, buat nota, input hutang

### **Checker**
- Stok dashboard only
- Widget: Stok current vs minimum, susut hari ini, anomali
- Chart: Stok by jenis ikan, susut trend
- Action button: Input timbangan

---

## ⚙️ Fitur Khusus

### **Multi Warehouse (Opsi)**
- Bos bisa punya lebih dari 1 gudang
- Jika aktif: Admin/Checker di-assign ke gudang spesifik
- Bos bisa lihat semua sekaligus atau switch per gudang
- Setting di Admin Panel

### **Harga Dinamis**
- Harga ikan bisa berubah kapan saja
- History harga tercatat otomatis
- Saat nota dibuat, gunakan harga current time (bukan history)
- Fitur set harga di Master Data atau modul Harga

### **Stok Minimum Alert**
- Setting threshold per jenis ikan
- Alert di dashboard jika stok < threshold
- Notifikasi in-app untuk admin
- Report susut dengan reason

### **Dark/Light Mode**
- Toggle di navbar
- CSS variable based system
- Preference tersimpan di localStorage
- Support PWA offline

### **PWA (Progressive Web App)**
- Install button di navbar
- Offline capability (cached data)
- Service worker untuk background sync
- Mobile responsive

---

## 🔄 Alur Umum Transaksi

### **Alur Stok Masuk**
```
1. Admin input stok masuk (dari supplier)
   - Pilih supplier
   - Input jenis ikan + qty + harga beli
   - Auto-calculate total

2. Checker timbang actual quantity
   - Input weight actual vs teoritis
   - Input alasan jika ada selisih
   - Susut tercatat otomatis

3. Stok masuk ke inventory
   - Inventory updated
   - Harga beli tersimpan untuk COGS
   - History tercatat
```

### **Alur Penjualan**
```
1. Admin create nota penjualan
   - Pilih pembeli
   - Pilih item dari inventory
   - Input qty + harga jual
   - Bisa add diskon
   - Calculate pajak (jika ada setting)

2. Admin save & generate nota fisik
   - Generate nota PDF
   - Payment status: Paid / Hutang

3. Stok update, hutang/piutang tercatat
   - Inventory -qty
   - Hutang/piutang update
   - History transaksi tersimpan
```

### **Alur Penitipan**
```
1. Admin input titipan masuk
   - Input supplier (gudang pengirim)
   - Input jenis ikan + qty + harga kesepakatan
   - Simpan sebagai "titipan" (bukan normal stok)

2. Penjualan titipan
   - Bisa dijual oleh pengirim atau penerima
   - Input di modul penjualan titipan
   - Generate nota titipan

3. Settlement komisi
   - Calculate komisi sesuai setting (potong/terpisah)
   - Update hutang pengirim jika komisi terpisah
   - Generate settlement report
```

### **Alur Retur**
```
1. Admin input retur
   - Dari supplier atau pembeli?
   - Input alasan retur
   - Input qty + item

2. Process sesuai jenis retur
   - Retur stok: kurangi inventory
   - Retur piutang: adjust hutang/piutang

3. History tersimpan
   - Retur tercatat per transaksi
   - Alasan & reason untuk analisis
```

---

## 📱 Localization & Format

| Aspek | Setting |
|---|---|
| **Bahasa** | Campur Indonesia + Inggris |
| **Timezone** | WIB (Asia/Jakarta) |
| **Format Tanggal** | DD/MM/YYYY |
| **Format Angka** | 1.000.000 (comma separator) |
| **Mata Uang** | IDR (Rp) |

---

## 🎨 Design System

- **Framework**: Tailwind CSS
- **Mode**: Dark/Light dengan CSS variable
- **Component Library**: Lucide Icons
- **Charts**: Chart.js
- **Interactivity**: Alpine.js
- **HTTP Client**: Axios
- **Color Scheme**: Customizable via CSS variable

---

## 🔐 Security & Performance

- **Authentication**: JWT (HttpOnly cookie)
- **CORS**: Configured untuk domain tertentu
- **Input Validation**: Server-side + client-side
- **File Upload**: MIME validation + size limit
- **Password**: Bcrypt hashing
- **Rate Limiting**: Anti brute-force
- **CSRF Protection**: Token verification

---

## 📈 Scalability

- **Multi Warehouse**: Support unlimited gudang
- **Multi User**: Concurrent user handling
- **Data Export**: Batch processing untuk large dataset
- **Caching**: Redis optional (future)
- **Database**: MySQL 8.0 with proper indexing

---

## ⏰ Timeline & Milestone

**Phase 1**: Database setup + Backend scaffold (2 minggu)
**Phase 2**: API endpoints core (stok, penjualan) (2 minggu)
**Phase 3**: Frontend core modules (3 minggu)
**Phase 4**: Advanced features (penitipan, retur, notifikasi) (2 minggu)
**Phase 5**: Testing, security, optimization (2 minggu)
**Phase 6**: Deployment & documentation (1 minggu)

---

## ✅ Success Criteria

- [ ] Semua modul berfungsi 100%
- [ ] Zero critical security issues
- [ ] Response time < 1 detik untuk page load
- [ ] Mobile responsive 100%
- [ ] PWA bisa install & offline capability
- [ ] Semua export berfungsi (PDF & Excel)
- [ ] Semua alert & notifikasi work
- [ ] Multi-warehouse tested thoroughly
- [ ] UAT passed dengan client
- [ ] Production ready

---

**Next**: Baca `04-user-roles.md` untuk detail permission →

