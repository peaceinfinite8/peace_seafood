<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Helper to convert images to Base64 for seamless Dompdf rendering
function getBase64Image($path) {
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return '';
}

// Asset Paths
$assetDir = __DIR__ . '/../presentation_assets';
$imgWaterfall = getBase64Image($assetDir . '/01_dashboard_waterfall.png');
$imgGauge = getBase64Image($assetDir . '/02_cold_storage_gauge.png');
$imgChecker = getBase64Image($assetDir . '/03_checker_mobile.png');
$imgAlert = getBase64Image($assetDir . '/04_alert_krisis.png');
$imgTheme = getBase64Image($assetDir . '/05_light_vs_dark.png');

// HTML Content with high-end corporate styling
$html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PANDUAN OPERASIONAL & PENGENALAN SISTEM: PEACE SEAFOOD</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            line-height: 1.5;
            font-size: 10.5pt;
        }
        
        /* Cover Page styling */
        .cover {
            padding: 3cm 0 0 0;
            text-align: center;
            height: 100%;
        }
        .cover-logo-area {
            margin-bottom: 2cm;
        }
        .cover-title {
            font-size: 26pt;
            font-weight: bold;
            color: #0b2240; /* Dark Navy Marine */
            margin-bottom: 0.2cm;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .cover-subtitle {
            font-size: 14pt;
            color: #007a87; /* Sea Teal */
            margin-bottom: 2.5cm;
            font-weight: 300;
            font-style: italic;
        }
        .cover-details {
            margin-top: 3cm;
            font-size: 11pt;
            color: #555555;
            line-height: 1.8;
            border-top: 2px solid #007a87;
            padding-top: 1cm;
            display: inline-block;
            width: 80%;
        }
        .page-break {
            page-break-after: always;
        }
        
        /* Header & Footer */
        .section-header {
            border-bottom: 2px solid #0b2240;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .section-header h2 {
            font-size: 16pt;
            color: #0b2240;
            margin: 0;
            text-transform: uppercase;
        }
        
        /* General styling */
        h3 {
            font-size: 12pt;
            color: #007a87;
            margin-top: 15px;
            margin-bottom: 5px;
            border-left: 3px solid #007a87;
            padding-left: 8px;
        }
        p {
            margin-top: 0;
            margin-bottom: 12px;
            text-align: justify;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }
        th {
            background-color: #0b2240;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #0b2240;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #dddddd;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        
        /* Highlight box */
        .highlight-box {
            background-color: #f0f7f7;
            border-left: 4px solid #007a87;
            padding: 12px 15px;
            margin-bottom: 18px;
            border-radius: 0 4px 4px 0;
        }
        .highlight-box strong {
            color: #007a87;
        }
        
        .alert-box {
            background-color: #fff5f5;
            border-left: 4px solid #e53e3e;
            padding: 12px 15px;
            margin-bottom: 18px;
            border-radius: 0 4px 4px 0;
        }
        .alert-box strong {
            color: #e53e3e;
        }
        
        /* Image presentation layout */
        .image-container {
            text-align: center;
            margin: 20px 0;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 6px;
        }
        .image-container img {
            max-width: 100%;
            height: auto;
            max-height: 8.5cm;
            border: 1px solid #cbd5e1;
        }
        .image-caption {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 8px;
            font-style: italic;
        }
        
        .role-badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 3px;
            color: #ffffff;
        }
        .badge-bos { background-color: #e28743; }
        .badge-admin { background-color: #3b82f6; }
        .badge-checker { background-color: #10b981; }
        .badge-super { background-color: #6366f1; }
    </style>
</head>
<body>

    <!-- COVER PAGE -->
    <div class="cover">
        <div class="cover-logo-area">
            <span style="font-size: 45pt; color: #0b2240; font-weight: bold; letter-spacing: -2px;">🌊 PEACE SEAFOOD</span>
        </div>
        <div class="cover-title">Proposal Operasional &<br>Panduan Digitalisasi Sistem</div>
        <div class="cover-subtitle">"Mencegah Kebocoran Finansial, Meningkatkan Akurasi Timbangan, & Mengontrol Cold Storage dalam Genggaman"</div>
        
        <div class="cover-details">
            <strong>Disiapkan Untuk:</strong> Pemilik / Bos Gudang Ikan & Cold Storage<br>
            <strong>Tingkat Dokumen:</strong> Executive Summary & User Manual Resmi<br>
            <strong>Tanggal Rilis:</strong> Mei 2026<br>
            <strong>Diterbitkan Oleh:</strong> Tim Pengembang Sistem Informasi Peace Seafood<br>
            <strong>Status Sistem:</strong> Siap Uji Coba & Deploy Lokal
        </div>
    </div>
    
    <div class="page-break"></div>

    <!-- DAFTAR ISI & PENDAHULUAN -->
    <div class="section-header">
        <h2>1. Pendahuluan & Visi Digitalisasi</h2>
    </div>
    
    <p>
        Industri cold storage dan distribusi hasil laut merupakan sektor yang bergerak sangat cepat dengan risiko operasional yang tinggi. Selama bertahun-tahun, pencatatan di gudang ikan masih mengandalkan kertas papan jalan, papan tulis basah, dan kalkulator manual. Metode ini sangat lambat dan membuka <strong>celah kebocoran finansial yang besar</strong>, mulai dari selisih berat timbangan (fraud), piutang yang lupa ditagih, stok habis tak terduga, hingga lambatnya laporan laba/rugi bulanan.
    </p>
    
    <p>
        Aplikasi <strong>Peace Seafood</strong> hadir sebagai solusi digital satu pintu yang mengintegrasikan pencatatan stok fisik secara real-time, penguncian berat timbangan, manajemen penjualan kas/piutang, hingga dashboard keuangan eksekutif. Sistem ini dirancang kokoh namun tetap mudah dioperasikan, bahkan oleh petugas lapangan yang kurang terbiasa dengan teknologi.
    </p>
    
    <div class="highlight-box">
        <strong>Visi Kami:</strong> Menghilangkan ketergantungan kertas harian, mengunci angka berat barang masuk sejak di pintu timbangan, memberikan visualisasi kapasitas gudang tanpa harus masuk ke suhu beku, serta menyajikan laba bersih harian secara instan bagi pemilik gudang.
    </div>

    <div class="section-header" style="margin-top: 30px;">
        <h2>2. Sistem Pembagian Wewenang (Multi-Role)</h2>
    </div>
    <p>
        Demi keamanan data perusahaan, sistem membagi hak akses ke dalam 4 peran utama secara ketat. Di bawah ini adalah data akun uji coba bawaan (demo credentials) yang telah dikonfigurasi:
    </p>
    
    <table>
        <thead>
            <tr>
                <th style="width: 20%">Peran (Role)</th>
                <th style="width: 25%">Nama Akun</th>
                <th style="width: 35%">Email Login</th>
                <th style="width: 20%">Cakupan Akses</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="role-badge badge-bos">BOS</span></td>
                <td>Bos Gudang</td>
                <td><code>bos@example.com</code></td>
                <td>Semua Gudang (View-only)</td>
            </tr>
            <tr>
                <td><span class="role-badge badge-admin">ADMIN</span></td>
                <td>Admin Gudang A</td>
                <td><code>admin@example.com</code></td>
                <td>Gudang A (Input & Transaksi)</td>
            </tr>
            <tr>
                <td><span class="role-badge badge-checker">CHECKER</span></td>
                <td>Checker Gudang A</td>
                <td><code>checker@example.com</code></td>
                <td>Gudang A (Timbangan & Fisik)</td>
            </tr>
            <tr>
                <td><span class="role-badge badge-admin">ADMIN 2</span></td>
                <td>Admin Gudang B</td>
                <td><code>admin2@example.com</code></td>
                <td>Gudang B (Input & Transaksi)</td>
            </tr>
            <tr>
                <td><span class="role-badge badge-super">SUPER ADMIN</span></td>
                <td>Super Admin IT</td>
                <td><code>superadmin@example.com</code></td>
                <td>Seluruh Sistem (Kontrol Penuh)</td>
            </tr>
        </tbody>
    </table>
    
    <div class="page-break"></div>

    <!-- HAK AKSES DETIL -->
    <div class="section-header">
        <h2>3. Hak Akses Detail per Peran</h2>
    </div>
    
    <h3>👑 BOS (Pemilik Gudang)</h3>
    <p>
        Hak akses didesain bersifat <em>View-Only</em> untuk data operasional rumit, namun memiliki kendali keputusan penuh. Bos dapat memantau grafik finansial, melihat tren barang masuk/keluar di semua gudang, dan melakukan persetujuan (approval) retur stok rusak. Bos tidak direpotkan dengan tugas input data sehari-hari.
    </p>
    
    <h3>🖥️ ADMIN (Staf Kantor Administrasi)</h3>
    <p>
        Merupakan motor penggerak administrasi kantor. Admin bertugas membuat pengajuan rencana stok masuk (PO estimasi), menerbitkan nota penjualan kas maupun tempo (piutang), mengelola data titipan nelayan, menginput pengeluaran kas operasional harian, serta memperbarui master data supplier dan pembeli langganan.
    </p>
    
    <h3>📋 CHECKER (Petugas Timbangan Lapangan)</h3>
    <p>
        Dirancang khusus untuk pekerja lapangan di area gudang dingin yang basah. Checker menggunakan HP/Tablet untuk memproses timbangan fisik barang masuk secara riil dan menguncinya ke sistem. Checker juga berwewenang mengajukan retur fisik jika menemukan ikan berkualitas buruk saat pembongkaran muatan.
    </p>
    
    <h3>🔑 SUPER ADMIN (Tim IT / Sistem Pendukung)</h3>
    <p>
        Mengendalikan aspek teknis sistem, termasuk pendaftaran dan penonaktifan akun staf harian, konfigurasi harga dasar beli/jual produk ikan, melakukan database backup, dan menjalankan proses migrasi data dari sistem lama.
    </p>

    <div class="highlight-box" style="background-color: #fffcf0; border-left-color: #d97706;">
        <strong>💡 Keamanan Sistem:</strong> Semua sesi masuk dilindungi menggunakan sistem token berbasis JWT (JSON Web Token) dengan masa aktif 24 jam. Password pengguna dienkripsi dengan metode <em>bcrypt</em> standar industri perbankan demi keamanan data Anda.
    </div>
    
    <div class="page-break"></div>

    <!-- FITUR UNGGULAN & SCREENSHOTS 1 -->
    <div class="section-header">
        <h2>4. Fitur Utama & Visualisasi Dashboard</h2>
    </div>
    
    <h3>📈 Dashboard Keuangan Eksekutif (Waterfall Financial Chart)</h3>
    <p>
        Bos Gudang tidak perlu lagi menunggu laporan akhir bulan dari admin yang dihitung manual. Begitu kas harian tercatat dan penjualan selesai, sistem menyajikan grafik <strong>Waterfall</strong> interaktif yang merangkum laba/rugi bersih berjalan secara instan.
    </p>
    
    <div class="image-container">
        <img src="{$imgWaterfall}" alt="Waterfall Chart">
        <div class="image-caption">Gambar 4.1: Tampilan Dashboard Eksekutif Bos dengan Waterfall Chart Keuangan & Ringkasan Transaksi.</div>
    </div>
    
    <h3>❄️ Visualisasi Kapasitas Cold Storage (Gauge Capacity Monitor)</h3>
    <p>
        Guna mencegah penumpukan barang berlebih (*overcapacity*), kapasitas penyimpanan dingin dipantau dalam bentuk diagram visual setengah lingkaran (Gauge). Indikator ini langsung memperlihatkan batas aman muatan secara riil.
    </p>
    
    <div class="image-container">
        <img src="{$imgGauge}" alt="Gauge Cold Storage">
        <div class="image-caption">Gambar 4.2: Tampilan Visual Kapasitas Cold Storage (Terpakai vs Batas Aman kg).</div>
    </div>
    
    <div class="page-break"></div>

    <!-- FITUR UNGGULAN & SCREENSHOTS 2 -->
    <div class="section-header">
        <h2>5. Pengunci Timbangan & Tampilan Lapangan</h2>
    </div>
    
    <h3>🔒 Anti-Fraud Weight Tracker (Pengunci Timbangan Lapangan)</h3>
    <p>
        Ketika kiriman ikan dari supplier tiba, Checker lapangan menggunakan HP untuk menginput berat timbangan riil (*Qty Aktual*). Begitu tombol **Kunci Timbangan** ditekan, data terintegrasi ke stok gudang dan tidak dapat dimanipulasi lagi oleh staf kantor. Sistem secara otomatis menghitung selisih penyusutan barang.
    </p>
    
    <div class="image-container" style="max-height: 8.5cm;">
        <img src="{$imgChecker}" alt="Checker Mobile Screen" style="max-height: 7.5cm;">
        <div class="image-caption">Gambar 5.1: Tampilan Aplikasi Mobile untuk Checker Lapangan di area Timbangan Fisik.</div>
    </div>
    
    <h3>🚨 Sistem Peringatan Dini (Alerts & Krisis)</h3>
    <p>
        Sistem secara proaktif mendeteksi kondisi krisis, seperti stok komoditas ikan tertentu yang sudah berada di bawah batas aman minimum harian atau tagihan piutang pelanggan yang telah melewati batas tanggal jatuh tempo. Indikator menyala merah untuk penanganan cepat.
    </p>
    
    <div class="image-container" style="max-height: 5cm;">
        <img src="{$imgAlert}" alt="Alert Krisis" style="max-height: 4.5cm;">
        <div class="image-caption">Gambar 5.2: Indikator Peringatan Dini untuk Stok Menipis & Piutang Jatuh Tempo.</div>
    </div>
    
    <div class="page-break"></div>

    <!-- FITUR UNGGULAN & SCREENSHOTS 3 & ALUR KERJA -->
    <div class="section-header">
        <h2>6. Alur Kerja Operasional & Penutup</h2>
    </div>
    
    <h3>☀️/🌙 Fleksibilitas Tema: Dark & Light Mode</h3>
    <p>
        Untuk kenyamanan mata staf lapangan yang bekerja di dalam area *cold storage* yang cenderung remang-remang dan basah, sistem menyediakan tombol pengubah tema (Light & Dark Mode) yang sangat responsif di bagian atas menu.
    </p>
    
    <div class="image-container" style="max-height: 5.5cm;">
        <img src="{$imgTheme}" alt="Theme Comparison" style="max-height: 4.8cm;">
        <div class="image-caption">Gambar 6.1: Perbandingan Tampilan Antarmuka Tema Terang (Light) vs Tema Gelap (Dark Mode).</div>
    </div>
    
    <h3>🔄 Siklus Kerja Harian yang Direkomendasikan</h3>
    <ol>
        <li><strong>Inbound PO:</strong> Admin Kantor menginput estimasi berat kiriman ikan dari supplier.</li>
        <li><strong>Timbangan Fisik:</strong> Checker menimbang ikan di lokasi fisik gudang, menginput angka timbangan aktual, lalu mengunci data tersebut lewat HP.</li>
        <li><strong>Proses Penjualan:</strong> Admin memproses penjualan ke pelanggan melalui Nota Penjualan (cash / tempo).</li>
        <li><strong>Pencatatan Keuangan:</strong> Admin menginput pengeluaran operasional gudang dan penerimaan cicilan piutang.</li>
        <li><strong>Monitoring Owner:</strong> Bos memantau arus laba bersih serta pergerakan stok cold storage secara dinamis dan aman.</li>
    </ol>
    
    <div class="alert-box">
        <strong>PENTING:</strong> Sistem ini telah diuji coba secara lokal dan siap untuk diimplementasikan. Uji coba paralel (menggunakan kertas berdampingan dengan sistem digital) sangat disarankan selama 1-2 minggu pertama masa transisi agar karyawan terbiasa.
    </div>
    
    <p style="text-align: center; margin-top: 30px; font-weight: bold; color: #0b2240;">
        Peace Seafood © 2026. Hak Cipta Dilindungi Undang-Undang.<br>
        <span style="font-size: 9pt; font-weight: normal; color: #555555;">Keamanan Timbangan, Transparansi Keuangan, Efisiensi Cold Storage.</span>
    </p>

</body>
</html>
HTML;

// Initialize Dompdf with options
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('dpi', 120);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');

// Render the PDF
$dompdf->render();

// Output the PDF to file
$pdfOutput = $dompdf->output();
$targetFile = __DIR__ . '/../PANDUAN_PENGGUNAAN_PEACE_SEAFOOD.pdf';
file_put_contents($targetFile, $pdfOutput);

echo "SUCCESS: PDF generated successfully at: " . realpath($targetFile) . PHP_EOL;

