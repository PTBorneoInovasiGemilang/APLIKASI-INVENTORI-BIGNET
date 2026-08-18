<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use App\Services\StockService;
use PDO;

class LoanController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN', 'TEKNISI_NOC']);
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClauses = ["1=1"];
        $params = [];

        if (!AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])) {
            $whereClauses[] = "l.borrower_id = ?";
            $params[] = (int)$user['id'];
        }

        if ($search !== '') {
            $whereClauses[] = "(l.transaction_code LIKE ? OR s.submission_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status !== '') {
            $whereClauses[] = "l.status = ?";
            $params[] = $status;
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        // Count for pagination
        $countSql = "SELECT COUNT(*) FROM loans l 
                     JOIN warehouses w ON l.warehouse_id = w.id 
                     JOIN users u ON l.borrower_id = u.id 
                     LEFT JOIN submissions s ON l.submission_id = s.id 
                     $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "SELECT l.*, w.name as warehouse_name, u.name as borrower_name, s.submission_code 
                FROM loans l 
                JOIN warehouses w ON l.warehouse_id = w.id 
                JOIN users u ON l.borrower_id = u.id 
                LEFT JOIN submissions s ON l.submission_id = s.id 
                $whereSql
                ORDER BY l.id DESC LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $loans = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/loans/index.php';
    }

    public function create() {
        AuthHelper::requireRole(['TEKNISI_NOC']);
        $pdo = Database::getInstance()->getConnection();
        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();
        $items = $pdo->query("SELECT id, kode_barang, nama, is_serialized FROM master_items WHERE is_active = 1")->fetchAll();
        
        $users = [];
        if (AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])) {
            $users = $pdo->query("SELECT id, name FROM users WHERE is_active = 1")->fetchAll();
        }

        // Fetch approved submissions
        if (AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])) {
            $submissions = $pdo->query("SELECT id, submission_code FROM submissions WHERE status IN ('APPROVED', 'PARTIAL_APPROVED')")->fetchAll();
        } else {
            $stmt = $pdo->prepare("SELECT id, submission_code FROM submissions WHERE status IN ('APPROVED', 'PARTIAL_APPROVED') AND submitted_by = ?");
            $stmt->execute([AuthHelper::user()['id']]);
            $submissions = $stmt->fetchAll();
        }

        require __DIR__ . '/../../resources/views/transactions/loans/create.php';
    }

    public function store() {
        AuthHelper::requireRole(['TEKNISI_NOC']);
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed.";
            header("Location: " . url('/loans/create'));
            exit;
        }

        $warehouse_id = (int)$_POST['warehouse_id'];
        $notes = trim($_POST['notes']);
        $due_date = $_POST['due_date'] ?? null;
        $loan_type = $_POST['loan_type'] ?? 'INTERNAL';
        $submission_id = !empty($_POST['submission_id']) ? (int)$_POST['submission_id'] : null;
        
        $borrower_id = AuthHelper::user()['id'];

        if ($loan_type === 'FROM_SUBMISSION' && $submission_id) {
            $subCheck = $pdo->prepare("SELECT submitted_by FROM submissions WHERE id = ?");
            $subCheck->execute([$submission_id]);
            $subRow = $subCheck->fetch();
            
            if (!$subRow || (!AuthHelper::hasRole(['SUPERADMIN', 'ADMIN']) && $subRow['submitted_by'] != $borrower_id)) {
                $_SESSION['error'] = "Pengajuan ini bukan milik Anda.";
                header("Location: " . url('/loans/create'));
                exit;
            }
            // borrower adalah teknisi pemilik pengajuan.
            if ($subRow) {
                $borrower_id = $subRow['submitted_by'];
            }
        }

        $item_ids = $_POST['item_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $sn_list = $_POST['sn_list'] ?? [];

        if (empty($item_ids)) {
            $_SESSION['error'] = "Tidak ada barang yang dipilih.";
            header("Location: " . url('/loans/create'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $stockService = new StockService();
        $user_id = AuthHelper::user()['id'];
        $transaction_code = \App\Helpers\TransactionHelper::generateCode('LOAN', 'loans');

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO loans (transaction_code, borrower_id, warehouse_id, submission_id, loan_type, loan_date, due_date, status, notes, processed_by) VALUES (?, ?, ?, ?, ?, NOW(), ?, 'BORROWED', ?, ?)");
            $stmt->execute([$transaction_code, $borrower_id, $warehouse_id, $submission_id, $loan_type, $due_date, $notes, $user_id]);
            $loan_id = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO loan_items (loan_id, master_item_id, quantity, returned_quantity) VALUES (?, ?, ?, 0)");

            foreach ($item_ids as $index => $item_id) {
                $qty = (int)$quantities[$index];
                if ($qty <= 0) continue;

                // Validation against submission if applicable (only if this loan itself is fulfilling the submission directly)
                // Note: According to the prompt, usually Outgoing fulfills it. If Loan fulfills it, it shouldn't double deduct.
                // Actually, if loan_type === 'FROM_SUBMISSION', we don't deduct stock because it was already deducted by Outgoing.
                // But wait, if loan_type === 'FROM_SUBMISSION', we don't update submission_items remaining_quantity because Outgoing did it!
                
                $itemStmt->execute([$loan_id, $item_id, $qty]);
                $loan_item_id = $pdo->lastInsertId();

                // Check serialized
                $mItem = $pdo->query("SELECT is_serialized FROM master_items WHERE id = " . (int)$item_id)->fetch();
                if ($mItem && $mItem['is_serialized']) {
                    $rawSn = $sn_list[$index] ?? '';
                    $snArray = array_filter(array_map('trim', preg_split('/[\n,]+/', $rawSn)));
                    
                    if (count($snArray) !== $qty) {
                        throw new \Exception("Jumlah Serial Number tidak cocok dengan kuantitas untuk barang ID $item_id. Diminta: $qty, Diisi: " . count($snArray));
                    }
                    if (count($snArray) !== count(array_unique($snArray))) {
                         throw new \Exception("Ada Serial Number duplikat pada input barang ID $item_id.");
                    }

                    foreach ($snArray as $sn) {
                        if ($loan_type === 'DIRECT') {
                            $chk = $pdo->prepare("SELECT id, status, master_item_id FROM serial_numbers WHERE serial_number = ? AND current_warehouse_id = ? FOR UPDATE");
                            $chk->execute([$sn, $warehouse_id]);
                            $snRow = $chk->fetch();
                        } else {
                            if (!$submission_id) {
                                throw new \Exception("Submission ID wajib diisi untuk peminjaman FROM_SUBMISSION.");
                            }
                            $chk = $pdo->prepare("SELECT id, status, master_item_id FROM serial_numbers WHERE serial_number = ? FOR UPDATE");
                            $chk->execute([$sn]);
                            $snRow = $chk->fetch();
                            
                            if ($snRow) {
                                $valOut = $pdo->prepare("
                                    SELECT 1 FROM serial_movements sm
                                    JOIN outgoing o ON sm.reference_id = o.id
                                    WHERE sm.serial_number_id = ? AND sm.reference_type = 'outgoing' AND o.submission_id = ?
                                ");
                                $valOut->execute([$snRow['id'], $submission_id]);
                                if (!$valOut->fetch()) {
                                    throw new \Exception("Serial Number '$sn' tidak terkait dengan Pengajuan (Submission) ini.");
                                }
                            }
                        }
                        
                        if (!$snRow) {
                            throw new \Exception("Serial Number '$sn' tidak ditemukan di lokasi yang diharapkan.");
                        }

                        if ($snRow['master_item_id'] != $item_id) {
                            throw new \Exception("Serial Number '$sn' tidak cocok dengan Master Item yang dipilih.");
                        }

                        if ($loan_type === 'DIRECT' && $snRow['status'] !== 'AVAILABLE') {
                            throw new \Exception("Serial Number '$sn' tidak berstatus AVAILABLE.");
                        }
                        if ($loan_type === 'FROM_SUBMISSION' && !in_array($snRow['status'], ['ISSUED', 'AVAILABLE'])) {
                            throw new \Exception("Serial Number '$sn' tidak berstatus ISSUED atau AVAILABLE (saat ini: {$snRow['status']}).");
                        }

                        $oldStatus = $snRow['status'];

                        // Update SN status and set holder to the borrower using StockService
                        $stockService->moveSerial(
                            $snRow['id'], 
                            'LOANED', 
                            null, 
                            null, 
                            $borrower_id, 
                            'loan', 
                            $loan_id, 
                            "Peminjaman - " . $transaction_code
                        );
                        
                        // Pivot insert
                        $pdo->prepare("INSERT INTO loan_item_serials (loan_item_id, serial_number_id) VALUES (?, ?)")->execute([$loan_item_id, $snRow['id']]);
                    }
                }

                // Mutate stock ONLY for DIRECT loan
                if ($loan_type === 'DIRECT') {
                    $stockService->mutateStock($item_id, $warehouse_id, -$qty, 'LOAN', 'loans', $loan_id, "Peminjaman Langsung $transaction_code oleh User ID $borrower_id");
                }
            }

            // We removed the submission fulfillment logic here since Outgoing handles it.
            // But we keep submission_id to link the loan to the submission.

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'loans', ?)");
            $auditStmt->execute([$user_id, $loan_id]);

            $pdo->commit();
            $_SESSION['success'] = "Peminjaman berhasil diproses.";
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal memproses peminjaman: " . $e->getMessage();
            header("Location: " . url('/loans/create'));
            exit;
        }

        // Notification ke ADMIN (business event: teknisi request peminjaman).
        try {
            $actorName = AuthHelper::user()['name'];
            \App\Helpers\NotificationHelper::createForRoleOnce(
                'ADMIN',
                'Peminjaman Barang Baru',
                $actorName . ' mengajukan peminjaman ' . $transaction_code . '.',
                'info',
                '/loans/show?id=' . $loan_id
            );
        } catch (\Exception $notifEx) {
            error_log('[BIGNET] Notification gagal (loan create #' . $loan_id . '): ' . $notifEx->getMessage());
        }

        header("Location: " . url('/loans'));
        exit;
    }

    public function show() {
        $id = (int)$_GET['id'];
        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();
        
        $stmt = $pdo->prepare("SELECT l.*, w.name as warehouse_name, u.name as borrower_name, s.submission_code 
                FROM loans l 
                JOIN warehouses w ON l.warehouse_id = w.id 
                JOIN users u ON l.borrower_id = u.id 
                LEFT JOIN submissions s ON l.submission_id = s.id 
                WHERE l.id = ?");
        $stmt->execute([$id]);
        $loan = $stmt->fetch();

        if (!$loan) {
            $_SESSION['error'] = "Loan not found";
            header("Location: " . url('/loans'));
            exit;
        }

        if ($user['role'] === 'TEKNISI_NOC' && $loan['borrower_id'] !== $user['id']) {
            $_SESSION['error'] = "403 Forbidden";
            header("Location: " . url('/loans'));
            exit;
        }

        $itemStmt = $pdo->prepare("SELECT li.*, m.kode_barang, m.nama, m.is_serialized 
                                   FROM loan_items li 
                                   JOIN master_items m ON li.master_item_id = m.id 
                                   WHERE li.loan_id = ?");
        $itemStmt->execute([$id]);
        $items = $itemStmt->fetchAll();

        // Fetch serial numbers for each item
        $snStmt = $pdo->prepare("SELECT sn.serial_number 
                                 FROM loan_item_serials lis 
                                 JOIN serial_numbers sn ON lis.serial_number_id = sn.id 
                                 WHERE lis.loan_item_id = ?");
        foreach ($items as &$item) {
            if ($item['is_serialized']) {
                $snStmt->execute([$item['id']]);
                $item['serial_numbers'] = $snStmt->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $item['serial_numbers'] = [];
            }
        }

        // Fetch Timeline / Audit logs
        $stmt = $pdo->prepare("SELECT a.*, u.name as user_name 
                               FROM audit_logs a 
                               LEFT JOIN users u ON a.user_id = u.id 
                               WHERE a.entity = 'loans' AND a.entity_id = ? 
                               ORDER BY a.created_at DESC");
        $stmt->execute([$id]);
        $timeline = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/loans/show.php';
    }
    
    public function apiItems() {
        header('Content-Type: application/json');
        try {
            if (!isset($_GET['id'])) {
                echo json_encode([]);
                return;
            }
            
            $id = (int)$_GET['id'];
            $pdo = Database::getInstance()->getConnection();
            
            $stmt = $pdo->prepare("SELECT li.id as loan_item_id, li.master_item_id, m.nama, m.kode_barang, m.is_serialized, li.quantity, li.returned_quantity 
                                   FROM loan_items li 
                                   JOIN master_items m ON li.master_item_id = m.id 
                                   WHERE li.loan_id = ? AND li.quantity > li.returned_quantity");
            $stmt->execute([$id]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode($items);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
