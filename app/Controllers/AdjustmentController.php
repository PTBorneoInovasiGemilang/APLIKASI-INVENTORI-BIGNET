<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use App\Services\StockService;
use PDO;

class AdjustmentController {
    
    public function __construct() {
        // Adjustments usually restricted to SUPERADMIN/ADMIN
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClauses = ["1=1"];
        $params = [];

        if ($search !== '') {
            $whereClauses[] = "a.transaction_code LIKE ?";
            $params[] = "%$search%";
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        // Count for pagination
        $countSql = "SELECT COUNT(*) FROM stock_adjustments a $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "SELECT a.*, w.name as warehouse_name, u.name as user_name 
                FROM stock_adjustments a 
                JOIN warehouses w ON a.warehouse_id = w.id 
                JOIN users u ON a.processed_by = u.id 
                $whereSql
                ORDER BY a.id DESC LIMIT $limit OFFSET $offset";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $adjustments = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/adjustments/index.php';
    }

    public function show() {
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("SELECT a.*, w.name as warehouse_name, u.name as user_name 
                FROM stock_adjustments a 
                JOIN warehouses w ON a.warehouse_id = w.id 
                JOIN users u ON a.processed_by = u.id 
                WHERE a.id = ?");
        $stmt->execute([$id]);
        $adjustment = $stmt->fetch();

        if (!$adjustment) {
            $_SESSION['error'] = "Data penyesuaian tidak ditemukan.";
            header("Location: " . url('/adjustments'));
            exit;
        }

        $itemsStmt = $pdo->prepare("SELECT ai.*, m.kode_barang, m.nama, m.is_serialized 
                                    FROM stock_adjustment_items ai 
                                    JOIN master_items m ON ai.master_item_id = m.id 
                                    WHERE ai.stock_adjustment_id = ?");
        $itemsStmt->execute([$id]);
        $items = $itemsStmt->fetchAll();

        // Get SN movements for this adjustment
        foreach ($items as &$item) {
            if ($item['is_serialized']) {
                $snStmt = $pdo->prepare("SELECT sn.serial_number, sm.from_status, sm.to_status FROM serial_movements sm JOIN serial_numbers sn ON sm.serial_number_id = sn.id WHERE sm.reference_type = 'adjustment' AND sm.reference_id = ? AND sn.master_item_id = ?");
                $snStmt->execute([$id, $item['master_item_id']]);
                $sns = $snStmt->fetchAll();
                
                $lostOrDamaged = [];
                $added = [];
                foreach ($sns as $sn) {
                    if ($sn['to_status'] === 'AVAILABLE') {
                        $added[] = $sn['serial_number'];
                    } else if (in_array($sn['to_status'], ['LOST', 'DAMAGED'])) {
                        $lostOrDamaged[] = $sn['serial_number'] . ' (' . $sn['to_status'] . ')';
                    }
                }
                
                $item['sn_added'] = implode(', ', $added);
                $item['sn_removed'] = implode(', ', $lostOrDamaged);
            }
        }
        unset($item);

        require __DIR__ . '/../../resources/views/transactions/adjustments/show.php';
    }

    public function create() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        $pdo = Database::getInstance()->getConnection();
        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();
        $items = $pdo->query("SELECT id, kode_barang, nama, is_serialized FROM master_items WHERE is_active = 1")->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/adjustments/create.php';
    }

    public function store() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed.";
            header("Location: " . url('/adjustments/create'));
            exit;
        }

        $warehouse_id = (int)$_POST['warehouse_id'];
        $reason_type = trim($_POST['reason_type'] ?? 'Selisih Opname');
        $notes = trim($_POST['notes'] ?? '');
        $reason = $reason_type . ($notes ? ' - ' . $notes : '');
        
        $item_ids = $_POST['item_ids'] ?? [];
        $actual_quantities = $_POST['actual_quantities'] ?? [];
        $sn_lists = $_POST['sn_list'] ?? [];

        if (empty($item_ids)) {
            $_SESSION['error'] = "Tidak ada barang yang di-opname.";
            header("Location: " . url('/adjustments/create'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $stockService = new StockService();
        $user_id = AuthHelper::user()['id'];
        $transaction_code = \App\Helpers\TransactionHelper::generateCode('ADJ', 'stock_adjustments');

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO stock_adjustments (transaction_code, warehouse_id, processed_by, reason) VALUES (?, ?, ?, ?)");
            $stmt->execute([$transaction_code, $warehouse_id, $user_id, $reason]);
            $adjustment_id = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO stock_adjustment_items (stock_adjustment_id, master_item_id, old_quantity, new_quantity, difference) VALUES (?, ?, ?, ?, ?)");

            foreach ($item_ids as $index => $item_id) {
                $actual_qty = (int)$actual_quantities[$index];
                if ($actual_qty < 0) continue;

                // Get current stock
                $currStmt = $pdo->prepare("SELECT quantity FROM stocks WHERE master_item_id = ? AND warehouse_id = ? FOR UPDATE");
                $currStmt->execute([$item_id, $warehouse_id]);
                $curr = $currStmt->fetchColumn();
                $old_qty = $curr !== false ? (int)$curr : 0;
                
                $diff = $actual_qty - $old_qty;

                if ($diff != 0) {
                    // Check serialized
                    $mItem = $pdo->query("SELECT is_serialized FROM master_items WHERE id = " . (int)$item_id)->fetch();
                    if ($mItem && $mItem['is_serialized']) {
                        $rawSn = $sn_lists[$index] ?? '';
                        $snArray = array_filter(array_map('trim', preg_split('/[\n,]+/', $rawSn)));
                        $absDiff = abs($diff);
                        
                        if (count($snArray) !== $absDiff) {
                            throw new \Exception("Jumlah Serial Number tidak cocok dengan selisih ($diff) untuk barang ID $item_id.");
                        }
                        if (count($snArray) !== count(array_unique($snArray))) {
                             throw new \Exception("Ada Serial Number duplikat pada input barang ID $item_id.");
                        }

                        if ($diff > 0) {
                            // Barang bertambah, add new SNs to AVAILABLE
                            $insSn = $pdo->prepare("INSERT INTO serial_numbers (master_item_id, serial_number, status, current_warehouse_id) VALUES (?, ?, 'AVAILABLE', ?)");
                            foreach ($snArray as $sn) {
                                // Check if SN exists
                                $chk = $pdo->prepare("SELECT id FROM serial_numbers WHERE serial_number = ?");
                                $chk->execute([$sn]);
                                if ($chk->fetch()) {
                                    throw new \Exception("Serial Number '$sn' sudah terdaftar di sistem.");
                                }
                                $insSn->execute([$item_id, $sn, $warehouse_id]);
                                $snId = $pdo->lastInsertId();
                                
                                $insMov = $pdo->prepare("INSERT INTO serial_movements (serial_number_id, from_status, to_status, from_warehouse_id, to_warehouse_id, from_holder_id, to_holder_id, reference_type, reference_id, performed_by, notes) VALUES (?, NULL, 'AVAILABLE', NULL, ?, NULL, NULL, 'adjustment', ?, ?, ?)");
                                $insMov->execute([$snId, $warehouse_id, $adjustment_id, $user_id, "Penyesuaian (Bertambah) - " . $transaction_code]);
                            }
                        } else {
                            // Barang berkurang, mark as LOST or DAMAGED
                            $newStatus = ($reason_type === 'Barang Rusak') ? 'DAMAGED' : 'LOST';
                            foreach ($snArray as $sn) {
                                $chk = $pdo->prepare("SELECT id, status, current_warehouse_id, current_holder_id FROM serial_numbers WHERE serial_number = ? AND current_warehouse_id = ? AND master_item_id = ? AND status = 'AVAILABLE' FOR UPDATE");
                                $chk->execute([$sn, $warehouse_id, $item_id]);
                                $snRow = $chk->fetch();
                                
                                if (!$snRow) {
                                    throw new \Exception("Serial Number '$sn' tidak valid (tidak ada, tidak AVAILABLE, atau bukan milik barang/gudang ini).");
                                }
                                
                                $stockService->moveSerial(
                                    $snRow['id'], 
                                    $newStatus, 
                                    null, 
                                    null, 
                                    null, 
                                    'adjustment', 
                                    $adjustment_id, 
                                    "Penyesuaian (Berkurang) - " . $transaction_code
                                );
                            }
                        }
                    }

                    $itemStmt->execute([$adjustment_id, $item_id, $old_qty, $actual_qty, $diff]);
                    // Mutate stock (pass the difference. if actual is 10, old is 8, diff is +2)
                    $stockService->mutateStock($item_id, $warehouse_id, $diff, 'ADJUSTMENT', 'stock_adjustments', $adjustment_id, "Opname $transaction_code");
                }
            }

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'stock_adjustments', ?)");
            $auditStmt->execute([$user_id, $adjustment_id]);

            $pdo->commit();
            $_SESSION['success'] = "Penyesuaian stok berhasil dicatat.";
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal memproses penyesuaian: " . $e->getMessage();
            header("Location: " . url('/adjustments/create'));
            exit;
        }

        header("Location: " . url('/adjustments'));
        exit;
    }
}
