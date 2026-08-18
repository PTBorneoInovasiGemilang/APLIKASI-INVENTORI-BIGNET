<?php
// Usage:
//   php user_seed.php seed [email]            -> inserts PENDING_APPROVAL user, echoes id
//   php user_seed.php cleanup <id>            -> deletes user + its audit rows + notifications
//   php user_seed.php get <email>             -> echoes id|account_status|is_active|role|rejection_reason (empty if not found)
//   php user_seed.php cleanup_by_email <email> -> deletes user by email + its audit rows + notifications
$pdo = new PDO('mysql:host=127.0.0.1;dbname=db_gudang_tommy;charset=utf8mb4', 'root', '');
$mode = $argv[1] ?? '';

function deleteUser($pdo, $id) {
    $id = (int)$id;
    if ($id <= 0) return;
    $pdo->prepare('DELETE FROM audit_logs WHERE entity = "users" AND entity_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM audit_logs WHERE user_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM notifications WHERE user_id = ?')->execute([$id]);
    // Ledger/transaksi uji yang mencatat test-admin sebagai pelaku agar FK users aman.
    $pdo->prepare('DELETE FROM stock_ledgers WHERE performed_by = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM serial_movements WHERE performed_by = ?')->execute([$id]);
    // Hapus header transaksi uji yang mereferensikan user (test admin) agar FK users aman.
    foreach (['incoming' => 'received_by', 'outgoing' => 'processed_by'] as $tbl => $col) {
        $ids = $pdo->query("SELECT id FROM $tbl WHERE $col = $id")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($ids as $hid) {
            $child = ($tbl === 'incoming') ? 'incoming_items' : 'outgoing_items';
            $pdo->prepare("DELETE FROM $child WHERE {$tbl}_id = ?")->execute([$hid]);
        }
        foreach ($ids as $hid) {
            $pdo->prepare("DELETE FROM $tbl WHERE id = ?")->execute([$hid]);
        }
    }
    $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
}

if ($mode === 'seed') {
    $email = $argv[2] ?? ('acct_pending_' . time() . '@ptbig.com');
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, account_status, is_active, phone) VALUES (?, ?, ?, 'TEKNISI_NOC', 'PENDING_APPROVAL', 0, '0811')");
    $stmt->execute(['Acct Pending', $email, password_hash('temp123456', PASSWORD_DEFAULT)]);
    echo $pdo->lastInsertId();
} elseif ($mode === 'cleanup') {
    deleteUser($pdo, $argv[2] ?? 0);
    echo "cleaned " . (int)($argv[2] ?? 0);
} elseif ($mode === 'cleanup_category') {
    $name = $argv[2] ?? '';
    if ($name !== '') {
        $pdo->prepare('DELETE FROM item_categories WHERE name = ?')->execute([$name]);
        echo 'cleaned_category ' . $name;
    }
} elseif ($mode === 'activate') {
    // Untuk test UI toggle: ubah user PENDING menjadi APPROVED + aktif.
    $id = (int)($argv[2] ?? 0);
    if ($id > 0) {
        $pdo->prepare("UPDATE users SET account_status = 'APPROVED', is_active = 1 WHERE id = ?")->execute([$id]);
        echo 'activated ' . $id;
    }
} elseif ($mode === 'get') {
    $email = $argv[2] ?? '';
    if ($email === '') exit;
    $stmt = $pdo->prepare("SELECT id, account_status, is_active, role, rejection_reason FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) { echo ''; exit; }
    echo $u['id'] . '|' . $u['account_status'] . '|' . $u['is_active'] . '|' . $u['role'] . '|' . ($u['rejection_reason'] ?? '');
} elseif ($mode === 'cleanup_by_email') {
    $email = $argv[2] ?? '';
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $id = $stmt->fetchColumn();
    if ($id) deleteUser($pdo, $id);
    echo "cleaned_by_email " . $email;
} elseif ($mode === 'cleanup_role_test') {
    // Hapus semua user uji role-label (idempotent spec) — hanya email prefix test.
    $stmt = $pdo->query("SELECT id FROM users WHERE email LIKE 'teknisi_%@ptbig.com' OR email LIKE 'roleadmin_%@ptbig.com'");
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ids as $id) deleteUser($pdo, $id);
    echo 'cleaned_role_test ' . count($ids);
}
