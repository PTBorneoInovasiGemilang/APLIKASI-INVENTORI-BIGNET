<?php
// Usage: php settings_set.php get <key>
//        php settings_set.php set <key> <value>
$pdo = new PDO('mysql:host=127.0.0.1;dbname=db_gudang_tommy;charset=utf8mb4', 'root', '');
$mode = $argv[1] ?? '';
$key = $argv[2] ?? '';

if ($mode === 'get' && $key !== '') {
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE `key` = ?');
    $stmt->execute([$key]);
    echo (string)$stmt->fetchColumn();
} elseif ($mode === 'set' && $key !== '') {
    $value = $argv[3] ?? '';
    $stmt = $pdo->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
    $stmt->execute([$key, $value]);
    echo "set $key = $value\n";
}
