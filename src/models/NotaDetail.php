<?php

declare(strict_types=1);

namespace App\Models;

use App\Utils\Database;

class NotaDetail extends Model
{
    protected static string $table = 'nota_detail';

    public static function getByNota(int $idNota): array
    {
        return Database::fetchAll(
            "SELECT nd.*, p.nama as produk_nama, p.harga_beli,
                    j.nama as jenis_ikan_nama
             FROM nota_detail nd
             JOIN produk p ON p.id = nd.id_produk
             JOIN jenis_ikan j ON j.id = p.id_jenis_ikan
             WHERE nd.id_nota = ?",
            [$idNota]
        );
    }

    public static function insertBulk(int $idNota, array $items): void
    {
        foreach ($items as $item) {
            Database::execute(
                "INSERT INTO nota_detail (id_nota, id_produk, qty, harga_jual, subtotal)
                 VALUES (?, ?, ?, ?, ?)",
                [
                    $idNota,
                    $item['id_produk'],
                    $item['qty'],
                    $item['harga_jual'],
                    $item['qty'] * $item['harga_jual'],
                ]
            );
        }
    }

    public static function deleteByNota(int $idNota): bool
    {
        return Database::execute(
            "DELETE FROM nota_detail WHERE id_nota = ?",
            [$idNota]
        );
    }
}
