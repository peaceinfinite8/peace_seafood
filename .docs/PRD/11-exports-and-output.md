# 📄 EXPORT & OUTPUT — Peace Seafood

---

## 🎯 Export Overview

Peace Seafood support export dalam 2 format: **PDF** (untuk cetak) dan **Excel** (untuk analisis).

---

## 📑 PDF EXPORT

### **PDF Library: DomPDF 2.x**

```php
<?php
// Usage example
use Dompdf\Dompdf;

$dompdf = new Dompdf();
$html = file_get_contents('template/nota.html');
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('nota.pdf');
```

### **PDF Format Layouts**

#### **1. NOTA PENJUALAN**

```
┌─────────────────────────────────────────┐
│          PEACE SEAFOOD                   │
│          NOTA PENJUALAN                  │
├─────────────────────────────────────────┤
│ Gudang: Gudang A           Tanggal: 17/05/2025
│ Alamat: Jl. Merdeka No.1   No. Nota: PS-250517-001
├─────────────────────────────────────────┤
│ Pembeli: PT ABC Inc.                    │
│ Alamat: Jl. Sudirman No.5                │
├─────────────────────────────────────────┤
│ Item        | Qty   | Harga  | Subtotal│
├─────────────┼───────┼────────┼─────────┤
│ Ikan A      | 50 kg | 50.000 | 2.5M   │
│ Ikan B      | 30 kg | 60.000 | 1.8M   │
├─────────────────────────────────────────┤
│ Subtotal:                     Rp 4.3M  │
│ Diskon:                       Rp 200k  │
│ Pajak (10%):                  Rp 410k  │
├─────────────────────────────────────────┤
│ TOTAL:                        Rp 4.51M │
├─────────────────────────────────────────┤
│ Pembayaran: HUTANG (Jatuh Tempo: 17/06/2025)
│                                          │
│ ________________________                 │
│ Penerima,                                │
│ (Tanda tangan & cap)                    │
│                                          │
│ Dibuat oleh: Admin A                     │
│ Waktu: 17/05/2025 10:30 WIB              │
└─────────────────────────────────────────┘
```

**PDF Properties:**

- Header: Logo gudang, info gudang
- Body: Nota details, items table
- Footer: Tanda tangan, dibuat oleh, timestamp
- Watermark: "COPY" jika bukan asli

#### **2. LAPORAN PENJUALAN (Per Periode)**

```
┌─────────────────────────────────────────┐
│          PEACE SEAFOOD                   │
│      LAPORAN PENJUALAN                   │
│      PERIODE: 1-31 Mei 2025              │
│          GUDANG: Gudang A                │
├─────────────────────────────────────────┤
│ Tanggal | Pembeli | Item | Qty | Total│
├─────────┼─────────┼──────┼─────┼──────┤
│ 01/05   │ Pembeli A│Ikan A│50kg │2.5M │
│ 02/05   │ Pembeli B│Ikan B│30kg │1.8M │
│ ...     │...      │...  │... │...  │
├─────────────────────────────────────────┤
│ SUMMARY:                                 │
│ Total Transaksi: 150                     │
│ Total Qty: 5000 kg                       │
│ Total Revenue: Rp 250M                   │
│ Rata-rata Transaksi: Rp 1.67M            │
│ Top Pembeli: Pembeli A (50 transaksi)   │
│ Top Produk: Ikan A (2000 kg)             │
├─────────────────────────────────────────┤
│ Profit Analysis:                         │
│ Total Revenue: Rp 250M                   │
│ Total COGS: Rp 200M                      │
│ Gross Profit: Rp 50M                     │
│ Margin: 20%                              │
└─────────────────────────────────────────┘
```

#### **3. LAPORAN KEUANGAN (Hutang/Piutang)**

```
┌─────────────────────────────────────────┐
│          PEACE SEAFOOD                   │
│      LAPORAN HUTANG/PIUTANG              │
│      PERIODE: 1-31 Mei 2025              │
│          GUDANG: Gudang A                │
├─────────────────────────────────────────┤
│ HUTANG KE SUPPLIER:                     │
│ Supplier | Nominal | Bayar | Sisa | Jatuh Tempo
│ Supplier A│1M      │300k  │700k  │17/06/2025
│ Supplier B│2M      │0     │2M    │10/06/2025
│ ...      │...     │...   │...   │...
│ TOTAL HUTANG: Rp 5M                      │
├─────────────────────────────────────────┤
│ PIUTANG DARI PEMBELI:                   │
│ Pembeli | Nominal | Bayar | Sisa | Jatuh Tempo
│ Pembeli A│500k    │200k  │300k  │20/06/2025
│ Pembeli B│1.5M    │0     │1.5M  │15/06/2025
│ ...     │...     │...   │...   │...
│ TOTAL PIUTANG: Rp 8M                     │
├─────────────────────────────────────────┤
│ NET POSITION:                            │
│ Piutang: Rp 8M                           │
│ Hutang: Rp 5M                            │
│ Net Receivable: Rp 3M                    │
└─────────────────────────────────────────┘
```

### **HTML Template Usage**

```php
// File: src/views/pdf/nota_template.html

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f5f5f5; }
        .footer { text-align: center; margin-top: 40px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>PEACE SEAFOOD</h2>
        <h3>NOTA PENJUALAN</h3>
    </div>

    <table style="margin-top: 20px; width: 100%;">
        <tr>
            <td><strong>No. Nota:</strong> {{ nota.no_nota }}</td>
            <td style="text-align: right;"><strong>Tanggal:</strong> {{ nota.tanggal | format_date }}</td>
        </tr>
        <tr>
            <td><strong>Pembeli:</strong> {{ pembeli.nama }}</td>
            <td style="text-align: right;"><strong>Gudang:</strong> {{ gudang.nama }}</td>
        </tr>
    </table>

    <table class="table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Harga</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            {% for detail in nota_details %}
            <tr>
                <td>{{ detail.produk.nama }}</td>
                <td>{{ detail.qty }} kg</td>
                <td>Rp {{ detail.harga_jual | format_currency }}</td>
                <td style="text-align: right;">Rp {{ detail.subtotal | format_currency }}</td>
            </tr>
            {% endfor %}
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: right;">
        <strong>Subtotal:</strong> Rp {{ nota.subtotal | format_currency }}<br>
        <strong>Diskon:</strong> Rp {{ nota.diskon_nominal | format_currency }}<br>
        <strong>Pajak:</strong> Rp {{ nota.pajak | format_currency }}<br>
        <hr>
        <strong>TOTAL:</strong> Rp {{ nota.total | format_currency }}
    </div>

    <div class="footer">
        <p>Dibuat oleh: {{ created_by.name }} | Waktu: {{ created_at }}</p>
    </div>
</body>
</html>
```

---

## 📊 EXCEL EXPORT

### **Excel Library: PhpSpreadsheet 1.x**

```php
<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set header
$sheet->setCellValue('A1', 'No. Nota');
$sheet->setCellValue('B1', 'Tanggal');
// ... more columns

// Add data
$row = 2;
foreach ($notes as $nota) {
    $sheet->setCellValue('A' . $row, $nota['no_nota']);
    $sheet->setCellValue('B' . $row, $nota['tanggal']);
    // ... more data
    $row++;
}

// Auto-size columns
$sheet->getColumnDimension('A')->setAutoSize(true);

// Write file
$writer = new Xlsx($spreadsheet);
$writer->save('exports/laporan_penjualan.xlsx');
```

### **Excel Format Examples**

#### **1. LAPORAN PENJUALAN**

```
┌─────┬──────────┬──────────┬─────────┬────────┬──────────┐
│  A  │    B     │    C     │    D    │   E    │    F     │
├─────┼──────────┼──────────┼─────────┼────────┼──────────┤
│ No  │ Tanggal  │ Pembeli  │  Item   │ Qty(kg)│  Harga   │
├─────┼──────────┼──────────┼─────────┼────────┼──────────┤
│ 1   │01/05/2025│Pembeli A │Ikan A   │ 50     │ 2500000  │
│ 2   │02/05/2025│Pembeli B │Ikan B   │ 30     │ 1800000  │
│...  │...       │...       │...      │...     │...       │
└─────┴──────────┴──────────┴─────────┴────────┴──────────┘

Column Features:
- Date column: Format DD/MM/YYYY
- Currency column: Format Rp (automatic)
- Qty column: Numeric format
- Auto-sum row at bottom: =SUM(F2:F150)
```

#### **2. STOK REPORT**

```
Columns:
- A: Item Name
- B: Current Qty (kg)
- C: Min Threshold
- D: Status (Aman/Warning/Critical)
- E: Last Updated
- F: Movement (+ / -)

Features:
- Conditional formatting (color Red if status=Critical)
- Pivot table untuk summary by type
- Chart: Stok by jenis ikan (Pie chart)
```

#### **3. KEUANGAN REPORT**

```
Columns:
- A: Type (Hutang/Piutang)
- B: Partner (Supplier/Pembeli)
- C: Nominal
- D: Dibayar
- E: Sisa
- F: Jatuh Tempo
- G: Status (Open/Sebagian/Lunas)

Features:
- Pivot table: Total by type
- Chart: Aging analysis (Days outstanding)
- Conditional formatting untuk due date
```

---

## 📋 EXPORT PERMISSION

### **Per Setting: siapa_bisa_export**

```
DEFAULT: BOZ ONLY (secure)
├─ BOZ: Can export all warehouses
├─ ADMIN: Cannot export (unless setting changed)
└─ CHECKER: Cannot export (unless setting changed)

SETTABLE: BOZ & ADMIN
├─ BOZ: Can export all
├─ ADMIN: Can export own warehouse only
└─ CHECKER: Still cannot export

PERMISSIVE: SEMUA USER
├─ BOZ, ADMIN, CHECKER: All can export
```

---

## 🔒 EXPORT SECURITY

1. **Access Control**: Check user permission via middleware
2. **Data Filtering**: Query hanya data yang user authorized lihat
3. **Rate Limiting**: Max 10 export per user per jam (prevent abuse)
4. **Audit Log**: Catat setiap export (user, waktu, tipe laporan)
5. **Filename**: Berisi timestamp (prevent overwrite)

### **Example: Audit Log**

```sql
INSERT INTO export_audit_log (user_id, tipe_export, filename, created_at)
VALUES (1, 'nota_penjualan', 'laporan_penjualan_250517_101530.xlsx', NOW());
```

---

## 💾 EXPORT STORAGE

```
storage/exports/
├── pdf/
│   ├── nota_PS-250517-001_250517_101530.pdf
│   ├── laporan_penjualan_Mei2025_250531.pdf
│   └── ...
├── excel/
│   ├── laporan_penjualan_250517.xlsx
│   ├── laporan_keuangan_Mei2025.xlsx
│   └── ...
└── .gitkeep
```

**Cleanup Policy:**

- Auto-delete export files > 30 hari
- Keep database audit log untuk history
- Cron job: `php cli/cleanup-exports.php` (daily 02:00)

---

## ✅ Export Feature Checklist

- [ ] PDF format dengan header/footer
- [ ] Excel format dengan styling & formulas
- [ ] Responsive: Works mobile/desktop
- [ ] Permission check sebelum generate
- [ ] Filename include timestamp (prevent duplicate)
- [ ] Audit log untuk tracking
- [ ] Error handling jika data empty
- [ ] Multiple period filter (hari/bulan/tahun)
- [ ] Custom column select (user pilih column apa saja)
- [ ] Batch export (multiple reports sekaligus)

---

**Next**: Baca `12-api-endpoints.md` untuk backend API →
