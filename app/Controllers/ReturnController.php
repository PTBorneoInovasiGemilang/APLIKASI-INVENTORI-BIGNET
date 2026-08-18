<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use App\Services\StockService;
use PDO;

class ReturnController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN', 'TEKNISI_NOC']);
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClauses = ["1=1"];
        $params = [];

        // Role restriction: Teknisi only sees their own returns
        if (AuthHelper::hasRole(['TEKNISI_NOC'])) {
            $whereClauses[] = "r.technician_id = ?";
            $params[] = AuthHelper::user()['id'];
        }

        if ($search !== '') {
            $whereClauses[] = "(r.transaction_code LIKE ? OR l.transaction_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        // Count for pagination
        $countSql = "SELECT COUNT(*) FROM returns r 
                     LEFT JOIN loans l ON r.reference_id = l.id AND r.reference_type = 'LOAN'
                     $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "SELECT r.*, w.name as warehouse_name, u.name as returner_name, l.transaction_code as loan_code,
                       t.name as technician_name
                FROM returns r 
                JOIN warehouses w ON r.warehouse_id = w.id 
                LEFT JOIN users u ON r.processed_by = u.id 
                LEFT JOIN users t ON r.technician_id = t.id
                LEFT JOIN loans l ON r.reference_id = l.id AND r.reference_type = 'LOAN'
                $whereSql
                ORDER BY r.id DESC LIMIT $limit OFFSET $offset";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $returns = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/returns/index.php';
    }

    public function show() {
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::getInstance()->getConnection();
        
        $sql = "SELECT r.*, w.name as warehouse_name, u.name as processor_name, l.transaction_code as loan_code,
                       t.name as technician_name
                FROM returns r 
                JOIN warehouses w ON r.warehouse_id = w.id 
                LEFT JOIN users u ON r.processed_by = u.id 
                LEFT JOIN users t ON r.technician_id = t.id
                LEFT JOIN loans l ON r.reference_id = l.id AND r.reference_type = 'LOAN'
                WHERE r.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $return = $stmt->fetch();

        if (!$return) {
            $_SESSION['error'] = "Data pengembalian tidak ditemukan.";
            header("Location: " . url('/returns'));
            exit;
        }

        // Role restriction check
        if (AuthHelper::hasRole(['TEKNISI_NOC']) && $return['technician_id'] != AuthHelper::user()['id']) {
            $_SESSION['error'] = "Anda tidak memiliki akses ke pengembalian ini.";
            header("Location: " . url('/returns'));
            exit;
        }

        // Fetch items
        $itemsStmt = $pdo->prepare("
            SELECT ri.*, m.kode_barang, m.nama, m.is_serialized, sn.serial_number 
            FROM return_items ri 
            JOIN master_items m ON ri.master_item_id = m.id
            LEFT JOIN serial_numbers sn ON ri.serial_number_id = sn.id
            WHERE ri.return_id = ?
        ");
        $itemsStmt->execute([$id]);
        $items = $itemsStmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/returns/show.php';
    }

    public function create() {
        AuthHelper::requireRole(['TEKNISI_NOC']); // ONLY TEKNISI CAN CREATE RETURN REQUEST
        $pdo = Database::getInstance()->getConnection();
        
        $user_id = AuthHelper::user()['id'];
        $stmt = $pdo->prepare("SELECT id, transaction_code, borrower_id FROM loans WHERE status != 'RETURNED' AND borrower_id = ?");
        $stmt->execute([$user_id]);
        $loans = $stmt->fetchAll();
        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();
        $items = $pdo->query("SELECT id, kode_barang, nama, is_serialized FROM master_items WHERE is_active = 1")->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/returns/create.php';
    }

    public function store() {
        AuthHelper::requireRole(['TEKNISI_NOC']); // ONLY TEKNISI CAN CREATE RETURN REQUEST
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed.";
            header("Location: " . url('/returns/create'));
            exit;
        }

        $warehouse_id = (int)$_POST['warehouse_id'];
        $loan_id = !empty($_POST['loan_id']) ? (int)$_POST['loan_id'] : null;
        $notes = trim($_POST['notes']);
        
        $item_ids = $_POST['item_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $sn_list = $_POST['sn_list'] ?? [];

        if (empty($item_ids)) {
            $_SESSION['error'] = "Tidak ada barang yang dipilih.";
            header("Location: " . url('/returns/create'));
            exit;
        }

        if ($loan_id) {
            $user_id = AuthHelper::user()['id'];
            $stmt = $pdo->prepare("SELECT id FROM loans WHERE id = ? AND borrower_id = ?");
            $stmt->execute([$loan_id, $user_id]);
            if (!$stmt->fetch()) {
                $_SESSION['error'] = "Dokumen Peminjaman tidak valid atau bukan milik Anda.";
                header("Location: " . url('/returns/create'));
                exit;
            }
        }

        $pdo = Database::getInstance()->getConnection();
        $user_id = AuthHelper::user()['id'];
        $transaction_code = \App\Helpers\TransactionHelper::generateCode('RET', 'returns');

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO returns (transaction_code, reference_type, reference_id, technician_id, warehouse_id, notes, status) VALUES (?, 'LOAN', ?, ?, ?, ?, 'PENDING')");
            $stmt->execute([$transaction_code, $loan_id, $user_id, $warehouse_id, $notes]);
            $return_id = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO return_items (return_id, master_item_id, quantity, serial_number_id, condition_status, loan_item_id) VALUES (?, ?, ?, ?, 'GOOD', ?)");

            foreach ($item_ids as $index => $item_id) {
                $qty = (int)$quantities[$index];
                if ($qty <= 0) continue;

                $master_item_id = (int)$item_id;
                $loan_item_id_val = null;

                if ($loan_id) {
                    $loanItemStmt = $pdo->prepare("SELECT id, master_item_id, quantity, returned_quantity FROM loan_items WHERE loan_id = ? AND id = ? FOR UPDATE");
                    $loanItemStmt->execute([$loan_id, $item_id]);
                    $loanItem = $loanItemStmt->fetch();

                    if (!$loanItem || $qty > ($loanItem['quantity'] - $loanItem['returned_quantity'])) {
                        throw new \Exception("Kuantitas pengembalian melebihi batas (Line ID: $item_id).");
                    }
                    $master_item_id = $loanItem['master_item_id'];
                    $loan_item_id_val = $loanItem['id'];
                }

                $mItem = $pdo->query("SELECT is_serialized FROM master_items WHERE id = " . $master_item_id)->fetch();
                if ($mItem && $mItem['is_serialized']) {
                    $rawSn = $sn_list[$index] ?? '';
                    $snArray = array_filter(array_map('trim', preg_split('/[\n,]+/', $rawSn)));
                    
                    if (count($snArray) !== $qty) {
                        throw new \Exception("Jumlah Serial Number tidak cocok dengan kuantitas untuk barang ID $master_item_id.");
                    }
                    if (count($snArray) !== count(array_unique($snArray))) {
                         throw new \Exception("Ada Serial Number duplikat pada input barang ID $master_item_id.");
                    }

                    foreach ($snArray as $sn) {
                        $chk = $pdo->prepare("SELECT id, status FROM serial_numbers WHERE serial_number = ? FOR UPDATE");
                        $chk->execute([$sn]);
                        $snRow = $chk->fetch();
                        
                        if (!$snRow) {
                            throw new \Exception("Serial Number '$sn' tidak ditemukan di sistem.");
                        }
                        if ($loan_item_id_val) {
                            $chkLoan = $pdo->prepare("SELECT lis.id, lis.status FROM loan_item_serials lis WHERE lis.loan_item_id = ? AND lis.serial_number_id = ?");
                            $chkLoan->execute([$loan_item_id_val, $snRow['id']]);
                            if (!$chkLoan->fetch()) {
                                throw new \Exception("Serial Number '$sn' bukan bagian dari peminjaman (Line ID $loan_item_id_val).");
                            }
                        }

                        $itemStmt->execute([$return_id, $master_item_id, 1, $snRow['id'], $loan_item_id_val]);
                    }
                } else {
                    $itemStmt->execute([$return_id, $master_item_id, $qty, null, $loan_item_id_val]);
                }
            }

            // Audit
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'returns', ?)");
            $auditStmt->execute([$user_id, $return_id]);

            $pdo->commit();
            $_SESSION['success'] = "Pengajuan pengembalian barang berhasil dikirim dan menunggu proses Admin.";
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal memproses pengembalian: " . $e->getMessage();
            header("Location: " . url('/returns/create'));
            exit;
        }

        // Notification ke ADMIN (business event: teknisi submit pengembalian).
        try {
            $actorName = AuthHelper::user()['name'];
            \App\Helpers\NotificationHelper::createForRoleOnce(
                'ADMIN',
                'Pengembalian Barang Baru',
                $actorName . ' mengajukan pengembalian ' . $transaction_code . '.',
                'info',
                '/returns/show?id=' . $return_id
            );
        } catch (\Exception $notifEx) {
            error_log('[BIGNET] Notification gagal (return create #' . $return_id . '): ' . $notifEx->getMessage());
        }

        header("Location: " . url('/returns'));
        exit;
    }

    public function process() {
        // D-006/D-014: proses pengembalian = ADMIN (operasional); SUPERADMIN read-only.
        AuthHelper::requireRole(['ADMIN']);
        AuthHelper::denySuperadminWrite();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . url('/returns'));
            exit;
        }
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed.";
            header("Location: " . url('/returns'));
            exit;
        }

        $id = (int)$_POST['id'];
        $pdo = Database::getInstance()->getConnection();
        $stockService = new StockService();
        $user_id = AuthHelper::user()['id'];

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM returns WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $return = $stmt->fetch();

            if (!$return || $return['status'] !== 'PENDING') {
                throw new \Exception("Pengembalian tidak valid atau sudah diproses.");
            }

            $warehouse_id = $return['warehouse_id'];
            $loan_id = $return['reference_id'];
            $transaction_code = $return['transaction_code'];

            // Fetch Items
            $itemsStmt = $pdo->prepare("SELECT ri.*, m.is_serialized FROM return_items ri JOIN master_items m ON ri.master_item_id = m.id WHERE ri.return_id = ?");
            $itemsStmt->execute([$id]);
            $items = $itemsStmt->fetchAll();

            $conditions = $_POST['conditions'] ?? [];

            foreach ($items as $item) {
                $cond = $conditions[$item['id']] ?? 'GOOD';
                $qty = (int)$item['quantity'];
                
                $isGood = in_array($cond, ['BAIK', 'GOOD', 'AVAILABLE']);
                
                // Update condition
                $pdo->prepare("UPDATE return_items SET condition_status = ? WHERE id = ?")->execute([$cond, $item['id']]);

                if ($item['is_serialized'] && $item['serial_number_id']) {
                    $snId = $item['serial_number_id'];
                    $chk = $pdo->prepare("SELECT id, status, current_warehouse_id, current_holder_id FROM serial_numbers WHERE id = ? FOR UPDATE");
                    $chk->execute([$snId]);
                    $snRow = $chk->fetch();

                    if ($snRow) {
                        $oldStatus = $snRow['status'];
                        $oldWh = $snRow['current_warehouse_id'];
                        $oldHolder = $snRow['current_holder_id'];
                        
                        $newStatus = $isGood ? 'AVAILABLE' : 'BROKEN';

                        // Update SN status and record movement using StockService
                        $stockService->moveSerial(
                            $snRow['id'], 
                            $newStatus, 
                            $warehouse_id, 
                            null, 
                            null, 
                            'return', 
                            $id, 
                            "Pengembalian barang - " . $transaction_code . " ($cond)", 
                            $cond
                        );
                        
                        // Update pivot if linked to a loan
                        if ($item['loan_item_id']) {
                            $pdo->prepare("UPDATE loan_item_serials SET status = 'RETURNED' WHERE loan_item_id = ? AND serial_number_id = ?")->execute([$item['loan_item_id'], $snRow['id']]);
                        }
                        
                        // Mutate stock ONLY if good
                        if ($isGood) {
                            $stockService->mutateStock($item['master_item_id'], $warehouse_id, 1, 'RETURN', 'returns', $id, "Pengembalian $transaction_code SN: " . $snRow['id']);
                        }
                    }
                } else {
                    // Bulk Mutate stock ONLY if good
                    if ($isGood) {
                        $stockService->mutateStock($item['master_item_id'], $warehouse_id, $qty, 'RETURN', 'returns', $id, "Pengembalian $transaction_code");
                    }
                }
                
                // If linked to a loan, update loan items
                if ($item['loan_item_id'] && !$item['is_serialized']) { 
                    $updLoan = $pdo->prepare("UPDATE loan_items SET returned_quantity = returned_quantity + ? WHERE id = ?");
                    $updLoan->execute([$qty, $item['loan_item_id']]);
                } else if ($item['loan_item_id'] && $item['is_serialized']) {
                    $updLoan = $pdo->prepare("UPDATE loan_items SET returned_quantity = returned_quantity + 1 WHERE id = ?");
                    $updLoan->execute([$item['loan_item_id']]);
                }
            }

            // Check if loan is fully returned
            if ($loan_id) {
                $chk = $pdo->prepare("SELECT SUM(quantity) as total_qty, SUM(returned_quantity) as total_returned FROM loan_items WHERE loan_id = ?");
                $chk->execute([$loan_id]);
                $loanStats = $chk->fetch();
                if ($loanStats && (int)$loanStats['total_returned'] >= (int)$loanStats['total_qty']) {
                    $pdo->prepare("UPDATE loans SET status = 'RETURNED' WHERE id = ?")->execute([$loan_id]);
                } else {
                    $pdo->prepare("UPDATE loans SET status = 'PARTIAL_RETURNED' WHERE id = ?")->execute([$loan_id]);
                }
            }

            // Mark return as completed
            $pdo->prepare("UPDATE returns SET status = 'COMPLETED', processed_by = ? WHERE id = ?")->execute([$user_id, $id]);

            // Audit
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'UPDATE', 'returns', ?)");
            $auditStmt->execute([$user_id, $id]);

            $pdo->commit();
            $_SESSION['success'] = "Pengembalian berhasil diproses dan stok telah diupdate.";
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal memproses: " . $e->getMessage();
        }

        header("Location: " . url('/returns/show?id=' . $id));
        exit;
    }
}
