<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=db_gudang_tommy;charset=utf8mb4', 'root', '');
$id = $pdo->query('SELECT id FROM incidents ORDER BY id DESC LIMIT 1')->fetchColumn();
if (!$id) {
    $pdo->prepare("INSERT INTO incidents (transaction_code, incident_type, reported_by, reported_date, status, notes) VALUES (?, 'DAMAGE', 3, NOW(), 'PENDING', 'seeded for evidence test')")
        ->execute(['INC-' . time()]);
    $id = $pdo->lastInsertId();
}
echo $id;
