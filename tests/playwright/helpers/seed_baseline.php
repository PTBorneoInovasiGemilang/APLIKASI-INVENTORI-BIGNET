<?php
/**
 * Seed baseline environment testing (idempotent).
 * Membuat (jika belum ada): 1 kategori, 1 satuan, 1 gudang, 2 item bulk ber-stok,
 * 1 item serialized dengan serial + stok. Kode mengikuti generator sistem
 * (KAT-/UNT-/GDG-/KAT-xxxxxx-xxxxxx). Dipakai spec yang butuh data nyata:
 * test_stock_master_e2e.spec.js & test_scan_barcode_e2e.spec.js.
 */
$config = require __DIR__ . '/../../../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}", $config['username'], $config['password']);

function nextCode($pdo, $table, $col, $prefix) {
    $stmt = $pdo->query("SELECT `$col` FROM `$table` WHERE `$col` LIKE '$prefix%' ORDER BY id DESC LIMIT 1 FOR UPDATE");
    $last = $stmt->fetchColumn();
    if ($last) {
        $n = (int)str_replace($prefix, '', $last) + 1;
        return $prefix . str_pad($n, 6, '0', STR_PAD_LEFT);
    }
    return $prefix . '000001';
}

$pdo->beginTransaction();

// Kategori baseline
$cat = $pdo->query("SELECT id FROM item_categories WHERE name = 'SYS-BASE-KATEGORI'")->fetchColumn();
if (!$cat) {
    $code = nextCode($pdo, 'item_categories', 'code', 'KAT-');
    $pdo->prepare("INSERT INTO item_categories (code, name, description, is_active) VALUES (?, 'SYS-BASE-KATEGORI', 'Data baseline testing', 1)")->execute([$code]);
    $cat = $pdo->lastInsertId();
}

// Satuan baseline
$unit = $pdo->query("SELECT id FROM units WHERE name = 'SYS-BASE-SATUAN'")->fetchColumn();
if (!$unit) {
    $code = nextCode($pdo, 'units', 'code', 'UNT-');
    $pdo->prepare("INSERT INTO units (code, name, description, is_active) VALUES (?, 'SYS-BASE-SATUAN', 'Data baseline testing', 1)")->execute([$code]);
    $unit = $pdo->lastInsertId();
}

// Gudang baseline
$wh = $pdo->query("SELECT id FROM warehouses WHERE name = 'SYS-BASE-GUDANG'")->fetchColumn();
if (!$wh) {
    $code = nextCode($pdo, 'warehouses', 'code', 'GDG-');
    $pdo->prepare("INSERT INTO warehouses (code, name, address, description, is_active) VALUES (?, 'SYS-BASE-GUDANG', NULL, 'Data baseline testing', 1)")->execute([$code]);
    $wh = $pdo->lastInsertId();
}

// Item bulk ber-stok (2 buah) + 1 serialized
function ensureItem($pdo, $cat, $unit, $name, $isSerialized) {
    $id = $pdo->query("SELECT id FROM master_items WHERE nama = '$name'")->fetchColumn();
    if ($id) return (int)$id;
    $catCode = $pdo->query("SELECT code FROM item_categories WHERE id = $cat")->fetchColumn();
    $stmt = $pdo->query("SELECT kode_barang FROM master_items WHERE kode_barang LIKE '$catCode-%' ORDER BY id DESC LIMIT 1 FOR UPDATE");
    $last = $stmt->fetchColumn();
    if ($last) { $n = (int)str_replace($catCode . '-', '', $last) + 1; }
    else { $n = 1; }
    $kode = $catCode . '-' . str_pad($n, 6, '0', STR_PAD_LEFT);
    $pdo->prepare("INSERT INTO master_items (kode_barang, nama, item_category_id, unit_id, is_serialized, stok_minimum, deskripsi, is_active) VALUES (?, ?, ?, ?, ?, 0, 'Data baseline testing', 1)")
        ->execute([$kode, $name, $cat, $unit, $isSerialized]);
    return (int)$pdo->lastInsertId();
}

$item1 = ensureItem($pdo, $cat, $unit, 'SYS-BASE-BARANG-1', 0);
$item2 = ensureItem($pdo, $cat, $unit, 'SYS-BASE-BARANG-2', 0);
$item3 = ensureItem($pdo, $cat, $unit, 'SYS-BASE-BARANG-SERIAL', 1);

// Stok: item1 = 45 (buat row stok), item2 = 10, item3 = 5 + 1 serial AVAILABLE
foreach ([[$item1, 45], [$item2, 10], [$item3, 5]] as [$iid, $qty]) {
    $has = $pdo->query("SELECT COUNT(*) FROM stocks WHERE master_item_id = $iid AND warehouse_id = $wh")->fetchColumn();
    if (!$has) {
        $pdo->prepare("INSERT INTO stocks (master_item_id, warehouse_id, quantity) VALUES (?, ?, ?)")->execute([$iid, $wh, $qty]);
    }
}
$hasSerial = $pdo->query("SELECT COUNT(*) FROM serial_numbers WHERE master_item_id = $item3")->fetchColumn();
if (!$hasSerial) {
    $pdo->prepare("INSERT INTO serial_numbers (serial_number, master_item_id, current_warehouse_id, status, condition_status) VALUES (?, ?, ?, 'AVAILABLE', 'BAIK')")
        ->execute(['SYS-BASE-SERIAL-' . time(), $item3, $wh]);
}

$pdo->commit();
echo "SEEDED cat=$cat unit=$unit wh=$wh item1=$item1 item2=$item2 item3=$item3\n";
