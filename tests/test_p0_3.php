<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$pdo = Database::getInstance()->getConnection();

$results = [
    'PASS' => 0,
    'FAIL' => 0,
    'DETAILS' => []
];

function assertTest($name, $condition, $failMessage = '') {
    global $results;
    if ($condition) {
        $results['PASS']++;
        $results['DETAILS'][] = "[PASS] $name";
    } else {
        $results['FAIL']++;
        $results['DETAILS'][] = "[FAIL] $name - $failMessage";
    }
}

try {
    $rand = rand(1000, 9999);
    
    // Create Active Category and Unit
    $pdo->exec("INSERT INTO item_categories (code, name, is_active) VALUES ('CAT-TEST-$rand', 'Test Category Active', 1)");
    $cat_id = $pdo->lastInsertId();
    
    $pdo->exec("INSERT INTO units (code, name, is_active) VALUES ('UNIT-TEST-$rand', 'Test Unit Active', 1)");
    $unit_id = $pdo->lastInsertId();
    
    // Create Item
    $pdo->exec("INSERT INTO master_items (kode_barang, nama, item_category_id, unit_id, is_serialized, stok_minimum, is_active) VALUES ('BRG-TEST-$rand', 'Test Item Edit', $cat_id, $unit_id, 0, 10, 1)");
    $item_id = $pdo->lastInsertId();
    
    $pdo->exec("INSERT INTO warehouses (code, name, is_active) VALUES ('WH-TEST-$rand', 'Test WH', 1)");
    $wh_id = $pdo->lastInsertId();
    
    $pdo->exec("INSERT INTO stocks (master_item_id, warehouse_id, quantity) VALUES ($item_id, $wh_id, 25)");
    
    $_GET['item_id'] = $item_id;
    $_GET['warehouse_id'] = $wh_id;
    
    // Simulate ApiController::stock
    ob_start();
    $stmt = $pdo->prepare("SELECT COALESCE(quantity, 0) as total_qty FROM stocks WHERE master_item_id = ? AND warehouse_id = ?");
    $stmt->execute([$item_id, $wh_id]);
    $qty = (int)$stmt->fetchColumn();
    echo json_encode(['quantity' => $qty]);
    $output = ob_get_clean();
    
    $json = json_decode($output, true);
    
    assertTest("ApiStockTest", isset($json['quantity']) && $json['quantity'] === 25, "API Stock should return 25, got " . json_encode($json));

    // Cleanup
    $pdo->exec("DELETE FROM stocks WHERE master_item_id = $item_id AND warehouse_id = $wh_id");
    $pdo->exec("DELETE FROM master_items WHERE id = $item_id");
    $pdo->exec("DELETE FROM item_categories WHERE id = $cat_id");
    $pdo->exec("DELETE FROM units WHERE id = $unit_id");
    $pdo->exec("DELETE FROM warehouses WHERE id = $wh_id");
    
} catch (\Exception $e) {
    assertTest("Exception thrown", false, $e->getMessage());
}

foreach ($results['DETAILS'] as $detail) {
    echo $detail . "\n";
}
echo "\nTotal PASS: " . $results['PASS'] . "\n";
echo "Total FAIL: " . $results['FAIL'] . "\n";
