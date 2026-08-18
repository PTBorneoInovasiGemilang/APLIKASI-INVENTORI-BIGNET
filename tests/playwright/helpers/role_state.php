<?php
// Usage: php role_state.php <email>  -> echoes id|role|account_status|is_active (NOTFOUND jika tidak ada)
$config = require __DIR__ . '/../../../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}", $config['username'], $config['password']);
$email = $argv[1] ?? '';
$stmt = $pdo->prepare("SELECT id, role, account_status, is_active FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
echo $u ? ($u['id'] . '|' . $u['role'] . '|' . $u['account_status'] . '|' . $u['is_active']) : 'NOTFOUND';
