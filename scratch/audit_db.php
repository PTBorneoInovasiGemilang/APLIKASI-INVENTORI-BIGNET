<?php
// Lightweight .env Loader
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("{$name}={$value}");
        }
    }
}

function getDbConnection() {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $db   = getenv('DB_DATABASE') ?: 'db_gudang_tommy';
    $user = getenv('DB_USERNAME') ?: 'root';
    $pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, $user, $pass, $options);
}

$report = ["errors" => [], "warnings" => [], "passed" => []];

try {
    $pdo = getDbConnection();
    $report['passed'][] = "Database connection established.";



    // Phase 9: Inventory Integrity
    $sql = "
        SELECT 
            s.id as stock_id, 
            s.quantity as current_stock,
            COALESCE(SUM(l.quantity_delta), 0) as calculated_stock
        FROM stocks s
        LEFT JOIN stock_ledgers l ON s.master_item_id = l.master_item_id AND s.warehouse_id = l.warehouse_id
        GROUP BY s.id, s.quantity
        HAVING current_stock != calculated_stock
    ";
    $stmt = $pdo->query($sql);
    $mismatches = $stmt->fetchAll();
    if (count($mismatches) > 0) {
        $report['errors'][] = "Stock mismatch found for " . count($mismatches) . " stock records (Current != Ledger sum).";
    } else {
        $report['passed'][] = "All stock records match their ledger history sum.";
    }

    $sql = "
        SELECT 
            s.id as stock_id,
            s.quantity,
            (SELECT COUNT(*) FROM serial_numbers sn WHERE sn.master_item_id = s.master_item_id AND sn.current_warehouse_id = s.warehouse_id AND sn.status = 'AVAILABLE') as serial_count
        FROM stocks s
        JOIN master_items m ON s.master_item_id = m.id
        WHERE m.is_serialized = 1
        HAVING s.quantity != serial_count
    ";
    $stmt = $pdo->query($sql);
    $serialMismatches = $stmt->fetchAll();
    if (count($serialMismatches) > 0) {
        $report['errors'][] = "Serialized stock quantity mismatch for " . count($serialMismatches) . " records (Quantity != AVAILABLE Serials).";
    } else {
        $report['passed'][] = "All serialized stock quantities match their AVAILABLE serial count.";
    }

    $sql = "
        SELECT serial_number, COUNT(*) as cnt 
        FROM serial_numbers 
        GROUP BY serial_number 
        HAVING cnt > 1
    ";
    $stmt = $pdo->query($sql);
    $dups = $stmt->fetchAll();
    if (count($dups) > 0) {
        $report['errors'][] = "Duplicate serial numbers found (" . count($dups) . " duplicates).";
    } else {
        $report['passed'][] = "No duplicate serial numbers found.";
    }
    
    $sql = "SELECT id, quantity FROM stocks WHERE quantity < 0";
    $stmt = $pdo->query($sql);
    $negatives = $stmt->fetchAll();
    if (count($negatives) > 0) {
        $report['errors'][] = "Negative stock found for " . count($negatives) . " records.";
    } else {
        $report['passed'][] = "No negative stock found.";
    }

} catch (Exception $e) {
    $report['errors'][] = "Database connection failed: " . $e->getMessage();
}

echo json_encode($report, JSON_PRETTY_PRINT);
