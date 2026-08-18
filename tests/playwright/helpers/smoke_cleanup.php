<?php
/**
 * Helper: bersihkan residu data dummy smoke test "SMK ..." / "DBG Cat ..."
 * Dipanggil dari spec Playwright (test idempotent). Hanya data uji dummy.
 */
$config = require __DIR__ . '/../../../config/database.php';

$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
    $config['username'],
    $config['password']
);

// Hapus items terlebih dahulu (FK master_items.item_category_id -> item_categories).
$pdo->exec("DELETE FROM stock_ledgers WHERE master_item_id IN (SELECT id FROM master_items WHERE nama LIKE 'SMK Item %')");
$pdo->exec("DELETE FROM stocks WHERE master_item_id IN (SELECT id FROM master_items WHERE nama LIKE 'SMK Item %')");
$pdo->exec("DELETE FROM master_items WHERE nama LIKE 'SMK Item %'");
$pdo->exec("DELETE FROM item_categories WHERE name LIKE 'SMK Cat %' OR name LIKE 'DBG Cat %'");
$pdo->exec("DELETE FROM units WHERE name LIKE 'SMK Unit %'");
$pdo->exec("DELETE FROM warehouses WHERE name LIKE 'SMK Wh %'");

echo "SMOKE-CLEANUP-OK\n";
