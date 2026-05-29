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
        $scope = \App\Utils\Helper::buildGudangScope('sm', $idGudang, $allGudang);
        $where = $scope['where'];
        $params = $scope['params'];

        foreach (['dari' => 'DATE(sm.created_at) >=', 'sampai' => 'DATE(sm.created_at) <='] as $key => $field) {
            if (!empty($filters[$key])) {
                $where .= ' AND ' . $field . ' ?';
                $params[] = $filters[$key];
            }
        }

        $gudangCol = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
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

        $headers = [
            'Tanggal',
            'Supplier',
            'Produk',
            'Jenis Ikan',
            'Qty Teoritis',
            'Qty Actual',
            'Harga Beli',
            'Total',
            'Status'
        ];
        if ($allGudang && $idGudang === 0)
            $headers[] = 'Gudang';

        $rows = [];
        foreach ($data as $d) {
            $row = [
                date('d/m/Y H:i', strtotime($d['created_at'])),
                $d['supplier'],
                $d['produk'],
                $d['jenis_ikan'],
                $d['qty'] . ' kg',
                $d['qty_actual'] ? $d['qty_actual'] . ' kg' : '-',
                'Rp ' . number_format((float) $d['harga_beli'], 0, ',', '.'),
                'Rp ' . number_format((float) $d['total'], 0, ',', '.'),
                strtoupper($d['status']),
            ];
            if ($allGudang && $idGudang === 0)
                $row[] = $d['nama_gudang'] ?? '-';
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
        $scope = \App\Utils\Helper::buildGudangScope('n', $idGudang, $allGudang);
        $where = $scope['where'];
        $params = $scope['params'];

        foreach (['dari' => 'DATE(n.tanggal_nota) >=', 'sampai' => 'DATE(n.tanggal_nota) <=', 'status' => 'n.status ='] as $key => $field) {
            if (!empty($filters[$key])) {
                $where .= ' AND ' . $field . ' ?';
                $params[] = $filters[$key];
            }
        }

        $gudangCol = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
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

        $headers = [
            'No Nota',
            'Tanggal',
            'Pembeli',
            'Subtotal',
            'Diskon',
            'Pajak',
            'Total',
            'Pembayaran',
            'Status'
        ];
        if ($allGudang && $idGudang === 0)
            $headers[] = 'Gudang';

        $rows = [];
        foreach ($data as $d) {
            $row = [
                $d['no_nota'],
                date('d/m/Y', strtotime($d['tanggal_nota'])),
                $d['pembeli'] ?? '-',
                'Rp ' . number_format((float) $d['subtotal'], 0, ',', '.'),
                'Rp ' . number_format((float) $d['diskon'], 0, ',', '.'),
                'Rp ' . number_format((float) $d['pajak'], 0, ',', '.'),
                'Rp ' . number_format((float) $d['total'], 0, ',', '.'),
                strtoupper($d['jenis_pembayaran']),
                strtoupper($d['status']),
            ];
            if ($allGudang && $idGudang === 0)
                $row[] = $d['nama_gudang'] ?? '-';
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
        $scope = \App\Utils\Helper::buildGudangScope('hp', $idGudang, $allGudang);
        $where = $scope['where'];
        $params = $scope['params'];

        foreach (['jenis' => 'hp.jenis =', 'status' => 'hp.status =', 'dari' => 'DATE(hp.created_at) >=', 'sampai' => 'DATE(hp.created_at) <='] as $key => $field) {
            if (!empty($filters[$key])) {
                $where .= ' AND ' . $field . ' ?';
                $params[] = $filters[$key];
            }
        }

        $gudangCol = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
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
        if ($allGudang && $idGudang === 0)
            $headers[] = 'Gudang';

        $rows = [];
        foreach ($data as $d) {
            $row = [
                strtoupper($d['jenis']),
                $d['pihak'] ?? '-',
                'Rp ' . number_format((float) $d['nominal'], 0, ',', '.'),
                'Rp ' . number_format((float) $d['sisa_hutang'], 0, ',', '.'),
                $d['jatuh_tempo'] ? date('d/m/Y', strtotime($d['jatuh_tempo'])) : '-',
                strtoupper($d['status']),
                date('d/m/Y', strtotime($d['created_at'])),
            ];
            if ($allGudang && $idGudang === 0)
                $row[] = $d['nama_gudang'] ?? '-';
            $rows[] = $row;
        }

        return $this->exportToCsv($rows, $headers, 'keuangan_report');
    }

    /**
     * Export Laporan as CSV according to active tab.
     */
    public function exportLaporanCsv(string $dariTanggal, string $sampaiTanggal, string $tabActive, int $idGudang, bool $allGudang = false): string
    {
        $filters = ['dari' => $dariTanggal, 'sampai' => $sampaiTanggal];
        return match ($tabActive) {
            'stok' => $this->exportStokCsv($idGudang, $filters, $allGudang),
            'keuangan', 'aging' => $this->exportKeuanganCsv($idGudang, $filters, $allGudang),
            default => $this->exportPenjualanCsv($idGudang, $filters, $allGudang),
        };
    }

    /**
     * Export Laporan as Excel (.xlsx) premium according to active tab.
     */
    public function exportLaporanXlsx(string $dariTanggal, string $sampaiTanggal, string $tabActive, int $idGudang, bool $allGudang = false): string
    {
        $filters = ['dari' => $dariTanggal, 'sampai' => $sampaiTanggal];

        // 1. Fetch data depending on active tab
        if ($tabActive === 'stok') {
            $scope = \App\Utils\Helper::buildGudangScope('sm', $idGudang, $allGudang);
            $where = $scope['where'];
            $params = $scope['params'];

            foreach (['dari' => 'DATE(sm.created_at) >=', 'sampai' => 'DATE(sm.created_at) <='] as $key => $field) {
                if (!empty($filters[$key])) {
                    $where .= ' AND ' . $field . ' ?';
                    $params[] = $filters[$key];
                }
            }

            $gudangCol = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
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

            $headers = ['No', 'Tanggal', 'Supplier', 'Produk', 'Jenis Ikan', 'Qty Teoritis', 'Qty Aktual', 'Harga Beli', 'Total', 'Status'];
            if ($allGudang && $idGudang === 0) {
                $headers[] = 'Gudang';
            }
        } elseif ($tabActive === 'penjualan') {
            $scope = \App\Utils\Helper::buildGudangScope('n', $idGudang, $allGudang);
            $where = $scope['where'];
            $params = $scope['params'];

            foreach (['dari' => 'DATE(n.tanggal_nota) >=', 'sampai' => 'DATE(n.tanggal_nota) <='] as $key => $field) {
                if (!empty($filters[$key])) {
                    $where .= ' AND ' . $field . ' ?';
                    $params[] = $filters[$key];
                }
            }

            if (!empty($filters['status'])) {
                $where .= ' AND n.status = ?';
                $params[] = $filters['status'];
            }

            $gudangCol = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
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

            $headers = ['No', 'No Nota', 'Tanggal', 'Pembeli', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Pembayaran', 'Status'];
            if ($allGudang && $idGudang === 0) {
                $headers[] = 'Gudang';
            }
        } else { // keuangan or aging
            $scope = \App\Utils\Helper::buildGudangScope('hp', $idGudang, $allGudang);
            $where = $scope['where'];
            $params = $scope['params'];

            if ($tabActive === 'aging') {
                $where .= " AND hp.status != 'lunas' AND hp.status != 'cancelled'";
            }

            foreach (['dari' => 'DATE(hp.created_at) >=', 'sampai' => 'DATE(hp.created_at) <='] as $key => $field) {
                if (!empty($filters[$key])) {
                    $where .= ' AND ' . $field . ' ?';
                    $params[] = $filters[$key];
                }
            }

            $gudangCol = ($allGudang && $idGudang === 0) ? ", g.nama as nama_gudang" : "";
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

            $headers = ['No', 'Jenis', 'Pihak', 'Nominal', 'Sisa', 'Jatuh Tempo', 'Status', 'Tanggal'];
            if ($allGudang && $idGudang === 0) {
                $headers[] = 'Gudang';
            }
        }

        // Initialize Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ensure gridlines are visible
        $sheet->setShowGridlines(true);

        // Set font Arial globally
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Freeze Panes below table header (headers on Row 10, freeze below it on Row 11)
        $sheet->freezePane('A11');

        // Branding and Title block
        $titleText = match ($tabActive) {
            'stok' => 'Laporan Mutasi Stok Masuk',
            'penjualan' => 'Laporan Penjualan Produk',
            'keuangan' => 'Laporan Keuangan & Kas',
            'aging' => 'Laporan Analisis Hutang Aging',
            default => 'Laporan Peace Seafood',
        };

        $periodeStr = ($dariTanggal && $sampaiTanggal)
            ? date('d M Y', strtotime($dariTanggal)) . " s/d " . date('d M Y', strtotime($sampaiTanggal))
            : "Semua Periode";

        $gudangObj = $idGudang ? Database::fetchOne("SELECT * FROM gudang WHERE id = ?", [$idGudang]) : null;
        $gudangNama = $gudangObj ? $gudangObj['nama'] : 'Semua Gudang';

        // Table Header Column Letter (calculate early for merge)
        $lastCol = chr(ord('A') + count($headers) - 1);

        $sheet->setCellValue('A1', 'PEACE SEAFOOD');
        $sheet->setCellValue('A2', 'Supplier Ikan & Seafood Premium — Cold Storage Terintegrasi');
        $sheet->setCellValue('A3', strtoupper($titleText));
        $sheet->setCellValue('A4', 'Periode: ' . $periodeStr . '  |  Gudang: ' . $gudangNama . '  |  Dicetak: ' . date('d-m-Y H:i'));

        // Merge branding rows across full table width so they don't inflate Column A
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');
        $sheet->mergeCells('A4:' . $lastCol . '4');

        // Styles
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF4B5563'));
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
        $sheet->getStyle('A4')->getFont()->setSize(9.5)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF374151'));

        // Double bottom border for row 4 (branding line separator)
        $sheet->getStyle('A4:' . $lastCol . '4')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);
        $sheet->getStyle('A4:' . $lastCol . '4')->getBorders()->getBottom()->getColor()->setARGB('FF1E3A8A');

        // Helper function for rendering KPI Cards
        $renderKpiCard = function ($sheet, $startCol, $endCol, $title, $value, $numberFormat, $bgColorARGB) {
            $titleRow = 6;
            $valRow = 7;

            $sheet->mergeCells($startCol . $titleRow . ':' . $endCol . $titleRow);
            $sheet->mergeCells($startCol . $valRow . ':' . $endCol . $valRow);

            $sheet->setCellValue($startCol . $titleRow, $title);
            $sheet->setCellValue($startCol . $valRow, $value);

            if ($numberFormat) {
                $sheet->getStyle($startCol . $valRow)->getNumberFormat()->setFormatCode($numberFormat);
            }

            $sheet->getStyle($startCol . $titleRow)->getFont()->setBold(true)->setSize(8.5)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF4B5563'));
            $sheet->getStyle($startCol . $titleRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($startCol . $titleRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->getStyle($startCol . $valRow)->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
            $sheet->getStyle($startCol . $valRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($startCol . $valRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->getStyle($startCol . $titleRow . ':' . $endCol . $valRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB($bgColorARGB);

            $cardBorder = [
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FFE2E8F0'],
                    ],
                ],
            ];
            $sheet->getStyle($startCol . $titleRow . ':' . $endCol . $valRow)->applyFromArray($cardBorder);
        };

        // Helper function for soft badges
        $applySoftBadgeStyle = function ($sheet, $cell, $status) {
            $status = strtoupper(trim((string) $status));

            $bg = 'FFF1F5F9';
            $fg = 'FF64748B';
            $border = 'FFCBD5E1';

            if (in_array($status, ['CONFIRMED', 'FINAL', 'LUNAS', 'CASH', 'OK', 'AMAN'])) {
                $bg = 'FFDCFCE7';     // Soft Green
                $fg = 'FF15803D';     // Dark Green text
                $border = 'FFA7F3D0'; // Green border
            } elseif (in_array($status, ['PENDING', 'SOON', 'SEGERA', 'HUTANG', 'PIUTANG', 'TEMPO'])) {
                $bg = 'FFFEF3C7';     // Soft Yellow/Orange
                $fg = 'FFB45309';     // Dark Orange text
                $border = 'FFFCD34D'; // Yellow border
            } elseif (in_array($status, ['CANCEL', 'CANCELLED', 'OVERDUE', 'JATUH TEMPO', 'DEBT'])) {
                $bg = 'FFFDE8E8';     // Soft Red
                $fg = 'FFB91C1C';     // Dark Red text
                $border = 'FFF5C2C2'; // Red border
            }

            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB($bg);

            $sheet->getStyle($cell)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($fg))->setBold(true);

            $badgeBorder = [
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => $border],
                    ],
                ],
            ];
            $sheet->getStyle($cell)->applyFromArray($badgeBorder);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        };

        // Table Header
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '10', $h);
            $col++;
        }

        // Fill Data Rows
        $rowIdx = 11;
        $no = 1;

        $totalStyle = [
            'font' => [
                'bold' => true,
                'name' => 'Arial',
                'size' => 10,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE0F2FE'],
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF1E3A8A'],
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE,
                    'color' => ['argb' => 'FF1E3A8A'],
                ],
            ],
        ];

        if ($tabActive === 'stok') {
            // Pre-calculate KPIs (linked to active filters & data)
            $totalTeoritis = 0;
            $totalAktual = 0;
            $totalNominal = 0;

            foreach ($data as $item) {
                $qtyT = (float) $item['qty'];
                $qtyA = $item['qty_actual'] !== null ? (float) $item['qty_actual'] : $qtyT;
                $totalTeoritis += $qtyT;
                $totalAktual += $qtyA;
                $totalNominal += (float) $item['total'];
            }

            // Draw KPI Cards
            $renderKpiCard($sheet, 'B', 'C', 'TOTAL QTY TEORITIS', $totalTeoritis, '#,##0" kg"', 'FFE0F2FE');
            $renderKpiCard($sheet, 'E', 'F', 'TOTAL QTY AKTUAL', $totalAktual, '#,##0" kg"', 'FFDCFCE7');
            $renderKpiCard($sheet, 'H', 'I', 'TOTAL NILAI PEMBELIAN', $totalNominal, 'Rp#,##0;(Rp#,##0);"-"', 'FFE0E7FF');

            foreach ($data as $item) {
                $sheet->setCellValue('A' . $rowIdx, $no++);
                $sheet->setCellValue('B' . $rowIdx, date('d/m/Y H:i', strtotime($item['created_at'])));
                $sheet->setCellValue('C' . $rowIdx, $item['supplier']);
                $sheet->setCellValue('D' . $rowIdx, $item['produk']);
                $sheet->setCellValue('E' . $rowIdx, $item['jenis_ikan']);

                $qtyTeoritis = (float) $item['qty'];
                $sheet->setCellValue('F' . $rowIdx, $qtyTeoritis);
                $sheet->getStyle('F' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0" kg"');

                $qtyAktual = $item['qty_actual'] !== null ? (float) $item['qty_actual'] : $qtyTeoritis;
                $sheet->setCellValue('G' . $rowIdx, $item['qty_actual'] !== null ? $qtyAktual : '-');
                if ($item['qty_actual'] !== null) {
                    $sheet->getStyle('G' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0" kg"');
                }

                $hargaBeli = (float) $item['harga_beli'];
                $sheet->setCellValue('H' . $rowIdx, $hargaBeli);
                $sheet->getStyle('H' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');

                $total = (float) $item['total'];
                $sheet->setCellValue('I' . $rowIdx, $total);
                $sheet->getStyle('I' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');

                $status = strtoupper($item['status']);
                $sheet->setCellValue('J' . $rowIdx, $status);
                $applySoftBadgeStyle($sheet, 'J' . $rowIdx, $status);

                if ($allGudang && $idGudang === 0) {
                    $sheet->setCellValue('K' . $rowIdx, $item['nama_gudang'] ?? '-');
                }

                $rowIdx++;
            }

            // Total row
            $sheet->setCellValue('A' . $rowIdx, 'TOTAL');
            $sheet->mergeCells('A' . $rowIdx . ':E' . $rowIdx);
            $sheet->setCellValue('F' . $rowIdx, $totalTeoritis);
            $sheet->getStyle('F' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0" kg"');
            $sheet->setCellValue('G' . $rowIdx, $totalAktual);
            $sheet->getStyle('G' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0" kg"');
            $sheet->setCellValue('I' . $rowIdx, $totalNominal);
            $sheet->getStyle('I' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');

        } elseif ($tabActive === 'penjualan') {
            // Pre-calculate KPIs (linked to active filters & data)
            $totalSubtotal = 0;
            $totalDiskon = 0;
            $totalPajak = 0;
            $totalTotal = 0;
            $numTrans = count($data);

            foreach ($data as $item) {
                $totalSubtotal += (float) $item['subtotal'];
                $totalDiskon += (float) $item['diskon'];
                $totalPajak += (float) $item['pajak'];
                $totalTotal += (float) $item['total'];
            }

            // Draw KPI Cards
            $renderKpiCard($sheet, 'B', 'C', 'TOTAL PENJUALAN', $totalTotal, 'Rp#,##0;(Rp#,##0);"-"', 'FFDCFCE7');
            $renderKpiCard($sheet, 'E', 'F', 'TOTAL DISKON', $totalDiskon, 'Rp#,##0;(Rp#,##0);"-"', 'FFFDE8E8');
            $renderKpiCard($sheet, 'H', 'I', 'JUMLAH TRANSAKSI', $numTrans, '#,##0" Nota"', 'FFE0F2FE');

            foreach ($data as $item) {
                $sheet->setCellValue('A' . $rowIdx, $no++);
                $sheet->setCellValue('B' . $rowIdx, $item['no_nota']);
                $sheet->setCellValue('C' . $rowIdx, date('d/m/Y', strtotime($item['tanggal_nota'])));
                $sheet->setCellValue('D' . $rowIdx, $item['pembeli'] ?? 'Umum');

                $subtotal = (float) $item['subtotal'];
                $sheet->setCellValue('E' . $rowIdx, $subtotal);
                $sheet->getStyle('E' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');

                $diskon = (float) $item['diskon'];
                $sheet->setCellValue('F' . $rowIdx, $diskon);
                $sheet->getStyle('F' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');

                $pajak = (float) $item['pajak'];
                $sheet->setCellValue('G' . $rowIdx, $pajak);
                $sheet->getStyle('G' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');

                $total = (float) $item['total'];
                $sheet->setCellValue('H' . $rowIdx, $total);
                $sheet->getStyle('H' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');

                $pembayaran = strtoupper($item['jenis_pembayaran']);
                $sheet->setCellValue('I' . $rowIdx, $pembayaran);
                $applySoftBadgeStyle($sheet, 'I' . $rowIdx, $pembayaran);

                $status = strtoupper($item['status']);
                $sheet->setCellValue('J' . $rowIdx, $status);
                $applySoftBadgeStyle($sheet, 'J' . $rowIdx, $status);

                if ($allGudang && $idGudang === 0) {
                    $sheet->setCellValue('K' . $rowIdx, $item['nama_gudang'] ?? '-');
                }

                $rowIdx++;
            }

            // Total row
            $sheet->setCellValue('A' . $rowIdx, 'TOTAL');
            $sheet->mergeCells('A' . $rowIdx . ':D' . $rowIdx);
            $sheet->setCellValue('E' . $rowIdx, $totalSubtotal);
            $sheet->getStyle('E' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');
            $sheet->setCellValue('F' . $rowIdx, $totalDiskon);
            $sheet->getStyle('F' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');
            $sheet->setCellValue('G' . $rowIdx, $totalPajak);
            $sheet->getStyle('G' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');
            $sheet->setCellValue('H' . $rowIdx, $totalTotal);
            $sheet->getStyle('H' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');

        } else { // keuangan / aging
            // Pre-calculate KPIs (linked to active filters & data)
            $totalNominal = 0;
            $totalSisaHutang = 0;
            $totalSisaPiutang = 0;

            foreach ($data as $item) {
                $jenis = strtolower($item['jenis']);
                $nom = (float) $item['nominal'];
                $sisa = (float) $item['sisa_hutang'];
                $totalNominal += $nom;
                if ($jenis === 'hutang') {
                    $totalSisaHutang += $sisa;
                } else {
                    $totalSisaPiutang += $sisa;
                }
            }

            // Draw KPI Cards
            $renderKpiCard($sheet, 'B', 'C', 'TOTAL NOMINAL', $totalNominal, 'Rp#,##0;(Rp#,##0);"-"', 'FFE0F2FE');
            $renderKpiCard($sheet, 'E', 'F', 'TOTAL SISA HUTANG', $totalSisaHutang, 'Rp#,##0;(Rp#,##0);"-"', 'FFFDE8E8');
            $renderKpiCard($sheet, 'H', 'I', 'TOTAL SISA PIUTANG', $totalSisaPiutang, 'Rp#,##0;(Rp#,##0);"-"', 'FFDCFCE7');

            foreach ($data as $item) {
                $sheet->setCellValue('A' . $rowIdx, $no++);

                $jenis = strtoupper($item['jenis']);
                $sheet->setCellValue('B' . $rowIdx, $jenis);
                $applySoftBadgeStyle($sheet, 'B' . $rowIdx, $jenis);

                $sheet->setCellValue('C' . $rowIdx, $item['pihak'] ?? '-');

                $nominal = (float) $item['nominal'];
                $sheet->setCellValue('D' . $rowIdx, $nominal);
                $sheet->getStyle('D' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');

                $sisa = (float) $item['sisa_hutang'];
                $sheet->setCellValue('E' . $rowIdx, $sisa);
                $sheet->getStyle('E' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');

                $jatuhTempo = $item['jatuh_tempo'] ? date('d/m/Y', strtotime($item['jatuh_tempo'])) : '-';
                $sheet->setCellValue('F' . $rowIdx, $jatuhTempo);

                // For aging tab, status indicates the overdue/soon/ok status
                if ($tabActive === 'aging') {
                    $jt = $item['jatuh_tempo'];
                    if (!$jt) {
                        $status = 'AMAN';
                    } else {
                        $jtTime = strtotime($jt);
                        $today = strtotime(date('Y-m-d'));
                        $soonLimit = strtotime('+7 days');
                        if ($jtTime < $today) {
                            $status = 'JATUH TEMPO';
                        } elseif ($jtTime <= $soonLimit) {
                            $status = 'SEGERA';
                        } else {
                            $status = 'AMAN';
                        }
                    }
                } else {
                    $status = strtoupper($item['status']);
                }

                $sheet->setCellValue('G' . $rowIdx, $status);
                $applySoftBadgeStyle($sheet, 'G' . $rowIdx, $status);

                $sheet->setCellValue('H' . $rowIdx, date('d/m/Y', strtotime($item['created_at'])));

                if ($allGudang && $idGudang === 0) {
                    $sheet->setCellValue('I' . $rowIdx, $item['nama_gudang'] ?? '-');
                }

                $rowIdx++;
            }

            // Total row
            $sheet->setCellValue('A' . $rowIdx, 'TOTAL');
            $sheet->mergeCells('A' . $rowIdx . ':C' . $rowIdx);
            $sheet->setCellValue('D' . $rowIdx, $totalNominal);
            $sheet->getStyle('D' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');
            $sheet->setCellValue('E' . $rowIdx, $totalSisaHutang + $totalSisaPiutang);
            $sheet->getStyle('E' . $rowIdx)->getNumberFormat()->setFormatCode('Rp#,##0;(Rp#,##0);"-"');
        }

        // Apply thin borders to the entire data table range (headers to totals)
        $dataBorderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE2E8F0'],
                ],
            ],
        ];
        $sheet->getStyle('A10:' . $lastCol . $rowIdx)->applyFromArray($dataBorderStyle);

        // Header Styling (Row 10)
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'name' => 'Arial',
                'size' => 10,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF162D6E'],
                ],
            ],
        ];
        $sheet->getStyle('A10:' . $lastCol . '10')->applyFromArray($headerStyle);
        $sheet->getRowDimension(10)->setRowHeight(30);

        // Total Row Styling (Row $rowIdx)
        $sheet->getStyle('A' . $rowIdx . ':' . $lastCol . $rowIdx)->applyFromArray($totalStyle);
        $sheet->getStyle('A' . $rowIdx)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Alignments for standard data cells
        if ($tabActive === 'stok') {
            $sheet->getStyle('A11:A' . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B11:B' . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        } elseif ($tabActive === 'penjualan') {
            $sheet->getStyle('A11:A' . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C11:C' . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        } else {
            $sheet->getStyle('A11:A' . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F11:F' . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H11:H' . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Apply heights to data rows and total row
        $sheet->getRowDimension(5)->setRowHeight(10);
        $sheet->getRowDimension(6)->setRowHeight(16);
        $sheet->getRowDimension(7)->setRowHeight(26);
        $sheet->getRowDimension(8)->setRowHeight(10);
        $sheet->getRowDimension(9)->setRowHeight(10);
        for ($i = 11; $i <= $rowIdx; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(22);
        }

        // Auto-fit Column Widths with +5 safety padding
        // Column A (No) gets a fixed narrow width; remaining columns auto-fit
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth(7);
        foreach (range('B', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $sheet->calculateColumnWidths();
        foreach (range('B', $lastCol) as $columnID) {
            $colWidth = $sheet->getColumnDimension($columnID)->getWidth();
            $sheet->getColumnDimension($columnID)->setAutoSize(false);
            $sheet->getColumnDimension($columnID)->setWidth(max($colWidth + 5, 12));
        }

        // Save Spreadsheet
        $exportPath = BASE_PATH . '/storage/exports/';
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }

        $filepath = $exportPath . 'laporan_' . $tabActive . '_' . date('Ymd_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }

    /**
     * Export Laporan as PDF according to active tab using Dompdf.
     */
    public function exportLaporanPdf(string $dariTanggal, string $sampaiTanggal, string $tabActive, int $idGudang, bool $allGudang = false): string
    {
        $filters = ['dari' => $dariTanggal, 'sampai' => $sampaiTanggal];

        $gudang = $idGudang ? Database::fetchOne("SELECT * FROM gudang WHERE id = ?", [$idGudang]) : null;
        if (!$gudang)
            $gudang = ['nama' => 'Semua Gudang'];

        $data = match ($tabActive) {
            'stok' => (new StokService())->getHistory($idGudang, $filters, $allGudang),
            'keuangan', 'aging' => (new KeuanganService())->getHutangAging($idGudang, $allGudang),
            default => (new PenjualanService())->getNotaList($idGudang, $filters, $allGudang),
        };

        $html = $this->generateReportHtml($tabActive, $data, $gudang, $dariTanggal, $sampaiTanggal);

        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false
            ]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return $dompdf->output();
        }

        throw new \Exception("Library Dompdf tidak ditemukan.");
    }

    /**
     * Generate laporan HTML untuk PDF (via DomPDF)
     */
    public function generateReportHtml(string $tipe, array $data, array $gudang, string $dariTanggal = '', string $sampaiTanggal = ''): string
    {
        $title = match ($tipe) {
            'stok' => 'Laporan Mutasi Stok Masuk',
            'penjualan' => 'Laporan Penjualan Produk',
            'keuangan' => 'Laporan Keuangan & Kas',
            'aging' => 'Laporan Analisis Hutang Aging',
            default => 'Laporan Peace Seafood',
        };

        $periodeStr = ($dariTanggal && $sampaiTanggal)
            ? date('d M Y', strtotime($dariTanggal)) . " s/d " . date('d M Y', strtotime($sampaiTanggal))
            : "Semua Periode";

        $gudangNama = htmlspecialchars($gudang['nama'] ?? 'Semua Gudang');
        $cetakTgl = date('d-m-Y H:i');
        $totalRows = count($data);

        $html = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>{$title}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #222222;
            line-height: 1.5;
        }

        /* ===== HEADER ===== */
        .report-header {
            width: 100%;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 12px;
            margin-bottom: 8px;
        }
        .header-inner {
            width: 100%;
            border-collapse: collapse;
        }
        .header-inner td {
            vertical-align: top;
            padding: 0;
            border: none;
        }
        .brand-name {
            font-size: 20pt;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 1px;
            margin: 0 0 2px 0;
        }
        .brand-tagline {
            font-size: 8pt;
            color: #666666;
            margin: 0;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            text-align: right;
            margin: 0 0 4px 0;
        }
        .doc-meta {
            font-size: 8pt;
            color: #555555;
            text-align: right;
            margin: 0;
            line-height: 1.6;
        }

        /* ===== INFO BAR ===== */
        .info-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background-color: #f0f4fa;
            border: 1px solid #d0d7e3;
        }
        .info-bar td {
            padding: 6px 12px;
            font-size: 8pt;
            color: #444444;
            border: none;
        }
        .info-bar strong {
            color: #1e3a8a;
        }

        /* ===== DATA TABLE ===== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #162d6e;
        }
        .data-table th.col-right {
            text-align: right;
        }
        .data-table th.col-center {
            text-align: center;
        }
        .data-table td {
            padding: 7px 10px;
            font-size: 8.5pt;
            color: #333333;
            border-bottom: 1px solid #dde2ea;
            border-left: 1px solid #eef1f5;
            border-right: 1px solid #eef1f5;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f7f9fc;
        }
        .data-table tr:hover td {
            background-color: #eef2fb;
        }
        .col-right {
            text-align: right;
        }
        .col-center {
            text-align: center;
        }
        .col-num {
            text-align: center;
            color: #999999;
            font-size: 8pt;
            width: 30px;
        }
        .col-bold {
            font-weight: bold;
        }

        /* ===== FOOTER / TOTAL ===== */
        .total-row td {
            background-color: #1e3a8a !important;
            color: #ffffff !important;
            font-weight: bold;
            font-size: 9pt;
            padding: 9px 10px;
            border: 1px solid #162d6e;
        }

        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-weight: bold;
            font-size: 7pt;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            border: 1.5px solid transparent;
        }
        .badge-hutang {
            background-color: #fde8e8;
            color: #b91c1c;
            border-color: #f5c2c2;
        }
        .badge-piutang {
            background-color: #e0f2fe;
            color: #0369a1;
            border-color: #a5d8f8;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
            border-color: #a7f3d0;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #b45309;
            border-color: #fcd34d;
        }
        .badge-danger {
            background-color: #fde8e8;
            color: #b91c1c;
            border-color: #f5c2c2;
        }
        .badge-gray {
            background-color: #f1f5f9;
            color: #64748b;
            border-color: #cbd5e1;
        }

        .text-danger {
            color: #b91c1c;
        }
        .text-success {
            color: #15803d;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999999;
            font-size: 10pt;
            border: 1px dashed #cccccc;
        }

        /* ===== FOOTER NOTE ===== */
        .page-footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #dde2ea;
            font-size: 7pt;
            color: #999999;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class='report-header'>
        <table class='header-inner'>
            <tr>
                <td style='width: 55%;'>
                    <div class='brand-name'>PEACE SEAFOOD</div>
                    <p class='brand-tagline'>Supplier Ikan &amp; Seafood Premium &mdash; Cold Storage Terintegrasi</p>
                </td>
                <td style='width: 45%;'>
                    <div class='doc-title'>{$title}</div>
                    <p class='doc-meta'>
                        Periode: <strong>{$periodeStr}</strong>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- INFO BAR -->
    <table class='info-bar'>
        <tr>
            <td style='width: 40%;'>Gudang: <strong>{$gudangNama}</strong></td>
            <td style='width: 30%; text-align: center;'>Total Data: <strong>{$totalRows} baris</strong></td>
            <td style='width: 30%; text-align: right;'>Dicetak: <strong>{$cetakTgl}</strong></td>
        </tr>
    </table>
";

        if (empty($data)) {
            $html .= "<div class='empty-state'>Tidak ada data laporan untuk parameter filter yang dipilih.</div>";
            $html .= "<div class='page-footer'>Dokumen ini digenerate secara otomatis oleh sistem Peace Seafood.</div></body></html>";
            return $html;
        }

        // ==================== STOK REPORT ====================
        if ($tipe === 'stok') {
            $html .= "
    <table class='data-table'>
        <colgroup>
            <col style='width: 4%;'>
            <col style='width: 12%;'>
            <col style='width: 16%;'>
            <col style='width: 14%;'>
            <col style='width: 9%;'>
            <col style='width: 10%;'>
            <col style='width: 10%;'>
            <col style='width: 12%;'>
            <col style='width: 13%;'>
        </colgroup>
        <thead>
            <tr>
                <th class='col-center'>No</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Produk</th>
                <th>Jenis</th>
                <th class='col-right'>Qty Teoritis</th>
                <th class='col-right'>Qty Aktual</th>
                <th class='col-right'>Harga Beli</th>
                <th class='col-right'>Total</th>
            </tr>
        </thead>
        <tbody>";

            $totalTeoritis = 0.0;
            $totalAktual = 0.0;
            $totalNominal = 0.0;
            $no = 1;

            foreach ($data as $row) {
                $qty = (float) $row['qty'];
                $qty_act = $row['qty_actual'] !== null ? (float) $row['qty_actual'] : 0.0;
                $harga = (float) $row['harga_beli'];
                $total = (float) $row['total'];

                $totalTeoritis += $qty;
                $totalAktual += ($row['qty_actual'] !== null ? $qty_act : $qty);
                $totalNominal += $total;

                $tgl = date('d-m-Y', strtotime($row['created_at']));

                $html .= "
            <tr>
                <td class='col-num'>{$no}</td>
                <td>{$tgl}</td>
                <td>" . htmlspecialchars($row['nama_supplier']) . "</td>
                <td class='col-bold'>" . htmlspecialchars($row['nama_produk']) . "</td>
                <td>" . htmlspecialchars($row['nama_jenis'] ?? '-') . "</td>
                <td class='col-right'>" . number_format($qty, 2, ',', '.') . " kg</td>
                <td class='col-right'>" . ($row['qty_actual'] !== null ? number_format($qty_act, 2, ',', '.') . " kg" : "-") . "</td>
                <td class='col-right'>Rp " . number_format($harga, 0, ',', '.') . "</td>
                <td class='col-right col-bold'>Rp " . number_format($total, 0, ',', '.') . "</td>
            </tr>";
                $no++;
            }

            $html .= "
            <tr class='total-row'>
                <td colspan='5'>TOTAL REKAPITULASI (" . ($no - 1) . " TRANSAKSI)</td>
                <td class='col-right'>" . number_format($totalTeoritis, 2, ',', '.') . " kg</td>
                <td class='col-right'>" . number_format($totalAktual, 2, ',', '.') . " kg</td>
                <td></td>
                <td class='col-right'>Rp " . number_format($totalNominal, 0, ',', '.') . "</td>
            </tr>
        </tbody>
    </table>";

            // ==================== PENJUALAN REPORT ====================
        } elseif ($tipe === 'penjualan') {
            $html .= "
    <table class='data-table'>
        <colgroup>
            <col style='width: 4%;'>
            <col style='width: 14%;'>
            <col style='width: 11%;'>
            <col style='width: 17%;'>
            <col style='width: 13%;'>
            <col style='width: 11%;'>
            <col style='width: 14%;'>
            <col style='width: 8%;'>
            <col style='width: 8%;'>
        </colgroup>
        <thead>
            <tr>
                <th class='col-center'>No</th>
                <th>No Nota</th>
                <th>Tanggal</th>
                <th>Pembeli</th>
                <th class='col-right'>Subtotal</th>
                <th class='col-right'>Diskon</th>
                <th class='col-right'>Total Akhir</th>
                <th class='col-center'>Bayar</th>
                <th class='col-center'>Status</th>
            </tr>
        </thead>
        <tbody>";

            $totalSubtotal = 0.0;
            $totalDiskon = 0.0;
            $totalAkhir = 0.0;
            $no = 1;

            foreach ($data as $row) {
                $sub = (float) $row['subtotal'];
                $disc = (float) ($row['diskon_nominal'] ?? $row['diskon'] ?? 0.0);
                $total = (float) $row['total'];

                $totalSubtotal += $sub;
                $totalDiskon += $disc;
                $totalAkhir += $total;

                $tgl = date('d-m-Y', strtotime($row['tanggal_nota']));
                $payBadge = $row['pembayaran'] === 'cash' ? 'badge-success' : 'badge-warning';
                $statusBadge = $row['status'] === 'final' ? 'badge-success' : 'badge-gray';

                $html .= "
            <tr>
                <td class='col-num'>{$no}</td>
                <td style='font-family: Courier, monospace; font-size: 8pt;'>" . htmlspecialchars($row['no_nota']) . "</td>
                <td>{$tgl}</td>
                <td>" . htmlspecialchars($row['nama_pembeli'] ?? 'Umum') . "</td>
                <td class='col-right'>Rp " . number_format($sub, 0, ',', '.') . "</td>
                <td class='col-right text-danger'>" . ($disc > 0 ? "- Rp " . number_format($disc, 0, ',', '.') : "-") . "</td>
                <td class='col-right col-bold'>Rp " . number_format($total, 0, ',', '.') . "</td>
                <td class='col-center'><span class='badge {$payBadge}'>" . strtoupper($row['pembayaran']) . "</span></td>
                <td class='col-center'><span class='badge {$statusBadge}'>" . strtoupper($row['status']) . "</span></td>
            </tr>";
                $no++;
            }

            $html .= "
            <tr class='total-row'>
                <td colspan='4'>TOTAL PENJUALAN (" . ($no - 1) . " NOTA)</td>
                <td class='col-right'>Rp " . number_format($totalSubtotal, 0, ',', '.') . "</td>
                <td class='col-right'>- Rp " . number_format($totalDiskon, 0, ',', '.') . "</td>
                <td class='col-right'>Rp " . number_format($totalAkhir, 0, ',', '.') . "</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>";

            // ==================== KEUANGAN / AGING REPORT ====================
        } else {
            $html .= "
    <table class='data-table'>
        <colgroup>
            <col style='width: 4%;'>
            <col style='width: 9%;'>
            <col style='width: 22%;'>
            <col style='width: 15%;'>
            <col style='width: 15%;'>
            <col style='width: 12%;'>
            <col style='width: 12%;'>
            <col style='width: 11%;'>
        </colgroup>
        <thead>
            <tr>
                <th class='col-center'>No</th>
                <th class='col-center'>Jenis</th>
                <th>Pihak Mitra</th>
                <th class='col-right'>Nominal Awal</th>
                <th class='col-right'>Sisa Hutang</th>
                <th class='col-center'>Jatuh Tempo</th>
                <th class='col-center'>Status Aging</th>
                <th class='col-center'>Hari Sisa</th>
            </tr>
        </thead>
        <tbody>";

            $totalNominal = 0.0;
            $totalSisa = 0.0;
            $no = 1;

            foreach ($data as $row) {
                $nom = (float) $row['nominal'];
                $sisa = (float) $row['sisa_hutang'];
                $totalNominal += $nom;
                $totalSisa += $sisa;

                $jenisBadge = $row['jenis'] === 'hutang' ? 'badge-hutang' : 'badge-piutang';
                $jenisLabel = strtoupper($row['jenis']);
                $aging = $row['aging_status'] ?? 'ok';
                $agingBadge = match ($aging) {
                    'overdue' => 'badge-danger',
                    'soon' => 'badge-warning',
                    'no_due' => 'badge-gray',
                    default => 'badge-success'
                };
                $agingLabel = match ($aging) {
                    'overdue' => 'JATUH TEMPO',
                    'soon' => 'SEGERA',
                    'no_due' => 'NO DUE',
                    default => 'AMAN'
                };

                $jt = $row['jatuh_tempo'] ? date('d-m-Y', strtotime($row['jatuh_tempo'])) : '-';
                $pihak = $row['nama_supplier'] ?? $row['nama_pembeli'] ?? $row['pihak'] ?? '-';
                $hariRaw = $row['hari_jatuh_tempo'] ?? null;
                $hari = $hariRaw !== null ? ((int) $hariRaw . ' hari') : '-';

                $html .= "
            <tr>
                <td class='col-num'>{$no}</td>
                <td class='col-center'><span class='badge {$jenisBadge}'>{$jenisLabel}</span></td>
                <td class='col-bold'>" . htmlspecialchars($pihak) . "</td>
                <td class='col-right'>Rp " . number_format($nom, 0, ',', '.') . "</td>
                <td class='col-right col-bold text-danger'>Rp " . number_format($sisa, 0, ',', '.') . "</td>
                <td class='col-center'>{$jt}</td>
                <td class='col-center'><span class='badge {$agingBadge}'>{$agingLabel}</span></td>
                <td class='col-center'>{$hari}</td>
            </tr>";
                $no++;
            }

            $html .= "
            <tr class='total-row'>
                <td colspan='3'>TOTAL OUTSTANDING (" . ($no - 1) . " ITEM)</td>
                <td class='col-right'>Rp " . number_format($totalNominal, 0, ',', '.') . "</td>
                <td class='col-right'>Rp " . number_format($totalSisa, 0, ',', '.') . "</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>";
        }

        $html .= "
    <div class='page-footer'>
        Dokumen ini digenerate secara otomatis oleh sistem Peace Seafood &mdash; {$cetakTgl} &bull; Data bersifat rahasia dan hanya untuk keperluan internal.
    </div>
</body>
</html>";

        return $html;
    }

    /**
     * Export Nota Penjualan & Surat Jalan ke PDF (menggunakan Dompdf)
     */
    public function exportNotaPdf(int $idNota, int $idGudang, bool $allGudang = false): ?string
    {
        $penjualanService = new \App\Services\PenjualanService();
        $nota = $penjualanService->getNotaDetail($idNota, $idGudang, $allGudang);

        if (!$nota)
            return null;

        $noNota = htmlspecialchars($nota['no_nota']);
        $tanggalNota = date('d-m-Y', strtotime($nota['tanggal_nota']));
        $pembeli = htmlspecialchars($nota['nama_pembeli'] ?? 'Umum');
        $telepon = htmlspecialchars($nota['telepon_pembeli'] ?? '-');
        $alamat = htmlspecialchars($nota['alamat_pembeli'] ?? '-');
        $gudang = htmlspecialchars($nota['nama_gudang'] ?? '-');
        $pembayaran = strtoupper($nota['pembayaran']);
        $statusBayar = strtoupper($nota['status_pembayaran'] ?? 'OPEN');
        $kasir = htmlspecialchars($nota['nama_user'] ?? 'Staff');

        // Build HTML with highly elegant, modern, and premium styles
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Nota #{$noNota}</title>
            <style>
                @page {
                    margin: 20px;
                }
                body {
                    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                    font-size: 11px;
                    color: #1e293b;
                    line-height: 1.4;
                    background-color: #ffffff;
                }
                .container {
                    padding: 10px;
                }
                .header-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                .header-left {
                    width: 60%;
                    vertical-align: top;
                }
                .header-right {
                    width: 40%;
                    text-align: right;
                    vertical-align: top;
                }
                .brand-title {
                    font-size: 20px;
                    font-weight: 800;
                    color: #1e3a8a;
                    margin: 0;
                    letter-spacing: 0.5px;
                }
                .brand-subtitle {
                    font-size: 10px;
                    color: #64748b;
                    margin: 2px 0 0 0;
                }
                .doc-title {
                    font-size: 16px;
                    font-weight: 700;
                    color: #0f172a;
                    margin: 0;
                    text-transform: uppercase;
                }
                .doc-meta {
                    font-size: 11px;
                    font-family: monospace;
                    margin: 4px 0 0 0;
                    color: #475569;
                }
                .info-section {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                    background-color: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 6px;
                }
                .info-cell {
                    width: 50%;
                    padding: 10px 15px;
                    vertical-align: top;
                }
                .info-title {
                    font-weight: 700;
                    color: #475569;
                    margin-bottom: 6px;
                    text-transform: uppercase;
                    font-size: 9px;
                    letter-spacing: 0.5px;
                }
                .info-detail {
                    font-size: 11px;
                    color: #0f172a;
                }
                .item-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                .item-table th {
                    background-color: #1e3a8a;
                    color: #ffffff;
                    font-weight: 700;
                    text-transform: uppercase;
                    font-size: 9px;
                    padding: 8px 10px;
                    text-align: left;
                    border: 1px solid #1e3a8a;
                }
                .item-table td {
                    padding: 8px 10px;
                    border-bottom: 1px solid #e2e8f0;
                    color: #334155;
                }
                .item-table tr:nth-child(even) td {
                    background-color: #f8fafc;
                }
                .summary-table {
                    width: 40%;
                    float: right;
                    border-collapse: collapse;
                    margin-bottom: 25px;
                }
                .summary-table td {
                    padding: 5px 10px;
                    font-size: 11px;
                }
                .summary-title {
                    text-align: right;
                    color: #64748b;
                }
                .summary-value {
                    text-align: right;
                    font-weight: 700;
                    color: #0f172a;
                    width: 120px;
                }
                .total-row td {
                    border-top: 1.5px solid #0f172a;
                    font-size: 13px !important;
                    padding-top: 8px !important;
                }
                .total-value {
                    color: #1e3a8a !important;
                }
                .badge {
                    display: inline-block;
                    padding: 2px 6px;
                    font-weight: 700;
                    font-size: 9px;
                    border-radius: 4px;
                    text-transform: uppercase;
                }
                .badge-success { background-color: #dcfce7; color: #15803d; }
                .badge-warning { background-color: #fef9c3; color: #a16207; }
                .badge-gray { background-color: #f1f5f9; color: #475569; }
                .bank-info {
                    background-color: #f0fdf4;
                    border: 1px dashed #15803d;
                    border-radius: 4px;
                    padding: 8px 12px;
                    display: inline-block;
                    margin-top: 10px;
                }
                .bank-title {
                    font-weight: 700;
                    font-size: 9px;
                    color: #15803d;
                    text-transform: uppercase;
                    margin-bottom: 3px;
                }
                .signature-section {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 40px;
                    page-break-inside: avoid;
                }
                .signature-cell {
                    width: 50%;
                    text-align: center;
                    vertical-align: top;
                }
                .signature-title {
                    margin-bottom: 60px;
                    color: #475569;
                }
                .signature-line {
                    border-top: 1px solid #475569;
                    width: 150px;
                    margin: 0 auto;
                    padding-top: 4px;
                    font-weight: 700;
                    color: #0f172a;
                }
                .watermark {
                    position: absolute;
                    top: 35%;
                    left: 20%;
                    font-size: 80px;
                    font-weight: 800;
                    color: rgba(226, 232, 240, 0.3);
                    transform: rotate(-30deg);
                    z-index: -1000;
                    text-transform: uppercase;
                }
                .clearfix::after {
                    content: '';
                    clear: both;
                    display: table;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <!-- Watermark status -->
                <div class='watermark'>" . ($nota['status'] === 'cancel' ? 'CANCELLED' : $pembayaran) . "</div>

                <!-- Header -->
                <table class='header-table'>
                    <tr>
                        <td class='header-left'>
                            <h1 class='brand-title'>PEACE SEAFOOD</h1>
                            <p class='brand-subtitle'>Supplier Ikan & Seafood Premium Segar | Gudang: {$gudang}</p>
                        </td>
                        <td class='header-right'>
                            <h2 class='doc-title'>Nota Penjualan & Surat Jalan</h2>
                            <p class='doc-meta'>NO: {$noNota}</p>
                        </td>
                    </tr>
                </table>

                <!-- Info Section -->
                <table class='info-section'>
                    <tr>
                        <td class='info-cell' style='border-right: 1px solid #e2e8f0;'>
                            <div class='info-title'>Informasi Transaksi</div>
                            <table style='width: 100%; border: none;'>
                                <tr style='border: none;'><td style='width: 80px; padding: 2px 0; border: none; color: #64748b;'>Tanggal</td><td style='padding: 2px 0; border: none;'>: <strong>{$tanggalNota}</strong></td></tr>
                                <tr style='border: none;'><td style='padding: 2px 0; border: none; color: #64748b;'>Kasir</td><td style='padding: 2px 0; border: none;'>: {$kasir}</td></tr>
                                <tr style='border: none;'>
                                    <td style='padding: 2px 0; border: none; color: #64748b;'>Pembayaran</td>
                                    <td style='padding: 2px 0; border: none;'>: 
                                        <span class='badge badge-gray'>{$pembayaran}</span>
                                        <span class='badge " . ($statusBayar === 'LUNAS' ? 'badge-success' : 'badge-warning') . "'>{$statusBayar}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class='info-cell'>
                            <div class='info-title'>Tujuan Pengiriman / Pembeli</div>
                            <div class='info-detail'>
                                <strong>{$pembeli}</strong><br>
                                Telp: {$telepon}<br>
                                Alamat: {$alamat}
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Item Table -->
                <table class='item-table'>
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th style='text-align: right; width: 120px;'>Kuantitas</th>
                            <th style='text-align: right; width: 140px;'>Harga Satuan</th>
                            <th style='text-align: right; width: 140px;'>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>";

        $totalQty = 0;
        foreach ($nota['items'] as $item) {
            $namaProduk = htmlspecialchars($item['nama_produk']);
            $qty = (float) $item['qty'];
            $satuan = htmlspecialchars($item['satuan'] ?? 'kg');
            $hargaJual = (float) $item['harga_jual'];
            $subtotal = (float) $item['subtotal'];
            $totalQty += $qty;

            // Format weight
            $weightStr = number_format($qty, 2, ',', '.') . ' ' . $satuan;
            if ($satuan === 'kg') {
                if ($qty >= 10000) {
                    $weightStr = number_format($qty / 1000, 2, ',', '.') . ' ton';
                } elseif ($qty >= 100) {
                    $weightStr = number_format($qty / 100, 2, ',', '.') . ' kintal';
                }
            }

            $html .= "
                        <tr>
                            <td><strong>{$namaProduk}</strong></td>
                            <td style='text-align: right;'>{$weightStr}</td>
                            <td style='text-align: right;'>Rp " . number_format($hargaJual, 0, ',', '.') . "</td>
                            <td style='text-align: right; font-weight: 700;'>Rp " . number_format($subtotal, 0, ',', '.') . "</td>
                        </tr>";
        }

        $html .= "
                    </tbody>
                </table>

                <!-- Summary Section -->
                <div class='clearfix'>
                    <div style='width: 55%; float: left;'>";

        if (!empty($nota['catatan'])) {
            $html .= "<p style='margin: 0 0 10px 0; font-style: italic; color: #475569;'><strong>Catatan:</strong> " . htmlspecialchars($nota['catatan']) . "</p>";
        }

        // Info bank account if payment is transfer
        if ($nota['pembayaran'] === 'transfer' && !empty($nota['nama_bank'])) {
            $namaBank = htmlspecialchars($nota['nama_bank']);
            $accName = htmlspecialchars($nota['account_name']);
            $accNumber = htmlspecialchars($nota['account_number']);

            $html .= "
                        <div class='bank-info'>
                            <div class='bank-title'>Info Transfer Rekening:</div>
                            <strong>{$namaBank}</strong><br>
                            A/N: {$accName}<br>
                            No. Rekening: <strong>{$accNumber}</strong>
                        </div>";
        }

        $html .= "
                    </div>
                    <table class='summary-table'>
                        <tr>
                            <td class='summary-title'>Subtotal:</td>
                            <td class='summary-value'>Rp " . number_format((float) $nota['subtotal'], 0, ',', '.') . "</td>
                        </tr>";

        if ((float) $nota['diskon_nominal'] > 0) {
            $html .= "
                        <tr>
                            <td class='summary-title' style='color: #dc2626;'>Potongan Diskon:</td>
                            <td class='summary-value' style='color: #dc2626;'>- Rp " . number_format((float) $nota['diskon_nominal'], 0, ',', '.') . "</td>
                        </tr>";
        }

        if ((float) $nota['pajak'] > 0) {
            $html .= "
                        <tr>
                            <td class='summary-title'>Pajak:</td>
                            <td class='summary-value'>+ Rp " . number_format((float) $nota['pajak'], 0, ',', '.') . "</td>
                        </tr>";
        }

        $html .= "
                        <tr class='total-row'>
                            <td class='summary-title' style='font-weight: bold;'>TOTAL AKHIR:</td>
                            <td class='summary-value total-value'>Rp " . number_format((float) $nota['total'], 0, ',', '.') . "</td>
                        </tr>
                    </table>
                </div>

                <!-- Signature Section -->
                <table class='signature-section'>
                    <tr>
                        <td class='signature-cell'>
                            <div class='signature-title'>Tanda Terima Pembeli,</div>
                            <div class='signature-line' style='width: 160px;'>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                        </td>
                        <td class='signature-cell'>
                            <div class='signature-title'>Hormat Kami / Pengirim,</div>
                            <div class='signature-line' style='width: 160px;'>{$kasir}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>";

        // Generate PDF using Dompdf
        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false
            ]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        }

        return null;
    }
}
