<?php
// Usage:
//   php item_state.php get <kode>          -> id|is_active|used  (kosong jika tidak ditemukan)
//   php item_state.php stock <id>          -> total stocks.quantity
//   php item_state.php cleanup <id>        -> hapus item uji + relasi transaksi uji
$pdo = new PDO('mysql:host=127.0.0.1;dbname=db_gudang_tommy;charset=utf8mb4', 'root', '');
$mode = $argv[1] ?? '';

function usedCount($pdo, $id) {
    $id = (int)$id;
    return (int)$pdo->query("SELECT COUNT(*) FROM (
        SELECT master_item_id FROM stocks WHERE master_item_id = $id AND quantity > 0
        UNION SELECT master_item_id FROM serial_numbers WHERE master_item_id = $id
        UNION SELECT master_item_id FROM incoming_items WHERE master_item_id = $id
        UNION SELECT master_item_id FROM outgoing_items WHERE master_item_id = $id
        UNION SELECT master_item_id FROM transfer_items WHERE master_item_id = $id
        UNION SELECT master_item_id FROM return_items WHERE master_item_id = $id
        UNION SELECT master_item_id FROM loan_items WHERE master_item_id = $id
        UNION SELECT master_item_id FROM installation_items WHERE master_item_id = $id
        UNION SELECT master_item_id FROM stock_adjustment_items WHERE master_item_id = $id
        UNION SELECT master_item_id FROM submission_items WHERE master_item_id = $id
        UNION SELECT master_item_id FROM stock_ledgers WHERE master_item_id = $id
    ) as t")->fetchColumn();
}

if ($mode === 'get') {
    $kode = $argv[2] ?? '';
    $stmt = $pdo->prepare("SELECT id, is_active FROM master_items WHERE kode_barang = ? LIMIT 1");
    $stmt->execute([$kode]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) { echo ''; exit; }
    echo $u['id'] . '|' . $u['is_active'] . '|' . usedCount($pdo, $u['id']);
} elseif ($mode === 'add_serial') {
    // Tambah serial AVAILABLE untuk item uji: php item_state.php add_serial <itemId> <serial> <warehouseId>
    $itemId = (int)($argv[2] ?? 0);
    $serial = $argv[3] ?? '';
    $whId = (int)($argv[4] ?? 0);
    if ($itemId <= 0 || $serial === '') { echo 'bad'; exit; }
    $stmt = $pdo->prepare("INSERT INTO serial_numbers (serial_number, master_item_id, current_warehouse_id, status, condition_status) VALUES (?, ?, ?, 'AVAILABLE', 'BAIK')");
    $stmt->execute([$serial, $itemId, $whId ?: null]);
    echo $pdo->lastInsertId();
} elseif ($mode === 'stock') {
    $id = (int)($argv[2] ?? 0);
    echo (int)$pdo->query("SELECT COALESCE(SUM(quantity),0) FROM stocks WHERE master_item_id = $id")->fetchColumn();
} elseif ($mode === 'cleanup') {
    $id = (int)($argv[2] ?? 0);
    if ($id <= 0) { echo 'skip'; exit; }
    $incIds = $pdo->query("SELECT DISTINCT incoming_id FROM incoming_items WHERE master_item_id = $id")->fetchAll(PDO::FETCH_COLUMN);
    $outIds = $pdo->query("SELECT DISTINCT outgoing_id FROM outgoing_items WHERE master_item_id = $id")->fetchAll(PDO::FETCH_COLUMN);
    $pdo->prepare("DELETE FROM incoming_items WHERE master_item_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM outgoing_items WHERE master_item_id = ?")->execute([$id]);
    foreach ($incIds as $iid) {
        $rest = $pdo->query("SELECT COUNT(*) FROM incoming_items WHERE incoming_id = $iid")->fetchColumn();
        if ((int)$rest === 0) {
            $pdo->prepare("DELETE FROM incoming WHERE id = ?")->execute([$iid]);
            $pdo->prepare("DELETE FROM audit_logs WHERE entity = 'incoming' AND entity_id = ?")->execute([$iid]);
        }
    }
    foreach ($outIds as $oid) {
        $rest = $pdo->query("SELECT COUNT(*) FROM outgoing_items WHERE outgoing_id = $oid")->fetchColumn();
        if ((int)$rest === 0) {
            $pdo->prepare("DELETE FROM outgoing WHERE id = ?")->execute([$oid]);
            $pdo->prepare("DELETE FROM audit_logs WHERE entity = 'outgoing' AND entity_id = ?")->execute([$oid]);
        }
    }
    $pdo->prepare("DELETE FROM stock_ledgers WHERE master_item_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM stocks WHERE master_item_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM serial_numbers WHERE master_item_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM master_item_attachments WHERE master_item_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM audit_logs WHERE entity = 'master_items' AND entity_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM master_items WHERE id = ?")->execute([$id]);
    echo 'cleaned ' . $id;
}
