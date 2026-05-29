<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Utils\Database;

function out($msg)
{
    echo $msg . PHP_EOL;
}

try {
    Database::beginTransaction();

    // Find AutoTest product IDs
    $products = Database::fetchAll("SELECT id FROM produk WHERE nama = ?", ['AutoTest Produk']);
    $prodIds = array_map(fn($r) => (int)$r['id'], $products);

    // Helper to build IN clause
    $inClause = function (array $ids) {
        if (empty($ids)) return '(NULL)';
        return '(' . implode(',', array_map('intval', $ids)) . ')';
    };

    out('Found product ids: ' . json_encode($prodIds));

    // Delete titipan_penjualan -> titipan
    if (!empty($prodIds)) {
        $titipanIds = Database::fetchAll("SELECT id FROM titipan WHERE id_produk IN " . $inClause($prodIds));
        $titipanIds = array_map(fn($r) => (int)$r['id'], $titipanIds);
        out('Found titipan ids: ' . json_encode($titipanIds));

        if (!empty($titipanIds)) {
            $ok = Database::execute("DELETE FROM titipan_penjualan WHERE id_titipan IN " . $inClause($titipanIds));
            out('Deleted titipan_penjualan: ' . ($ok ? 'ok' : 'none'));
            $ok = Database::execute("DELETE FROM titipan WHERE id IN " . $inClause($titipanIds));
            out('Deleted titipan: ' . ($ok ? 'ok' : 'none'));
        }

        // Delete nota_detail and nota related to these products
        $notaIds = Database::fetchAll("SELECT DISTINCT id_nota as id FROM nota_detail WHERE id_produk IN " . $inClause($prodIds));
        $notaIds = array_map(fn($r) => (int)$r['id'], $notaIds);
        out('Found nota ids: ' . json_encode($notaIds));
        if (!empty($notaIds)) {
            Database::execute("DELETE FROM nota_detail WHERE id_nota IN (" . implode(',', $notaIds) . ")");
            Database::execute("DELETE FROM nota WHERE id IN (" . implode(',', $notaIds) . ")");
            out('Deleted nota_detail and nota for product');
        }

        // Delete timbangan -> stok_masuk
        $stokMasukIds = Database::fetchAll("SELECT id FROM stok_masuk WHERE id_produk IN " . $inClause($prodIds));
        $stokMasukIds = array_map(fn($r) => (int)$r['id'], $stokMasukIds);
        out('Found stok_masuk ids: ' . json_encode($stokMasukIds));
        if (!empty($stokMasukIds)) {
            Database::execute("DELETE FROM timbangan WHERE id_stok_masuk IN (" . implode(',', $stokMasukIds) . ")");
            Database::execute("DELETE FROM stok_masuk WHERE id IN (" . implode(',', $stokMasukIds) . ")");
            out('Deleted timbangan and stok_masuk');
        }

        // Delete stok_opname_detail and stok_opname referencing these products (prevent FK)
        $opnameDetails = Database::fetchAll("SELECT id, id_stok_opname FROM stok_opname_detail WHERE id_produk IN " . $inClause($prodIds));
        $opnameIds = array_map(fn($r) => (int)$r['id_stok_opname'], $opnameDetails);
        $opnameIds = array_values(array_unique($opnameIds));
        out('Found stok_opname ids: ' . json_encode($opnameIds));
        if (!empty($opnameIds)) {
            Database::execute("DELETE FROM stok_opname_detail WHERE id_produk IN " . $inClause($prodIds));
            Database::execute("DELETE FROM stok_opname WHERE id IN (" . implode(',', $opnameIds) . ")");
            out('Deleted stok_opname_detail and stok_opname');
        }

        // Delete harga_history referencing products
        Database::execute("DELETE FROM harga_history WHERE id_produk IN " . $inClause($prodIds));
        out('Deleted harga_history entries for products');

        // Delete stok_transfer referencing products
        Database::execute("DELETE FROM stok_transfer WHERE id_produk IN " . $inClause($prodIds));
        out('Deleted stok_transfer entries for products');

        // Delete retur referencing products
        Database::execute("DELETE FROM retur WHERE id_produk IN " . $inClause($prodIds));
        out('Deleted retur entries for products');

        // Delete nota/journal related entries already handled

        // Delete activity_log entries related to AutoTest
        Database::execute("DELETE FROM activity_log WHERE (before_value LIKE '%AutoTest%' OR after_value LIKE '%AutoTest%')");
        out('Deleted activity_log entries referencing AutoTest');

        // Delete biaya_operasional and hutang_piutang entries with AutoTest marker
        Database::execute("DELETE FROM biaya_operasional WHERE deskripsi LIKE '%AutoTest%'");
        Database::execute("DELETE FROM hutang_piutang WHERE catatan LIKE '%AutoTest%'");
        out('Deleted biaya_operasional and hutang_piutang with AutoTest markers');

        // Finally delete produk
        Database::execute("DELETE FROM produk WHERE id IN " . $inClause($prodIds));
        out('Deleted produk');
    }

    // Delete suppliers and jenis ikan created by tests
    Database::execute("DELETE FROM supplier WHERE nama = ?", ['AutoTest Supplier']);
    out('Deleted supplier AutoTest Supplier');

    Database::execute("DELETE FROM jenis_ikan WHERE nama = ?", ['AutoTest Ikan']);
    out('Deleted jenis_ikan AutoTest Ikan');

    // Clean up any leftover activity_log, biaya_operasional, and hutang_piutang with AutoTest text
    Database::execute("DELETE FROM activity_log WHERE before_value LIKE '%AutoTest%' OR after_value LIKE '%AutoTest%'");
    Database::execute("DELETE FROM biaya_operasional WHERE deskripsi LIKE '%AutoTest%'");
    Database::execute("DELETE FROM hutang_piutang WHERE catatan LIKE '%AutoTest%'");
    out('Deleted leftover activity logs, operational costs, and debt/receivables containing AutoTest markers');

    Database::commit();
    out('Cleanup committed');
} catch (Throwable $e) {
    Database::rollBack();
    out('Cleanup failed: ' . $e->getMessage());
    exit(1);
}

out('DONE');
