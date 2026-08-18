<?php
require_once 'app/Core/Database.php';

$pdo = \App\Core\Database::getInstance()->getConnection();

echo "--- DISTINCT STATUS IN DB ---\n";
$stmt = $pdo->query("SELECT status, COUNT(*) as c FROM incidents GROUP BY status");
foreach ($stmt->fetchAll() as $row) {
    echo $row['status'] . " : " . $row['c'] . "\n";
}

echo "\n--- ALL INCIDENTS ---\n";
$stmt = $pdo->query("SELECT id, transaction_code, status, incident_type, created_at FROM incidents ORDER BY created_at DESC");
foreach ($stmt->fetchAll() as $row) {
    echo $row['id'] . " | " . $row['transaction_code'] . " | " . $row['status'] . " | " . $row['incident_type'] . "\n";
}

echo "\n--- CHECK GET STATUS PARAMETER BEHAVIOR ---\n";
$_GET['status'] = 'PENDING';
$statusFilter = $_GET['status'] ?? '';
$whereParams = [];
$whereClause = "WHERE 1=1";
if ($statusFilter !== '') {
    $whereClause .= " AND i.status = :status";
    $whereParams[':status'] = $statusFilter;
}
$query = "SELECT i.* FROM incidents i $whereClause";
echo "Query built: $query\n";
$stmt = $pdo->prepare($query);
$stmt->execute($whereParams);
echo "Count rows with ?status=PENDING : " . count($stmt->fetchAll()) . "\n";
