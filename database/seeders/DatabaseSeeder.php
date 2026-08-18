<?php

// Autoloader
require __DIR__ . '/../../app/Core/Database.php';
require __DIR__ . '/../../app/Helpers/TransactionHelper.php';
require __DIR__ . '/../../app/Services/StockService.php';

// Quick autoloader fallback for other classes if needed
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

// Mock session and server for helpers
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
session_start();

use App\Core\Database;
use App\Helpers\TransactionHelper;
use App\Services\StockService;


$pdo = Database::getInstance()->getConnection();
$stockService = new StockService();

try {
    echo "Clearing old operational data (Truncating)...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tablesToTruncate = [
        'audit_logs', 'stock_adjustments', 'installations', 'returns', 'loan_items', 'loans',
        'transfer_items', 'transfers', 'outgoing_items', 'outgoing', 'incoming_items', 'incoming',
        'submission_items', 'submissions', 'serial_movements', 'serial_numbers', 'stock_ledgers', 'stocks',
        'master_item_attachments', 'master_items', 'locations', 'warehouses', 'units', 'item_categories'
    ];
    foreach ($tablesToTruncate as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
    }
    // Delete test users but keep the 3 main ones
    $pdo->exec("DELETE FROM users WHERE email NOT IN ('superadmin@ptbig.com', 'admin@ptbig.com', 'teknisi@ptbig.com')");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $pdo->beginTransaction();

    // Ensure 3 main users exist
    echo "Ensuring main users exist...\n";
    $users = [
        ['name' => 'Super Admin', 'email' => 'superadmin@ptbig.com', 'role' => 'SUPERADMIN', 'password_hash' => password_hash('superadmin123', PASSWORD_DEFAULT)],
        ['name' => 'Admin Gudang', 'email' => 'admin@ptbig.com', 'role' => 'ADMIN', 'password_hash' => password_hash('admin123', PASSWORD_DEFAULT)],
        ['name' => 'Teknisi Bignet', 'email' => 'teknisi@ptbig.com', 'role' => 'TEKNISI_NOC', 'password_hash' => password_hash('teknisi123', PASSWORD_DEFAULT)],
    ];
    $stmt = $pdo->prepare("INSERT INTO users (name, email, role, password_hash) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE role = VALUES(role), password_hash = VALUES(password_hash)");
    foreach ($users as $u) {
        $stmt->execute([$u['name'], $u['email'], $u['role'], $u['password_hash']]);
    }
    
    // Get user IDs
    $adminId = $pdo->query("SELECT id FROM users WHERE role = 'ADMIN' LIMIT 1")->fetchColumn();
    $teknisiId = $pdo->query("SELECT id FROM users WHERE role = 'TEKNISI_NOC' LIMIT 1")->fetchColumn();

    // Mock login for StockService
    $_SESSION['user_id'] = $adminId;
    $_SESSION['authenticated'] = true;
    $_SESSION['role'] = 'ADMIN';
    $_SESSION['user_name'] = 'Admin Gudang';
    $_SESSION['user_email'] = 'admin@ptbig.com';

    // 1. Categories
    echo "Seeding Categories...\n";
    $categories = [
        ['KAT-000001', 'Modem/ONT', 'Optical Network Terminal'],
        ['KAT-000002', 'Router', 'Network Router'],
        ['KAT-000003', 'Switch', 'Network Switch'],
        ['KAT-000004', 'SFP', 'Small Form-factor Pluggable'],
        ['KAT-000005', 'Kabel Fiber', 'Fiber Optic Cables'],
        ['KAT-000006', 'Adaptor', 'Power Adapters'],
        ['KAT-000007', 'Perangkat Wireless', 'Access Points & Wireless devices']
    ];
    $stmtCat = $pdo->prepare("INSERT INTO item_categories (code, name, description) VALUES (?, ?, ?)");
    foreach ($categories as $c) $stmtCat->execute($c);
    $catOnt = $pdo->lastInsertId() - 6;
    $catRouter = $pdo->lastInsertId() - 5;
    $catSfp = $pdo->lastInsertId() - 3;
    $catKabel = $pdo->lastInsertId() - 2;
    $catAdaptor = $pdo->lastInsertId() - 1;

    // 2. Units
    echo "Seeding Units...\n";
    $units = [
        ['UNT-000001', 'Pcs', 'Pieces'],
        ['UNT-000002', 'Roll', 'Rolls'],
        ['UNT-000003', 'Box', 'Boxes']
    ];
    $stmtUnit = $pdo->prepare("INSERT INTO units (code, name, description) VALUES (?, ?, ?)");
    foreach ($units as $u) $stmtUnit->execute($u);
    $unitPcs = $pdo->lastInsertId() - 2;
    $unitRoll = $pdo->lastInsertId() - 1;

    // 3. Warehouses
    echo "Seeding Warehouses...\n";
    $warehouses = [
        ['GDG-000001', 'Gudang Pontianak', 'Pusat'],
        ['GDG-000002', 'Gudang Singkawang', 'Cabang Singkawang'],
        ['GDG-000003', 'Gudang Sintang', 'Cabang Sintang']
    ];
    $stmtWh = $pdo->prepare("INSERT INTO warehouses (code, name, description) VALUES (?, ?, ?)");
    foreach ($warehouses as $w) $stmtWh->execute($w);
    $whPtk = $pdo->lastInsertId() - 2;
    $whSkw = $pdo->lastInsertId() - 1;

    // 4. Locations
    echo "Seeding Locations...\n";
    $locations = [
        ['LOK-000001', 'Rak A-01', $whPtk],
        ['LOK-000002', 'Rak A-02', $whPtk],
        ['LOK-000003', 'Rak B-01', $whSkw],
        ['LOK-000004', 'Rak C-01', $whPtk]
    ];
    $stmtLoc = $pdo->prepare("INSERT INTO locations (code, name, warehouse_id) VALUES (?, ?, ?)");
    foreach ($locations as $l) $stmtLoc->execute($l);

    // 5. Master Items
    echo "Seeding Master Items...\n";
    // code, name, cat, unit, serialized, min_stok
    $items = [
        ['KAT-000001-000001', 'Huawei HG8245H', $catOnt, $unitPcs, 1, 10],
        ['KAT-000001-000002', 'ZTE F670L', $catOnt, $unitPcs, 1, 10],
        ['KAT-000002-000001', 'MikroTik hEX', $catRouter, $unitPcs, 1, 5],
        ['KAT-000002-000002', 'MikroTik RB4011', $catRouter, $unitPcs, 1, 2],
        ['KAT-000004-000001', 'SFP GPON', $catSfp, $unitPcs, 0, 50],
        ['KAT-000005-000001', 'Kabel Fiber Dropcore', $catKabel, $unitRoll, 0, 5],
        ['KAT-000006-000001', 'Adaptor 12V', $catAdaptor, $unitPcs, 0, 20],
    ];
    $stmtItem = $pdo->prepare("INSERT INTO master_items (kode_barang, nama, item_category_id, unit_id, is_serialized, stok_minimum) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($items as $i) $stmtItem->execute($i);
    $itemHuawei = $pdo->lastInsertId() - 6;
    $itemDropcore = $pdo->lastInsertId() - 1;

    // 6. Incoming (Add Stock)
    echo "Seeding Incoming...\n";
    $incCode = TransactionHelper::generateCode('INC', 'incoming');
    $pdo->prepare("INSERT INTO incoming (transaction_code, warehouse_id, received_by, supplier_info, notes) VALUES (?, ?, ?, 'Supplier Pusat', 'Stok awal bulan')")->execute([$incCode, $whPtk, $adminId]);
    $incId = $pdo->lastInsertId();

    $pdo->prepare("INSERT INTO incoming_items (incoming_id, master_item_id, quantity) VALUES (?, ?, ?)")->execute([$incId, $itemHuawei, 50]);
    $stockService->mutateStock($itemHuawei, $whPtk, 50, 'INCOMING', 'incoming', $incId, "Incoming $incCode");

    $pdo->prepare("INSERT INTO incoming_items (incoming_id, master_item_id, quantity) VALUES (?, ?, ?)")->execute([$incId, $itemDropcore, 10]);
    $stockService->mutateStock($itemDropcore, $whPtk, 10, 'INCOMING', 'incoming', $incId, "Incoming $incCode");

    // 7. Add Serials
    echo "Seeding Serials...\n";
    $stmtSn = $pdo->prepare("INSERT INTO serial_numbers (master_item_id, serial_number, current_warehouse_id, status) VALUES (?, ?, ?, 'AVAILABLE')");
    $stmtMove = $pdo->prepare("INSERT INTO serial_movements (serial_number_id, from_status, to_status, from_warehouse_id, to_warehouse_id, reference_type, reference_id, performed_by, notes) VALUES (?, '', 'AVAILABLE', NULL, ?, 'incoming', ?, ?, ?)");
    for ($i=1; $i<=5; $i++) {
        $sn = "ONT-HW-2026-" . str_pad($i, 5, '0', STR_PAD_LEFT);
        $stmtSn->execute([$itemHuawei, $sn, $whPtk]);
        $snId = $pdo->lastInsertId();
        $stmtMove->execute([$snId, $whPtk, $incId, $adminId, "Incoming $incCode"]);
        
        if ($i == 1) $sn1 = $snId; // We will track this one for full lifecycle
    }

    // 8. Transfer (Send to Singkawang)
    echo "Seeding Transfer...\n";
    $trfCode = TransactionHelper::generateCode('TRF', 'transfers');
    $pdo->prepare("INSERT INTO transfers (transaction_code, source_warehouse_id, destination_warehouse_id, status, sent_by, notes) VALUES (?, ?, ?, 'COMPLETED', ?, 'Distribusi Cabang')")->execute([$trfCode, $whPtk, $whSkw, $adminId]);
    $trfId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO transfer_items (transfer_id, master_item_id, quantity_sent, quantity_received) VALUES (?, ?, ?, ?)")->execute([$trfId, $itemHuawei, 5, 5]);
    
    // Transfer Out Stock
    $stockService->mutateStock($itemHuawei, $whPtk, -5, 'TRANSFER_OUT', 'transfers', $trfId, "Transfer Out $trfCode");
    // Transfer In Stock
    $stockService->mutateStock($itemHuawei, $whSkw, 5, 'TRANSFER_IN', 'transfers', $trfId, "Transfer In $trfCode");

    // Move SN 1 to Singkawang
    $pdo->prepare("UPDATE serial_numbers SET current_warehouse_id = ? WHERE id = ?")->execute([$whSkw, $sn1]);
    $stmtMove->execute([$sn1, $whSkw, $trfId, $adminId, "Transfer $trfCode"]);

    // 9. Submission
    echo "Seeding Submission & Fulfillment...\n";
    $subCode = TransactionHelper::generateCode('REQ', 'submissions', 'submission_code');
    $pdo->prepare("INSERT INTO submissions (submission_code, submitted_by, status, approved_by, approved_at, notes) VALUES (?, ?, 'APPROVED', ?, NOW(), 'Minta modem buat pasang')")->execute([$subCode, $teknisiId, $adminId]);
    $subId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO submission_items (submission_id, master_item_id, requested_quantity, approved_quantity, fulfilled_quantity, remaining_quantity) VALUES (?, ?, 2, 2, 1, 1)")->execute([$subId, $itemHuawei]);

    // 10. Outgoing (Fulfill 1)
    $outCode = TransactionHelper::generateCode('OUT', 'outgoing');
    $pdo->prepare("INSERT INTO outgoing (transaction_code, warehouse_id, submission_id, processed_by, notes) VALUES (?, ?, ?, ?, 'Parsial')")->execute([$outCode, $whSkw, $subId, $adminId]);
    $outId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO outgoing_items (outgoing_id, master_item_id, quantity) VALUES (?, ?, ?)")->execute([$outId, $itemHuawei, 1]);
    
    $stockService->mutateStock($itemHuawei, $whSkw, -1, 'OUTGOING', 'outgoing', $outId, "Outgoing $outCode");

    // SN 1 Outgoing to Teknsi
    $pdo->prepare("UPDATE serial_numbers SET current_warehouse_id = NULL, current_holder_id = ?, status = 'USED' WHERE id = ?")->execute([$teknisiId, $sn1]);
    $stmtMoveOut = $pdo->prepare("INSERT INTO serial_movements (serial_number_id, from_status, to_status, from_warehouse_id, to_holder_id, reference_type, reference_id, performed_by, notes) VALUES (?, 'AVAILABLE', 'USED', ?, ?, 'outgoing', ?, ?, ?)");
    $stmtMoveOut->execute([$sn1, $whSkw, $teknisiId, $outId, $adminId, "Outgoing $outCode"]);

    // 11. Installation
    echo "Seeding Installation...\n";
    $insCode = TransactionHelper::generateCode('INS', 'installations');
    $pdo->prepare("INSERT INTO installations (transaction_code, customer_site, technician_id, installed_at, status, notes) VALUES (?, 'Budi - Jl. Merdeka 123', ?, NOW(), 'INSTALLED', 'Pasang baru')")->execute([$insCode, $teknisiId]);
    $insId = $pdo->lastInsertId();

    // Move SN 1 to Installed
    $pdo->prepare("UPDATE serial_numbers SET status = 'INSTALLED', current_holder_id = NULL WHERE id = ?")->execute([$sn1]);
    $stmtMoveIns = $pdo->prepare("INSERT INTO serial_movements (serial_number_id, from_status, to_status, from_holder_id, reference_type, reference_id, performed_by, notes) VALUES (?, 'USED', 'INSTALLED', ?, 'installations', ?, ?, ?)");
    $stmtMoveIns->execute([$sn1, $teknisiId, $insId, $teknisiId, "Installation $insCode at Budi"]);

    $pdo->commit();
    echo "Database Seeding Completed Successfully!\n";

} catch (\Exception $e) {
    $pdo->rollBack();
    echo "Seeding Failed: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
