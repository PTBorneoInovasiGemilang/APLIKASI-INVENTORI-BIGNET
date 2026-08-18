<?php
require_once 'app/Core/Database.php';

$pdo = \App\Core\Database::getInstance()->getConnection();

// 1. Dashboard KPI
$lowStock = (int)$pdo->query("SELECT COUNT(DISTINCT s.master_item_id) FROM stocks s JOIN master_items m ON s.master_item_id = m.id WHERE s.quantity <= m.stok_minimum")->fetchColumn();
echo "STOK RENDAH = $lowStock\n";

// 2. Fetch via StockController (simulate GET)
$_GET['filter'] = 'low_stock';
$whereClauses = ["1=1"];
$whereClauses[] = "s.quantity <= m.stok_minimum";
$whereSql = "WHERE " . implode(" AND ", $whereClauses);

$countSql = "SELECT COUNT(*) FROM stocks s 
             JOIN master_items m ON s.master_item_id = m.id 
             JOIN warehouses w ON s.warehouse_id = w.id $whereSql";
$stockPageCount = (int)$pdo->query($countSql)->fetchColumn();
echo "STOCK PAGE MATCHES = $stockPageCount\n";

if ($lowStock === $stockPageCount) {
    echo "DATA KONSISTEN.\n";
} else {
    echo "DATA TIDAK KONSISTEN!\n";
}
