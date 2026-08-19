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

        $whereClauses = ['1=1'];
        $params = [];
        if (!AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])) {
            $whereClauses[] = 'l.borrower_id = ?';
            $params[] = (int)$user['id'];
        }
        if ($search !== '') {
            $whereClauses[] = '(l.transaction_code LIKE ? OR s.submission_code LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($status !== '') {
            $whereClauses[] = 'l.status = ?';
            $params[] = $status;
        }
        $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);

        $countSql = "SELECT COUNT(*) FROM loans l
                     JOIN warehouses w ON l.warehouse_id = w.id
                     JOIN users u ON l.borrower_id = u.id
                     LEFT JOIN submissions s ON l.submission_id = s.id
                     $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = (int)$stmtCount->fetchColumn();
        $totalPages = (int)ceil($totalItems / $limit);

        $sql = "SELECT l.*, w.name AS warehouse_name, u.name AS borrower_name, s.submission_code
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
        $warehouses = $pdo->query('SELECT id, name FROM warehouses WHERE is_active = 1 ORDER BY name')->fetchAll();
        $items = $pdo->query('SELECT id, nama, is_serialized FROM master_items WHERE is_active = 1 ORDER BY nama')->fetchAll();

        $submissions = [];
        $stmt = $pdo->prepare("SELECT id FROM submissions WHERE status IN ('APPROVED', 'PARTIAL_APPROVED') AND submitted_by = ? ORDER BY id DESC");
        $stmt->execute([AuthHelper::user()['id']]);
        $submissions = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/loans/create.php';
    }

    public function store() {
        AuthHelper::requireRole(['TEKNISI_NOC']);
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'CSRF validation failed.';
            header('Location: ' . url('/loans/create'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $warehouse_id = (int)($_POST['warehouse_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $due_date = $_POST['due_date'] ?? null;
        $loan_type = $_POST['loan_type'] ?? 'INTERNAL';
        $submission_id = !empty($_POST['submission_id']) ? (int)$_POST['submission_id'] : null;
        $borrower_id = (int)AuthHelper::user()['id'];
        $user_id = $borrower_id;

        if ($warehouse_id <= 0) {
            $_SESSION['error'] = 'Gudang asal wajib dipilih.';
            header('Location: ' . url('/loans/create'));
            exit;
        }

        if ($submission_id) {
            $subCheck = $pdo->prepare("SELECT submitted_by FROM submissions WHERE id = ? AND status IN ('APPROVED','PARTIAL_APPROVED')");
            $subCheck->execute([$submission_id]);
            $subRow = $subCheck->fetch();
            if (!$subRow || (int)$subRow['submitted_by'] !== $borrower_id) {
                $_SESSION['error'] = 'Pengajuan yang dipilih tidak valid atau bukan milik Anda.';
                header('Location: ' . url('/loans/create'));
                exit;
            }
        }

        $source_types = $_POST['source_types'] ?? [];
        $item_ids = $_POST['item_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $sn_lists = $_POST['sn_list'] ?? [];
        $manual_names = $_POST['manual_names'] ?? [];
        $manual_specs = $_POST['manual_specs'] ?? [];
        $manual_units = $_POST['manual_units'] ?? [];

        if (empty($source_types)) {
            $_SESSION['error'] = 'Tambahkan minimal satu barang.';
            header('Location: ' . url('/loans/create'));
            exit;
        }

        $stockService = new StockService();
        $transaction_code = \\App\\Helpers\\TransactionHelper::generateCode('LOAN', 'loans');

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO loans (transaction_code, borrower_id, warehouse_id, submission_id, loan_type, loan_date, due_date, status, notes, processed_by) VALUES (?, ?, ?, ?, ?, NOW(), ?, 'BORROWED', ?, ?)");
            $stmt->execute([$transaction_code, $borrower_id, $warehouse_id, $submission_id, $loan_type, $due_date, $notes, $user_id]);
            $loan_id = (int)$pdo->lastInsertId();

            $stockItemStmt = $pdo->prepare("INSERT INTO loan_items (loan_id, master_item_id, quantity, returned_quantity) VALUES (?, ?, ?, 0)");
            $manualItemStmt = $pdo->prepare("INSERT INTO loan_manual_items (loan_id, name, specification, unit, quantity, returned_quantity) VALUES (?, ?, ?, ?, ?, 0)");
            $serialLookupStmt = $pdo->prepare("SELECT id, status, master_item_id FROM serial_numbers WHERE serial_number = ? AND current_warehouse_id = ? FOR UPDATE");

            $validLines = 0;
            foreach ($source_types as $index => $source) {
                $qty = (int)($quantities[$index] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                if ($source === 'MANUAL') {
                    $name = trim($manual_names[$index] ?? '');
                    $spec = trim($manual_specs[$index] ?? '');
                    $unit = trim($manual_units[$index] ?? '');
                    if ($name === '') {
                        throw new \\Exception('Nama barang manual wajib diisi.');
                    }
                    $manualItemStmt->execute([$loan_id, $name, $spec !== '' ? $spec : null, $unit !== '' ? $unit : null, $qty]);
                    $validLines++;
                    continue;
                }

                $itemId = (int)($item_ids[$index] ?? 0);
                if ($itemId <= 0) {
                    throw new \\Exception('Barang dari stok wajib dipilih.');
                }

                $itemStmt = $pdo->prepare('SELECT id, nama, is_serialized FROM master_items WHERE id = ? AND is_active = 1');
                $itemStmt->execute([$itemId]);
                $masterItem = $itemStmt->fetch();
                if (!$masterItem) {
                    throw new \\Exception('Barang yang dipilih tidak valid.');
                }

                $stockCheck = $pdo->prepare('SELECT quantity FROM stocks WHERE master_item_id = ? AND warehouse_id = ? FOR UPDATE');
                $stockCheck->execute([$itemId, $warehouse_id]);
                $available = (int)$stockCheck->fetchColumn();
                if ($available < $qty) {
                    throw new \\Exception("Stok {$masterItem['nama']} tidak mencukupi. Tersedia $available, diminta $qty.");
                }

                $stockItemStmt->execute([$loan_id, $itemId, $qty]);
                $loan_item_id = (int)$pdo->lastInsertId();
                $validLines++;

                if ((int)$masterItem['is_serialized'] === 1) {
                    $selectedSerials = [];
                    foreach ((array)($sn_lists[$index] ?? []) as $snValue) {
                        $snValue = trim((string)$snValue);
                        if ($snValue !== '') $selectedSerials[] = $snValue;
                    }
                    if (count($selectedSerials) !== $qty) {
                        throw new \\Exception("Jumlah Serial Number untuk {$masterItem['nama']} harus $qty.");
                    }
                    if (count($selectedSerials) !== count(array_unique($selectedSerials))) {
                        throw new \\Exception('Ada Serial Number yang dipilih lebih dari sekali.');
                    }

                    foreach ($selectedSerials as $sn) {
                        $serialLookupStmt->execute([$sn, $warehouse_id]);
                        $snRow = $serialLookupStmt->fetch();
                        if (!$snRow) {
                            throw new \\Exception("Serial Number '$sn' tidak tersedia di gudang asal.");
                        }
                        if ((int)$snRow['master_item_id'] !== $itemId) {
                            throw new \\Exception("Serial Number '$sn' bukan milik barang yang dipilih.");
                        }
                        if ($snRow['status'] !== 'AVAILABLE') {
                            throw new \\Exception("Serial Number '$sn' tidak tersedia untuk dipinjam.");
                        }

                        $stockService->moveSerial($snRow['id'], 'LOANED', null, null, $borrower_id, 'loan', $loan_id, 'Peminjaman - ' . $transaction_code);
                        $pdo->prepare('INSERT INTO loan_item_serials (loan_item_id, serial_number_id) VALUES (?, ?)')->execute([$loan_item_id, $snRow['id']]);
                    }
                }

                $stockService->mutateStock($itemId, $warehouse_id, -$qty, 'LOAN', 'loans', $loan_id, 'Peminjaman ' . $transaction_code);
            }

            if ($validLines === 0) {
                throw new \\Exception('Tidak ada barang valid dalam peminjaman.');
            }

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'loans', ?)");
            $auditStmt->execute([$user_id, $loan_id]);
            $pdo->commit();
            $_SESSION['success'] = 'Peminjaman berhasil diproses.';
        } catch (\\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = 'Gagal memproses peminjaman: ' . $e->getMessage();
            header('Location: ' . url('/loans/create'));
            exit;
        }

        try {
            $actorName = AuthHelper::user()['name'];
            \\App\\Helpers\\NotificationHelper::createForRoleOnce('ADMIN', 'Peminjaman Barang Baru', $actorName . ' mengajukan peminjaman.', 'info', '/loans/show?id=' . $loan_id);
        } catch (\\Exception $notifEx) {
            error_log('[BIGNET] Notification gagal (loan create #' . $loan_id . '): ' . $notifEx->getMessage());
        }

        header('Location: ' . url('/loans'));
        exit;
    }

    public function show() {
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();

        $stmt = $pdo->prepare("SELECT l.*, w.name AS warehouse_name, u.name AS borrower_name, s.submission_code FROM loans l JOIN warehouses w ON l.warehouse_id = w.id JOIN users u ON l.borrower_id = u.id LEFT JOIN submissions s ON l.submission_id = s.id WHERE l.id = ?");
        $stmt->execute([$id]);
        $loan = $stmt->fetch();
        if (!$loan) {
            $_SESSION['error'] = 'Peminjaman tidak ditemukan.';
            header('Location: ' . url('/loans'));
            exit;
        }
        if ($user['role'] === 'TEKNISI_NOC' && (int)$loan['borrower_id'] !== (int)$user['id']) {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke peminjaman ini.';
            header('Location: ' . url('/loans'));
            exit;
        }

        $items = [];
        $itemStmt = $pdo->prepare("SELECT li.id, li.quantity, li.returned_quantity, m.nama, m.is_serialized, 'STOCK' AS source_type FROM loan_items li JOIN master_items m ON li.master_item_id = m.id WHERE li.loan_id = ?");
        $itemStmt->execute([$id]);
        foreach ($itemStmt->fetchAll() as $row) $items[] = $row;

        $manualStmt = $pdo->prepare("SELECT lmi.id, lmi.name AS nama, lmi.specification, lmi.unit, lmi.quantity, lmi.returned_quantity, lmi.master_item_id, COALESCE(m.is_serialized,0) AS is_serialized, 'MANUAL' AS source_type FROM loan_manual_items lmi LEFT JOIN master_items m ON m.id = lmi.master_item_id WHERE lmi.loan_id = ? ORDER BY lmi.id");
        $manualStmt->execute([$id]);
        foreach ($manualStmt->fetchAll() as $row) {
            $row['serial_numbers'] = [];
            $items[] = $row;
        }

        $snStmt = $pdo->prepare("SELECT sn.serial_number FROM loan_item_serials lis JOIN serial_numbers sn ON lis.serial_number_id = sn.id WHERE lis.loan_item_id = ?");
        $manualSnStmt = $pdo->prepare("SELECT sn.serial_number FROM loan_item_serials lis JOIN serial_numbers sn ON lis.serial_number_id = sn.id WHERE lis.loan_manual_item_id = ? AND lis.status IN ('PENDING','BORROWED')");
        foreach ($items as &$item) {
            if (($item['source_type'] ?? '') === 'STOCK' && !empty($item['is_serialized'])) {
                $snStmt->execute([(int)$item['id']]);
                $item['serial_numbers'] = $snStmt->fetchAll(PDO::FETCH_COLUMN);
            } elseif (($item['source_type'] ?? '') === 'MANUAL' && !empty($item['is_serialized'])) {
                $manualSnStmt->execute([(int)$item['id']]);
                $item['serial_numbers'] = $manualSnStmt->fetchAll(PDO::FETCH_COLUMN);
            }
        }
        unset($item);

        $auditStmt = $pdo->prepare("SELECT a.*, u.name AS user_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id WHERE a.entity = 'loans' AND a.entity_id = ? ORDER BY a.created_at DESC");
        $auditStmt->execute([$id]);
        $timeline = $auditStmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/loans/show.php';
    }

    public function apiItems() {
        header('Content-Type: application/json');
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode([]);
                return;
            }
            $pdo = Database::getInstance()->getConnection();
            $items = [];

            $stmt = $pdo->prepare("SELECT li.id AS loan_item_id, li.master_item_id, m.nama, m.is_serialized, li.quantity, li.returned_quantity, 'STOCK' AS source_type FROM loan_items li JOIN master_items m ON li.master_item_id = m.id WHERE li.loan_id = ? AND li.quantity > li.returned_quantity");
            $stmt->execute([$id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stockSnStmt = $pdo->prepare("SELECT sn.serial_number FROM loan_item_serials lis JOIN serial_numbers sn ON lis.serial_number_id = sn.id WHERE lis.loan_item_id = ? AND lis.status IN ('PENDING','BORROWED') ORDER BY sn.serial_number");
            foreach ($items as &$item) {
                $item['serial_numbers'] = [];
                if (!empty($item['is_serialized'])) {
                    $stockSnStmt->execute([(int)$item['loan_item_id']]);
                    $item['serial_numbers'] = $stockSnStmt->fetchAll(PDO::FETCH_COLUMN);
                }
            }
            unset($item);

            $manualStmt = $pdo->prepare("SELECT lmi.id AS loan_item_id, lmi.master_item_id, lmi.name AS nama, COALESCE(m.is_serialized,0) AS is_serialized, lmi.quantity, lmi.returned_quantity, 'MANUAL' AS source_type, lmi.specification, lmi.unit FROM loan_manual_items lmi LEFT JOIN master_items m ON m.id = lmi.master_item_id WHERE lmi.loan_id = ? AND lmi.quantity > lmi.returned_quantity");
            $manualStmt->execute([$id]);
            $manualItems = $manualStmt->fetchAll(PDO::FETCH_ASSOC);

            $manualSnStmt = $pdo->prepare("SELECT sn.serial_number FROM loan_item_serials lis JOIN serial_numbers sn ON lis.serial_number_id = sn.id WHERE lis.loan_manual_item_id = ? AND lis.status IN ('PENDING','BORROWED') ORDER BY sn.serial_number");
            foreach ($manualItems as &$item) {
                $item['serial_numbers'] = [];
                if (!empty($item['is_serialized'])) {
                    $manualSnStmt->execute([(int)$item['loan_item_id']]);
                    $item['serial_numbers'] = $manualSnStmt->fetchAll(PDO::FETCH_COLUMN);
                }
            }
            unset($item);

            $items = array_merge($items, $manualItems);
            echo json_encode($items);
        } catch (\\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
