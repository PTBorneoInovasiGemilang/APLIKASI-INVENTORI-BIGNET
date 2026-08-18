<?php
/**
 * Helper state/cleanup untuk spec master-code.
 * Usage:
 *   php master_code_state.php get <table> <nameLike>   -> echoes "code1|code2|..." (empty jika tak ada)
 *   php master_code_state.php getid <table> <nameLike> -> echoes "id1|id2|..."
 *   php master_code_state.php cleanup <namePrefix>     -> hapus item/lokasi/lain yg mereferensikan master uji (FK-safe)
 */
$config = require __DIR__ . '/../../../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}", $config['username'], $config['password']);

$mode = $argv[1] ?? '';

if ($mode === 'get' || $mode === 'getid') {
    $table = $argv[2] ?? '';
    $like = $argv[3] ?? '';
    $col = $mode === 'getid' ? 'id' : 'code';
    $tableMap = ['units' => 'units', 'item_categories' => 'item_categories', 'warehouses' => 'warehouses', 'locations' => 'locations', 'master_items' => 'master_items'];
    if (!isset($tableMap[$table])) { echo 'BADTABLE'; exit; }
    $t = $tableMap[$table];
    $nameCol = ($t === 'master_items') ? 'nama' : 'name';
    if ($t === 'master_items' && $col === 'code') $col = 'kode_barang';
    $stmt = $pdo->prepare("SELECT `$col` FROM `$t` WHERE `$nameCol` = ? ORDER BY id ASC");
    $stmt->execute([$like]);
    echo implode('|', $stmt->fetchAll(PDO::FETCH_COLUMN));
} elseif ($mode === 'cleanup') {
    $prefix = $argv[2] ?? '';
    if ($prefix === '') { echo 'NOPREFIX'; exit; }
    $pdo->beginTransaction();
    // Item uji (nama prefix) + item lain yang mereferensikan kategori uji (residu spec lain)
    $itemIds = $pdo->query("SELECT id FROM master_items WHERE nama LIKE '$prefix%'")->fetchAll(PDO::FETCH_COLUMN);
    $catIds = $pdo->query("SELECT id FROM item_categories WHERE name LIKE '$prefix%'")->fetchAll(PDO::FETCH_COLUMN);
    if ($catIds) {
        $catIn = implode(',', array_map('intval', $catIds));
        $extra = $pdo->query("SELECT id FROM master_items WHERE item_category_id IN ($catIn)")->fetchAll(PDO::FETCH_COLUMN);
        $itemIds = array_values(array_unique(array_merge($itemIds, $extra)));
    }
    foreach ($itemIds as $iid) {
        $pdo->prepare("DELETE FROM master_item_attachments WHERE master_item_id = ?")->execute([$iid]);
        $pdo->prepare("DELETE FROM stocks WHERE master_item_id = ?")->execute([$iid]);
        $pdo->prepare("DELETE FROM serial_numbers WHERE master_item_id = ?")->execute([$iid]);
        $pdo->prepare("DELETE FROM stock_ledgers WHERE master_item_id = ?")->execute([$iid]);
        $pdo->prepare("DELETE FROM master_items WHERE id = ?")->execute([$iid]);
    }
    // Lokasi uji
    $locIds = $pdo->query("SELECT id FROM locations WHERE name LIKE '$prefix%'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($locIds as $lid) {
        $pdo->prepare("DELETE FROM serial_numbers WHERE current_location_id = ?")->execute([$lid]);
        $pdo->prepare("DELETE FROM locations WHERE id = ?")->execute([$lid]);
    }
    $pdo->exec("DELETE FROM item_categories WHERE name LIKE '$prefix%'");
    $pdo->exec("DELETE FROM units WHERE name LIKE '$prefix%'");
    $pdo->exec("DELETE FROM warehouses WHERE name LIKE '$prefix%'");
    $pdo->exec("DELETE FROM audit_logs WHERE entity = 'master_items' AND entity_id IN (" . (implode(',', $itemIds) ?: '0') . ")");
    $pdo->commit();
    echo 'CLEANED';
}
