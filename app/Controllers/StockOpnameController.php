<?php

namespace App\Controllers;

use App\Core\Database;
use App\Helpers\AuthHelper;
use PDO;
use Exception;

class StockOpnameController
{
    public function index()
    {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);

        $db = Database::getInstance();
        
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $whereParams = [];
        $whereClause = "WHERE 1=1";

        if ($search) {
            $whereClause .= " AND (so.transaction_code LIKE :search OR w.name LIKE :search)";
            $whereParams[':search'] = "%$search%";
        }

        $countQuery = "SELECT COUNT(*) FROM stock_opnames so 
                       LEFT JOIN warehouses w ON so.warehouse_id = w.id 
                       $whereClause";
        $stmtCount = $db->prepare($countQuery);
        $stmtCount->execute($whereParams);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $query = "SELECT so.*, w.name as warehouse_name, u.name as conducted_by_name 
                  FROM stock_opnames so 
                  LEFT JOIN warehouses w ON so.warehouse_id = w.id 
                  LEFT JOIN users u ON so.conducted_by = u.id 
                  $whereClause 
                  ORDER BY so.created_at DESC 
                  LIMIT $limit OFFSET $offset";
                  
        $stmt = $db->prepare($query);
        $stmt->execute($whereParams);
        $opnames = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../resources/views/transactions/stock_opnames/index.php';
    }

    public function create()
    {
        \App\Helpers\AuthHelper::denySuperadminWrite();
        AuthHelper::requireRole(['ADMIN']);
        $db = Database::getInstance();
        
        $stmtW = $db->query("SELECT id, name FROM warehouses ORDER BY name ASC");
        $warehouses = $stmtW->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../../resources/views/transactions/stock_opnames/create.php';
    }

    public function store()
    {
        \App\Helpers\AuthHelper::denySuperadminWrite();
        AuthHelper::requireRole(['ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/opnames'));
            exit;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("CSRF Token Invalid");
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $warehouseId = $_POST['warehouse_id'];
            $notes = $_POST['notes'];
            
            $datePrefix = date('Ymd');
            $prefix = 'SOP';
            $stmtLast = $db->prepare("SELECT transaction_code FROM stock_opnames WHERE transaction_code LIKE :prefix ORDER BY id DESC LIMIT 1 FOR UPDATE");
            $stmtLast->execute([':prefix' => $prefix . '-' . $datePrefix . '-%']);
            $lastCode = $stmtLast->fetchColumn();
            
            $sequence = 1;
            if ($lastCode) {
                $parts = explode('-', $lastCode);
                $sequence = (int)end($parts) + 1;
            }
            $transactionCode = sprintf("%s-%s-%04d", $prefix, $datePrefix, $sequence);

            $stmtInc = $db->prepare("INSERT INTO stock_opnames (transaction_code, warehouse_id, conducted_by, opname_date, status, notes) 
                                     VALUES (?, ?, ?, NOW(), 'REVIEW', ?)");
            $stmtInc->execute([
                $transactionCode, 
                $warehouseId, 
                $_SESSION['user_id'], 
                $notes
            ]);
            $opnameId = $db->lastInsertId();

            if (empty($_POST['items'])) {
                throw new Exception("Minimal satu item harus ada selisih yang dicatat.");
            }

            foreach ($_POST['items'] as $itemData) {
                $masterItemId = $itemData['master_item_id'];
                $sysQty = (int)$itemData['system_quantity'];
                $physQty = (int)$itemData['physical_quantity'];
                $variance = $physQty - $sysQty;
                $reason = $itemData['reason'] ?? '';
                
                if ($variance === 0) continue; // Only record variances

                $stmtItem = $db->prepare("INSERT INTO stock_opname_items (stock_opname_id, master_item_id, system_quantity, physical_quantity, variance, reason) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtItem->execute([
                    $opnameId, $masterItemId, $sysQty, $physQty, $variance, $reason
                ]);
            }

            // Check if any items actually inserted
            $check = $db->prepare("SELECT COUNT(*) FROM stock_opname_items WHERE stock_opname_id = ?");
            $check->execute([$opnameId]);
            if ($check->fetchColumn() == 0) {
                throw new Exception("Semua barang cocok (Variance 0). Laporan tidak perlu dibuat.");
            }

            // Timeline
            $stmtTimeline = $db->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, new_data) VALUES (?, ?, ?, ?, ?)");
            $stmtTimeline->execute([
                $_SESSION['user_id'], 'CREATE_OPNAME', 'stock_opnames', $opnameId, json_encode(['description' => 'Stock Opname dibuat (' . $transactionCode . ')'])
            ]);

            $db->commit();
            \App\Helpers\NotificationHelper::createForRole('SUPERADMIN', 'Stock Opname menunggu review', 'Stock Opname ' . $transactionCode . ' menunggu review/approval Anda.', 'warning', '/opnames/show?id=' . $opnameId);
            $_SESSION['success'] = "Stock Opname berhasil dibuat dan menunggu proses Review.";
            header('Location: ' . url('/opnames/show?id=' . $opnameId));
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
            if (php_sapi_name() === 'cli') {
                echo "SESSION ERROR: " . $_SESSION['error'] . "\n";
                exit;
            }
            header('Location: ' . url('/opnames/create'));
            exit;
        }
    }

    public function show()
    {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . url('/opnames'));
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT so.*, w.name as warehouse_name, u.name as conducted_by_name 
                              FROM stock_opnames so 
                              LEFT JOIN warehouses w ON so.warehouse_id = w.id 
                              LEFT JOIN users u ON so.conducted_by = u.id 
                              WHERE so.id = ?");
        $stmt->execute([$id]);
        $opname = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$opname) {
            header('Location: ' . url('/opnames'));
            exit;
        }

        // Get Items
        $stmtItems = $db->prepare("SELECT soi.*, m.kode_barang, m.nama as item_name 
                                   FROM stock_opname_items soi
                                   JOIN master_items m ON soi.master_item_id = m.id
                                   WHERE soi.stock_opname_id = ?");
        $stmtItems->execute([$id]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        
        // Get Timeline
        $stmtTimeline = $db->prepare("SELECT u.name as user_name, a.action, a.new_data, a.created_at 
                                      FROM audit_logs a
                                      LEFT JOIN users u ON a.user_id = u.id
                                      WHERE a.entity = 'stock_opnames' AND a.entity_id = ?
                                      ORDER BY a.created_at DESC");
        $stmtTimeline->execute([$id]);
        $timelineRaw = $stmtTimeline->fetchAll(PDO::FETCH_ASSOC);
        
        $timeline = [];
        foreach ($timelineRaw as $t) {
            $data = json_decode($t['new_data'], true);
            $t['description'] = $data['description'] ?? '';
            $timeline[] = $t;
        }

        require __DIR__ . '/../../resources/views/transactions/stock_opnames/show.php';
    }
    
    public function process()
    {
        AuthHelper::requireRole(['SUPERADMIN']); // Only SUPERADMIN can approve opname variance

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/opnames'));
            exit;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("CSRF Token Invalid");
        }
        
        $id = $_POST['opname_id'];
        $action = $_POST['action']; // APPROVED, REJECTED
        $resolutionNotes = $_POST['resolution_notes'];

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare("SELECT * FROM stock_opnames WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $opname = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$opname || $opname['status'] === 'APPROVED' || $opname['status'] === 'REJECTED') {
                throw new Exception("Stock Opname tidak valid atau sudah selesai diproses.");
            }
            
            $stmtUpdate = $db->prepare("UPDATE stock_opnames SET status = ?, updated_at = NOW(), notes = CONCAT(IFNULL(notes,''), '\n\nResolusi: ', ?) WHERE id = ?");
            $stmtUpdate->execute([$action, $resolutionNotes, $id]);
            
            if ($action === 'APPROVED') {
                // Fetch items and create automatic Adjustment transaction
                $stmtItems = $db->prepare("SELECT * FROM stock_opname_items WHERE stock_opname_id = ?");
                $stmtItems->execute([$id]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                
                // Create Adjustment Record
                $datePrefix = date('Ymd');
                $stmtLastAdj = $db->prepare("SELECT transaction_code FROM stock_adjustments WHERE transaction_code LIKE :prefix ORDER BY id DESC LIMIT 1 FOR UPDATE");
                $stmtLastAdj->execute([':prefix' => 'ADJ-' . $datePrefix . '-%']);
                $lastCodeAdj = $stmtLastAdj->fetchColumn();
                $seqAdj = 1;
                if ($lastCodeAdj) {
                    $parts = explode('-', $lastCodeAdj);
                    $seqAdj = (int)end($parts) + 1;
                }
                $adjCode = sprintf("ADJ-%s-%04d", $datePrefix, $seqAdj);
                
                $stmtAdj = $db->prepare("INSERT INTO stock_adjustments (transaction_code, warehouse_id, processed_by, reason) VALUES (?, ?, ?, ?)");
                $stmtAdj->execute([$adjCode, $opname['warehouse_id'], $_SESSION['user_id'], "Auto-adjustment from Stock Opname " . $opname['transaction_code']]);
                $adjId = $db->lastInsertId();
                
                // Update link back to stock_opnames
                $stmtLink = $db->prepare("UPDATE stock_opnames SET adjustment_transaction_code = ? WHERE id = ?");
                $stmtLink->execute([$adjCode, $id]);

                foreach ($items as $item) {
                    // Update exact stock
                    $stmtUpdateStock = $db->prepare("UPDATE stocks SET quantity = ?, updated_at = NOW() WHERE warehouse_id = ? AND master_item_id = ?");
                    $stmtUpdateStock->execute([$item['physical_quantity'], $opname['warehouse_id'], $item['master_item_id']]);
                    
                    if ($stmtUpdateStock->rowCount() === 0) {
                        // Insert if doesn't exist
                        $stmtInsertStock = $db->prepare("INSERT INTO stocks (warehouse_id, master_item_id, quantity) VALUES (?, ?, ?)");
                        $stmtInsertStock->execute([$opname['warehouse_id'], $item['master_item_id'], $item['physical_quantity']]);
                    }
                    
                    // Insert into stock_adjustment_items
                    $stmtAdjItem = $db->prepare("INSERT INTO stock_adjustment_items (stock_adjustment_id, master_item_id, old_quantity, new_quantity, difference) VALUES (?, ?, ?, ?, ?)");
                    $stmtAdjItem->execute([$adjId, $item['master_item_id'], $item['system_quantity'], $item['physical_quantity'], $item['variance']]);
                    
                    // Insert Stock Ledger
                    $stmtLedger = $db->prepare("INSERT INTO stock_ledgers (master_item_id, warehouse_id, transaction_type, reference_type, reference_id, quantity_delta, balance_before, balance_after, performed_by, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtLedger->execute([
                        $item['master_item_id'], 
                        $opname['warehouse_id'], 
                        'ADJUSTMENT', 
                        'stock_adjustments', 
                        $adjId, 
                        $item['variance'], 
                        $item['system_quantity'],
                        $item['physical_quantity'],
                        $_SESSION['user_id'],
                        "Opname Variance"
                    ]);
                }
            }
            
            // Timeline
            $stmtTimeline = $db->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, new_data) VALUES (?, ?, ?, ?, ?)");
            $stmtTimeline->execute([
                $_SESSION['user_id'], 'PROCESS_OPNAME', 'stock_opnames', $id, json_encode(['description' => 'Diproses menjadi ' . $action . '. ' . $resolutionNotes])
            ]);
            
            $db->commit();
            $_SESSION['success'] = "Stock Opname berhasil diupdate menjadi $action.";
            header('Location: ' . url('/opnames/show?id=' . $id));
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
            header('Location: ' . url('/opnames/show?id=' . $id));
            exit;
        }
    }
    
    // Helper API to fetch current stock for a warehouse
    public function apiItems()
    {
        header('Content-Type: application/json');
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
        
        $warehouseId = $_GET['warehouse_id'] ?? null;
        if (!$warehouseId) {
            echo json_encode([]);
            exit;
        }
        
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT s.master_item_id, s.quantity, m.kode_barang, m.nama 
                              FROM stocks s
                              JOIN master_items m ON s.master_item_id = m.id
                              WHERE s.warehouse_id = ? AND m.is_serialized = 0
                              ORDER BY m.nama ASC");
        $stmt->execute([$warehouseId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($items);
        exit;
    }
}
