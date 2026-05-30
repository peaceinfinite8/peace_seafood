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

// HTML Content - Ultra-personalized with the "Mobil Matic vs Manual" analogy, sweet drinks, and good food theme!
$html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PANDUAN INTERNAL ALUR KERJA PEACE SEAFOOD</title>
    <style>
        @page {
            margin: 1.1cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.55;
            font-size: 11pt;
        }
        
        /* Cover Page styling */
        .cover {
            padding: 2.2cm 0 0 0;
            text-align: center;
            height: 100%;
        }
        .cover-emoji {
            font-size: 65pt;
            margin-bottom: 15px;
        }
        .cover-title {
            font-size: 26pt;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .cover-subtitle {
            font-size: 14pt;
            color: #0284c7;
            margin-bottom: 2.5cm;
            font-weight: bold;
            font-style: italic;
        }
        .cover-box {
            background-color: #f0fdf4;
            border: 2px dashed #16a34a;
            padding: 22px;
            border-radius: 12px;
            display: inline-block;
            width: 85%;
            font-size: 11.5pt;
            color: #334155;
            line-height: 1.8;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        /* Section Styling */
        .section-header {
            border-bottom: 3px solid #0284c7;
            padding-bottom: 5px;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .section-header h2 {
            font-size: 15pt;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }
        
        h3 {
            font-size: 12.5pt;
            color: #0284c7;
            margin-top: 15px;
            margin-bottom: 6px;
        }
        
        /* Stepper */
        .step-container {
            margin-bottom: 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .step-num {
            background-color: #0284c7;
            color: #ffffff;
            font-weight: bold;
            display: inline-block;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-size: 9.5pt;
            margin-right: 6px;
        }
        .step-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            display: inline;
        }
        .step-body {
            margin-top: 6px;
            padding-left: 30px;
            font-size: 10pt;
        }
        
        /* Analogies / Fun Notes */
        .analogi-box {
            background-color: #eff6ff;
            border-left: 5px solid #3b82f6;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 0 8px 8px 0;
            font-size: 10.5pt;
        }
        .analogi-box strong {
            color: #1d4ed8;
        }
        
        .dont-box {
            background-color: #fff5f5;
            border-left: 5px solid #ef4444;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 0 8px 8px 0;
            font-size: 10.5pt;
        }
        .dont-box strong {
            color: #b91c1c;
        }
        
        /* Screenshots */
        .ss-container {
            text-align: center;
            margin: 12px 0;
            background-color: #ffffff;
            border: 1px dashed #cbd5e1;
            padding: 6px;
            border-radius: 8px;
        }
        .ss-container img {
            max-width: 100%;
            height: auto;
            max-height: 7.2cm;
            border: 1px solid #e2e8f0;
        }
        .ss-caption {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 4px;
            font-weight: bold;
        }
        
        /* Roles Grid */
        .role-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 8px;
        }
        .role-title {
            font-weight: bold;
            font-size: 10.5pt;
            margin-bottom: 2px;
        }
        
    </style>
</head>
<body>

    <!-- COVER PAGE -->
    <div class="cover">
        <div class="cover-emoji">🚗🍹✨</div>
        <div class="cover-title">PANDUAN INTERNAL<br>ALUR KERJA PEACE SEAFOOD</div>
        <div class="cover-subtitle">"Dari Ribetnya Mobil Manual, Pindah ke Nyamannya Mobil Matic Tinggal Gas!"</div>
        
        <div class="cover-box">
            <strong>Dibuat Khusus Untuk Sahabat Terbaik:</strong><br>
            Sambil santai **nyruput minuman manis dingin** kesukaan lu, dan ngebayangin **makanan enak** buat makan siang nanti, yuk baca panduan singkat ini bentar. Dijamin langsung nyangkut di kepala, gak bikin pusing!
        </div>
    </div>
    
    <div class="page-break"></div>

    <!-- PENGANTAR & KONSEP MATIC VS MANUAL -->
    <div class="section-header">
        <h2>1. Kenapa Kita Ganti Sistem? (Analogi Mobil Matic)</h2>
    </div>
    
    <div class="analogi-box">
        <strong>🚗 Sistem Kerja Lama = Bawa Mobil Transmisi Manual di Tengah Macet Parah:</strong><br>
        Inget gak capeknya nyetir mobil manual pas macet? Kaki kiri pegel setengah mati injek kopling, tangan sibuk oper gigi 1, gigi 2, netral, mundur, kopling lagi. <br><br>
        Kerjaan kita yang dulu tuh kayak gitu, bro! Setiap hari timbangan dicatat di **kertas** (gampang basah/hilang), terus tiap malam kita harus lembur **mindahin data ke Excel secara manual**. Belum lagi pas si Bos mau pulang, dia harus nungguin kita mindahin semua kertas coret-coretan ke komputer. Capek, buang waktu, dan bikin kita telat nongkrong!
    </div>
    
    <div class="analogi-box" style="background-color: #f0fdf4; border-left: 5px solid #16a34a;">
        <strong>⚡ Sistem Baru (Peace Seafood) = Pindah ke Mobil Matic Mewah Tinggal Gas!</strong><br>
        Nah, sistem baru ini kayak lu **nyobain mobil matic pertama kali dan langsung jatuh cinta**! Tinggal masukin gigi ke **D (Drive)**, lepas rem, injek gas... langsung jalan mulus! Nggak ada lagi drama kaki kiri pegel injek kopling.<br><br>
        Di sistem ini, begitu orang gudang timbang ikan terus klik "Kunci" di HP-nya, dan staf kantor buat nota, **laporan Excel-nya langsung jadi otomatis saat itu juga!** Si Bos mau pulang? Ya tinggal pulang aja, gak usah nungguin kita mindahin kertas. Bos bisa santai lihat semua laporan lengkap lewat HP-nya dari rumah sambil tiduran.<br><br>
        <strong>Hasilnya?</strong> Kerja kita jadi super cepat! Kita bisa langsung pulang teng-go (tepat waktu) buat **jalan-jalan naik mobil, kulineran makanan enak, sambil nyruput boba manis dingin kesukaan lu tanpa kepikiran beban kerjaan!**
    </div>
    
    <div class="section-header" style="margin-top: 25px;">
        <h2>2. Siapa yang Nyetir Sistem Ini? (Pembagian Tugas)</h2>
    </div>
    
    <div class="role-card" style="border-left: 4px solid #e28743;">
        <div class="role-title" style="color: #e28743;">👑 Peran BOS (Akun: bos@example.com)</div>
        <div><strong>Tugasnya:</strong> Penonton santai di jok belakang. Tinggal rebahan lihat laba bersih hari ini dan kapasitas gudang dingin di HP-nya. Bos cuma perlu ngeklik "Approve" (Setuju) kalau ada pengajuan ikan retur dari kita.</div>
    </div>
    
    <div class="role-card" style="border-left: 4px solid #3b82f6;">
        <div class="role-title" style="color: #3b82f6;">🖥️ Peran ADMIN / Kantor (Akun: admin@example.com)</div>
        <div><strong>Tugasnya:</strong> Navigator kantor. Yang bikin rencana barang masuk, catat nota penjualan pas ada yang beli ikan, catat uang kas keluar buat operasional gudang, dan catat kalau ada pembeli bayar cicilan utang tempo.</div>
    </div>
    
    <div class="role-card" style="border-left: 4px solid #10b981;">
        <div class="role-title" style="color: #10b981;">📋 Peran CHECKER / Gudang (Akun: checker@example.com)</div>
        <div><strong>Tugasnya:</strong> Jagoan timbangan fisik. Pas kiriman ikan supplier dateng, dia timbang jujur di lapangan, buka HP, input berat aslinya di menu timbangan, lalu klik **Kunci Timbangan**. Tugasnya super simpel tapi vital!</div>
    </div>

    <div class="page-break"></div>

    <!-- ALUR 1: BARANG MASUK -->
    <div class="section-header">
        <h2>3. Alur 1: Proses Barang Masuk (Biar Timbangan Aman)</h2>
    </div>
    
    <p>Ini cara kerja otomatisnya pas kiriman ikan supplier dateng, gampang banget dibaca sambil minum manis:</p>
    
    <div class="step-container">
        <div class="step-num">1</div>
        <div class="step-title">Admin Kantor Bikin Rencana Pengiriman</div>
        <div class="step-body">
            Admin nanya supplier hari ini mau kirim ikan apa dan kira-kira berapa kg. Admin input data estimasi ini di komputer kantor.
        </div>
    </div>
    
    <div class="step-container">
        <div class="step-num">2</div>
        <div class="step-title">Truk Datang, Checker Timbang Fisik Ikan</div>
        <div class="step-body">
            Ikan dibongkar. Checker timbang keranjang ikan secara jujur dan teliti di timbangan lantai gudang.
        </div>
    </div>
    
    <div class="step-container">
        <div class="step-num">3</div>
        <div class="step-title">Checker Kunci Berat Asli di HP</div>
        <div class="step-body">
            Checker buka HP, masuk menu **Timbangan**, klik kiriman pending, ketik berat aslinya di kolom **Qty Aktual**, lalu klik tombol **Kunci Timbangan**. Selesai!
        </div>
    </div>
    
    <div class="dont-box">
        <strong>⚠️ PENTING BANGET:</strong><br>
        Sekali tombol **Kunci Timbangan** ditekan sama orang gudang, angkanya langsung masuk stok dan **TIDAK BISA DIUBAH LAGI** oleh staf kantor. Ini sistem pengunci biar gak ada kecurangan berat timbangan!
    </div>
    
    <div class="ss-container" style="max-height: 5.8cm;">
        <img src="{$imgChecker}" alt="Checker Screen" style="max-height: 5cm;">
        <div class="ss-caption">Gambar 3.1: Layar Timbangan HP Checker. Ketik berat riil lalu klik Kunci!</div>
    </div>
    
    <div class="page-break"></div>

    <!-- ALUR 2: JUALAN & KEUANGAN -->
    <div class="section-header">
        <h2>4. Alur 2: Proses Jualan & Kas Operasional (Excel Otomatis)</h2>
    </div>
    
    <p>Biar sore hari kita gak usah lembur mindahin kertas ke Excel harian, ini alur kerjanya:</p>
    
    <div class="step-container">
        <div class="step-num">1</div>
        <div class="step-title">Bikin Nota Penjualan di Komputer</div>
        <div class="step-body">
            Ada yang beli ikan? Admin tinggal buka menu **Penjualan ➔ Buat Nota**. Pilih pembeli, pilih ikan, beratnya berapa kg, lalu pilih bayar Cash atau Tempo. Klik **Finalisasi**. Stok ikan langsung berkurang sendiri secara otomatis!
        </div>
    </div>
    
    <div class="step-container">
        <div class="step-num">2</div>
        <div class="step-title">Catat Kas Keluar Detik Itu Juga</div>
        <div class="step-body">
            Lu beli es balokan? Beli solar truk? Kasih uang bensin kurir? Admin langsung input di menu **Keuangan ➔ Input Kas Keluar**. Jangan ditunda biar sore hari kas gak selisih!
        </div>
    </div>
    
    <div class="step-container">
        <div class="step-num">3</div>
        <div class="step-title">Laporan Excel & Laba Rugi Selesai Otomatis!</div>
        <div class="step-body">
            Karena semuanya diinput langsung, **sistem otomatis menyusun laporan Excel dan Laba Rugi saat itu juga**. Gak perlu mindahin data tiap malam! Bos bisa langsung pulang dengan tenang dan memantau grafiknya dari kasur rumah.
        </div>
    </div>
    
    <div class="ss-container" style="max-height: 4.8cm; margin-bottom: 5px;">
        <img src="{$imgWaterfall}" alt="Waterfall Laba Rugi" style="max-height: 4.1cm;">
        <div class="ss-caption">Gambar 4.1: Grafik Waterfall Keuangan (Naik = Keuntungan Masuk, Turun = Kas Keluar).</div>
    </div>
    
    <div class="ss-container" style="max-height: 4.8cm;">
        <img src="{$imgGauge}" alt="Gauge Cold Storage" style="max-height: 4.1cm;">
        <div class="ss-caption">Gambar 4.2: Diagram Kapasitas Cold Storage (Biar gak usah masuk dingin-dingin cuma buat cek sisa tempat).</div>
    </div>
    
    <div class="page-break"></div>

    <!-- CHEAT SHEET AKHIR -->
    <div class="section-header">
        <h2>5. Pertanyaan Cepat Pas Kerja (Cheat Sheet)</h2>
    </div>
    
    <div class="role-card" style="background-color: #fdf2f8; border: 1px solid #fbcfe8;">
        <span style="font-weight: bold; color: #db2777;">💡 Tips Cepat Sukses Kerja:</span><br>
        Kunci utama sistem matic ini adalah **"Disiplin Nyatet Harian"**. Begitu ada transaksi langsung input. Pas tutup buku jam 5 sore, kita tinggal matiin komputer terus langsung tancap gas pulang jalan-jalan! Gak pake lembur-lembur pusing kepala lagi!
    </div>
    
    <h3>❓ Q&A Santai:</h3>
    <p>
        <strong>Q: Kalau lupa password akun gimana?</strong><br>
        A: Kontak Super Admin (Tim IT), dalam 10 detik akun lu bakal di-reset biar bisa login lagi.
    </p>
    
    <p>
        <strong>Q: Ikan kiriman supplier ada yang busuk/hancur pas ditimbang. Harus apa?</strong><br>
        A: Checker langsung klik menu **Retur ➔ Buat Retur** di HP, ketik ikan apa yang rusak dan berapa kg, kasih alasannya. Begitu dikirim, HP si Bos bakal bunyi. Bos tinggal klik **Approve (Setuju)**, beres!
    </p>
    
    <p>
        <strong>Q: Kenapa warna layarnya hitam gelap?</strong><br>
        A: Itu nama fitur-nya **Dark Mode**. Bagus banget kalau lu lagi di gudang es yang cahayanya minim biar mata gak capek dan silau. Kalau mau ganti warna putih, tinggal klik gambar matahari ☀️ di kanan atas layar.
    </p>
    
    <p>
        <strong>Q: Ada kotak warna merah menyala di dashboard, itu apa?</strong><br>
        A: Itu alarm otomatis! Ngingetin kalau ada stok ikan laris kita yang mau abis, atau ada pembeli nakal yang utang temponya udah lewat hari tapi belum bayar. Biar kita langsung gercep nagih!
    </p>
    
    <p style="text-align: center; margin-top: 40px; font-weight: bold; color: #0284c7; font-size: 13pt;">
        Nyetir Matic Lebih Nyaman, Kerja Praktis Gak Pake Lembur! 🚗🍹🌊<br>
        <span style="font-size: 9.5pt; font-weight: normal; color: #64748b;">Peace Seafood - Panduan Kerja Internal Sahabat © 2026.</span>
    </p>

</body>
</html>
HTML;

// Initialize Dompdf with custom simple layout options
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
$targetFile = __DIR__ . '/../PANDUAN_INTERNAL_MATIC.pdf';
file_put_contents($targetFile, $pdfOutput);

echo "SUCCESS: Panduan Internal PDF updated successfully at: " . realpath($targetFile) . PHP_EOL;

