USE `peace_seafood`;

UPDATE `produk` SET `gambar` = 'kakap_merah.webp'      WHERE `nama` LIKE '%Kakap Merah%'  AND `nama` NOT LIKE '%Beku%';
UPDATE `produk` SET `gambar` = 'kakap_merah_beku.webp'  WHERE `nama` LIKE '%Kakap%'        AND `nama` LIKE '%Beku%';
UPDATE `produk` SET `gambar` = 'tenggiri.webp'          WHERE `nama` LIKE '%Tenggiri%';
UPDATE `produk` SET `gambar` = 'tuna.webp'              WHERE `nama` LIKE '%Tuna%';
UPDATE `produk` SET `gambar` = 'nila.webp'              WHERE `nama` LIKE '%Nila%';
UPDATE `produk` SET `gambar` = 'lele.webp'              WHERE `nama` LIKE '%Lele%';
UPDATE `produk` SET `gambar` = 'udang_windu.webp'       WHERE `nama` LIKE '%Udang Windu%';
UPDATE `produk` SET `gambar` = 'cumi.webp'              WHERE `nama` LIKE '%Cumi%';

SELECT id, nama, gambar FROM `produk` ORDER BY id;
