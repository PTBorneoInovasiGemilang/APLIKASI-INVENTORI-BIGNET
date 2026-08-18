<?php
require_once 'app/Core/Database.php';

$pdo = \App\Core\Database::getInstance()->getConnection();

// 1. Dashboard KPI
$openIncidents = (int)$pdo->query("SELECT COUNT(*) FROM incidents WHERE status = 'PENDING'")->fetchColumn();
echo "OPEN INCIDENTS = $openIncidents\n";

// 2. Fetch via IncidentController (simulate GET)
$_GET['status'] = 'PENDING';
$whereParams = [];
$whereClause = "WHERE 1=1";

$statusFilter = $_GET['status'] ?? '';
if ($statusFilter !== '') {
    $whereClause .= " AND i.status = :status";
    $whereParams[':status'] = $statusFilter;
}

$countQuery = "SELECT COUNT(*) FROM incidents i 
               LEFT JOIN users u ON i.reported_by = u.id 
               $whereClause";
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($whereParams);
$listCount = (int)$stmtCount->fetchColumn();
echo "INCIDENT PAGE MATCHES = $listCount\n";

if ($openIncidents === $listCount) {
    echo "DATA KONSISTEN.\n";
} else {
    echo "DATA TIDAK KONSISTEN!\n";
}
