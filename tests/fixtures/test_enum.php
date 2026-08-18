<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query("SELECT DISTINCT transaction_type FROM stock_ledgers");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
