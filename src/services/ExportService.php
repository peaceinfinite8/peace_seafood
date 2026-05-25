<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Database;

class ExportService
{
    /**
     * Export data ke CSV (fallback jika spreadsheet tidak tersedia)
     */
    public function exportToCsv(array $data, array $headers, string $filename): string
    {
        $exportPath = BASE_PATH . '/storage/exports/';
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }

        $filepath = $exportPath . $filename . '_' . date('Ymd_His') . '.csv';

        $fp = fopen($filepath, 'w');
        // BOM for UTF-8
        fputs($fp, "\xEF\xBB\xBF");
        // Headers
        fputcsv($fp, $headers, ';');
        // Data
        foreach ($data as $row) {
            fputcsv($fp, $row, ';');
        }
        fclose($fp);

        return $filepath;
    }

    /**
     * Export stok report ke CSV.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function exportStokCsv(int $idGudang, array $filters = [], bool $allGudang = false): string
    {
        if ($allGudang && $idGudang === 0) {
            $where  = "1=1";
            $params = [];
        } else {
            $where  = "sm.id_gudang = ?";
            $params = [$idGudang];
        }

        if (!empty($filters['dari'])) {
            $where   .= " AND DATE(sm.created_at) >= ?";
            $params[] = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where   .= " AND DATE(sm.created_at) <= ?";
            $params[] = $filters['sampai'];
        }

        $gudangCol  = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
        $gudangJoin = ($allGudang && $idGudang === 0) ? "LEFT JOIN gudang g ON sm.id_gudang = g.id" : "";

        $data = Database::fetchAll(
            "SELECT sm.created_at, s.nama as supplier, p.nama as produk,
                    ji.nama as jenis_ikan, sm.qty, t.qty_actual, sm.harga_beli,
                    sm.total, sm.status{$gudangCol}
             FROM stok_masuk sm
             JOIN supplier s ON sm.id_supplier = s.id
             JOIN produk p ON sm.id_produk = p.id
             JOIN jenis_ikan ji ON p.id_jenis_ikan = ji.id
             LEFT JOIN timbangan t ON t.id_stok_masuk = sm.id
             {$gudangJoin}
             WHERE {$where}
             ORDER BY sm.created_at DESC",
            $params
        );

        $headers = ['Tanggal', 'Supplier', 'Produk', 'Jenis Ikan',
                    'Qty Teoritis', 'Qty Actual', 'Harga Beli', 'Total', 'Status'];
        if ($allGudang && $idGudang === 0) $headers[] = 'Gudang';

        $rows = [];
        foreach ($data as $d) {
            $row = [
                date('d/m/Y H:i', strtotime($d['created_at'])),
                $d['supplier'],
                $d['produk'],
                $d['jenis_ikan'],
                $d['qty'] . ' kg',
                $d['qty_actual'] ? $d['qty_actual'] . ' kg' : '-',
                'Rp ' . number_format((float)$d['harga_beli'], 0, ',', '.'),
                'Rp ' . number_format((float)$d['total'], 0, ',', '.'),
                strtoupper($d['status']),
            ];
            if ($allGudang && $idGudang === 0) $row[] = $d['nama_gudang'] ?? '-';
            $rows[] = $row;
        }

        return $this->exportToCsv($rows, $headers, 'stok_report');
    }

    /**
     * Export penjualan report ke CSV.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function exportPenjualanCsv(int $idGudang, array $filters = [], bool $allGudang = false): string
    {
        if ($allGudang && $idGudang === 0) {
            $where  = "1=1";
            $params = [];
        } else {
            $where  = "n.id_gudang = ?";
            $params = [$idGudang];
        }

        if (!empty($filters['dari'])) {
            $where   .= " AND DATE(n.tanggal_nota) >= ?";
            $params[] = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where   .= " AND DATE(n.tanggal_nota) <= ?";
            $params[] = $filters['sampai'];
        }
        if (!empty($filters['status'])) {
            $where   .= " AND n.status = ?";
            $params[] = $filters['status'];
        }

        $gudangCol  = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
        $gudangJoin = ($allGudang && $idGudang === 0) ? "LEFT JOIN gudang g ON n.id_gudang = g.id" : "";

        $data = Database::fetchAll(
            "SELECT n.no_nota, n.tanggal_nota, p.nama as pembeli,
                    n.subtotal, n.diskon_nominal as diskon, n.pajak, n.total,
                    n.pembayaran as jenis_pembayaran, n.status{$gudangCol}
             FROM nota n
             LEFT JOIN pembeli p ON n.id_pembeli = p.id
             {$gudangJoin}
             WHERE {$where}
             ORDER BY n.tanggal_nota DESC",
            $params
        );

        $headers = ['No Nota', 'Tanggal', 'Pembeli',
                    'Subtotal', 'Diskon', 'Pajak', 'Total', 'Pembayaran', 'Status'];
        if ($allGudang && $idGudang === 0) $headers[] = 'Gudang';

        $rows = [];
        foreach ($data as $d) {
            $row = [
                $d['no_nota'],
                date('d/m/Y', strtotime($d['tanggal_nota'])),
                $d['pembeli'] ?? '-',
                'Rp ' . number_format((float)$d['subtotal'], 0, ',', '.'),
                'Rp ' . number_format((float)$d['diskon'], 0, ',', '.'),
                'Rp ' . number_format((float)$d['pajak'], 0, ',', '.'),
                'Rp ' . number_format((float)$d['total'], 0, ',', '.'),
                strtoupper($d['jenis_pembayaran']),
                strtoupper($d['status']),
            ];
            if ($allGudang && $idGudang === 0) $row[] = $d['nama_gudang'] ?? '-';
            $rows[] = $row;
        }

        return $this->exportToCsv($rows, $headers, 'penjualan_report');
    }

    /**
     * Export hutang piutang ke CSV.
     * Jika $allGudang = true, ambil semua gudang (untuk BOS).
     */
    public function exportKeuanganCsv(int $idGudang, array $filters = [], bool $allGudang = false): string
    {
        if ($allGudang && $idGudang === 0) {
            $where  = "1=1";
            $params = [];
        } else {
            $where  = "hp.id_gudang = ?";
            $params = [$idGudang];
        }

        if (!empty($filters['jenis'])) {
            $where   .= " AND hp.jenis = ?";
            $params[] = $filters['jenis'];
        }
        if (!empty($filters['status'])) {
            $where   .= " AND hp.status = ?";
            $params[] = $filters['status'];
        }

        $gudangCol  = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
        $gudangJoin = ($allGudang && $idGudang === 0) ? "LEFT JOIN gudang g ON hp.id_gudang = g.id" : "";

        $data = Database::fetchAll(
            "SELECT hp.jenis, COALESCE(s.nama, p.nama) as pihak,
                    hp.nominal, hp.sisa_hutang, hp.jatuh_tempo,
                    hp.status, hp.created_at{$gudangCol}
             FROM hutang_piutang hp
             LEFT JOIN supplier s ON hp.id_supplier = s.id
             LEFT JOIN pembeli p ON hp.id_pembeli = p.id
             {$gudangJoin}
             WHERE {$where}
             ORDER BY hp.jatuh_tempo ASC",
            $params
        );

        $headers = ['Jenis', 'Pihak', 'Nominal', 'Sisa', 'Jatuh Tempo', 'Status', 'Tanggal'];
        if ($allGudang && $idGudang === 0) $headers[] = 'Gudang';

        $rows = [];
        foreach ($data as $d) {
            $row = [
                strtoupper($d['jenis']),
                $d['pihak'] ?? '-',
                'Rp ' . number_format((float)$d['nominal'], 0, ',', '.'),
                'Rp ' . number_format((float)$d['sisa_hutang'], 0, ',', '.'),
                $d['jatuh_tempo'] ? date('d/m/Y', strtotime($d['jatuh_tempo'])) : '-',
                strtoupper($d['status']),
                date('d/m/Y', strtotime($d['created_at'])),
            ];
            if ($allGudang && $idGudang === 0) $row[] = $d['nama_gudang'] ?? '-';
            $rows[] = $row;
        }

        return $this->exportToCsv($rows, $headers, 'keuangan_report');
    }

    /**
     * Generate laporan HTML untuk PDF (via DomPDF)
     */
    public function generateReportHtml(string $tipe, array $data, array $gudang): string
    {
        $title = match($tipe) {
            'stok'      => 'Laporan Stok',
            'penjualan' => 'Laporan Penjualan',
            'keuangan'  => 'Laporan Keuangan',
            default     => 'Laporan Peace Seafood',
        };

        $html  = "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
        $html .= "<title>{$title}</title>";
        $html .= "<style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            h1 { color: #2563eb; }
            .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th { background: #2563eb; color: white; padding: 8px; text-align: left; }
            td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
            tr:nth-child(even) { background: #f8fafc; }
            .total-row { font-weight: bold; background: #dbeafe !important; }
        </style></head><body>";

        $html .= "<div class='header'>";
        $html .= "<h1>Peace Seafood</h1>";
        $html .= "<h2>{$title}</h2>";
        $html .= "<p>Gudang: {$gudang['nama']} | Dicetak: " . date('d/m/Y H:i') . "</p>";
        $html .= "</div>";

        // Render table based on type
        if (!empty($data)) {
            $html .= "<table>";
            $html .= "<thead><tr>";
            foreach (array_keys($data[0]) as $key) {
                $html .= "<th>" . ucfirst(str_replace('_', ' ', $key)) . "</th>";
            }
            $html .= "</tr></thead><tbody>";
            foreach ($data as $row) {
                $html .= "<tr>";
                foreach ($row as $val) {
                    $html .= "<td>" . htmlspecialchars((string)$val) . "</td>";
                }
                $html .= "</tr>";
            }
            $html .= "</tbody></table>";
        } else {
            $html .= "<p>Tidak ada data untuk periode ini.</p>";
        }

        return $html . "</body></html>";
    }
}
