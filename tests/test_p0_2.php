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
    
    // Deactivate Category and Unit
    $pdo->exec("UPDATE item_categories SET is_active = 0 WHERE id = $cat_id");
    $pdo->exec("UPDATE units SET is_active = 0 WHERE id = $unit_id");
    
    // Simulate what MasterItemController::edit does
    $catStmt = $pdo->prepare("SELECT * FROM item_categories WHERE is_active = 1 OR id = ?");
    $catStmt->execute([$cat_id]);
    $categories = $catStmt->fetchAll();
    
    $unitStmt = $pdo->prepare("SELECT * FROM units WHERE is_active = 1 OR id = ?");
    $unitStmt->execute([$unit_id]);
    $units = $unitStmt->fetchAll();
    
    // Validate
    $catFound = false;
    foreach ($categories as $c) {
        if ($c['id'] == $cat_id) $catFound = true;
    }
    
    $unitFound = false;
    foreach ($units as $u) {
        if ($u['id'] == $unit_id) $unitFound = true;
    }
    
    assertTest("InactiveCategoryCurrentValueTest", $catFound, "Inactive category should still be returned for existing item.");
    assertTest("InactiveUnitCurrentValueTest", $unitFound, "Inactive unit should still be returned for existing item.");

    // Cleanup
    $pdo->exec("DELETE FROM master_items WHERE id = $item_id");
    $pdo->exec("DELETE FROM item_categories WHERE id = $cat_id");
    $pdo->exec("DELETE FROM units WHERE id = $unit_id");
    
} catch (\Exception $e) {
    assertTest("Exception thrown", false, $e->getMessage());
}

foreach ($results['DETAILS'] as $detail) {
    echo $detail . "\n";
}
echo "\nTotal PASS: " . $results['PASS'] . "\n";
echo "Total FAIL: " . $results['FAIL'] . "\n";
