<?php
// Helper untuk test remediasi return workflow.
// Usage:
//   php return_seed.php seed        -> membuat return PENDING, echo "id|itemId|warehouseId|oldStock|itemName|warehouseName"
//   php return_seed.php cleanup id itemId warehouseId oldStock  -> revert stock + hapus data test
$pdo = new PDO('mysql:host=127.0.0.1;dbname=db_gudang_tommy;charset=utf8mb4', 'root', '');
$mode = $argv[1] ?? 'seed';

if ($mode === 'seed') {
    $item = $pdo->query("SELECT id, kode_barang, nama FROM master_items WHERE is_active = 1 AND is_serialized = 0 ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $wh = $pdo->query("SELECT id, name FROM warehouses WHERE is_active = 1 ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $tech = $pdo->query("SELECT id FROM users WHERE email = 'teknisia@ptbig.com' LIMIT 1")->fetchColumn();
    if (!$item || !$wh || !$tech) { fwrite(STDERR, "seed data tidak tersedia\n"); exit(1); }

    $stk = $pdo->prepare("SELECT quantity FROM stocks WHERE master_item_id = ? AND warehouse_id = ?");
    $stk->execute([$item['id'], $wh['id']]);
    $oldStock = $stk->fetchColumn();
    $oldStock = ($oldStock === false) ? 'NULL' : (int)$oldStock;

    $pdo->beginTransaction();
    $pdo->prepare("INSERT INTO returns (transaction_code, reference_type, reference_id, technician_id, warehouse_id, notes, status) VALUES (?, 'LOAN', NULL, ?, ?, 'test remediasi return', 'PENDING')")
        ->execute(['RET-TEST-' . time(), $tech, $wh['id']]);
    $rid = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO return_items (return_id, master_item_id, quantity, serial_number_id, condition_status, loan_item_id) VALUES (?, ?, 1, NULL, 'GOOD', NULL)")
        ->execute([$rid, $item['id']]);
    $pdo->commit();

    echo $rid . '|' . $item['id'] . '|' . $wh['id'] . '|' . $oldStock . '|' . $item['nama'] . '|' . $wh['name'];
} elseif ($mode === 'cleanup') {
    $id = (int)$argv[2];
    $itemId = (int)$argv[3];
    $whId = (int)$argv[4];
    $oldStock = $argv[5];

    $pdo->beginTransaction();
    // Revert ledger + stock yang dibuat proses return
    $pdo->prepare("DELETE FROM stock_ledgers WHERE reference_type = 'returns' AND reference_id = ?")->execute([$id]);
    if ($oldStock === 'NULL') {
        $pdo->prepare("DELETE FROM stocks WHERE master_item_id = ? AND warehouse_id = ?")->execute([$itemId, $whId]);
    } else {
        $pdo->prepare("UPDATE stocks SET quantity = ? WHERE master_item_id = ? AND warehouse_id = ?")->execute([(int)$oldStock, $itemId, $whId]);
    }
    $pdo->prepare("DELETE FROM audit_logs WHERE entity = 'returns' AND entity_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM return_items WHERE return_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM returns WHERE id = ?")->execute([$id]);
    $pdo->commit();
    echo "cleaned";
}
