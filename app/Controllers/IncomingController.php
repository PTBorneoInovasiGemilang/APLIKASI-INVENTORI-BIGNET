<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use App\Services\StockService;
use PDO;

class IncomingController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        $search = trim($_GET['search'] ?? '');
        $warehouseId = isset($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : 0;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClauses = ["1=1"];
        $params = [];

        if ($warehouseId > 0) {
            $whereClauses[] = "i.warehouse_id = ?";
            $params[] = $warehouseId;
        }

        if ($search !== '') {
            $whereClauses[] = "(i.transaction_code LIKE ? OR i.supplier_info LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        // Count for pagination
        $countSql = "SELECT COUNT(*) FROM incoming i $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "SELECT i.*, w.name as warehouse_name, u.name as receiver_name 
                FROM incoming i 
                JOIN warehouses w ON i.warehouse_id = w.id 
                JOIN users u ON i.received_by = u.id 
                $whereSql
                ORDER BY i.id DESC LIMIT $limit OFFSET $offset";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $incomings = $stmt->fetchAll();

        // Ringkasan barang per transaksi (list Barang Masuk wajib menampilkan quantity).
        $incomingSummary = [];
        $incomingItems = [];
        $aggStmt = $pdo->query("SELECT incoming_id, COUNT(DISTINCT master_item_id) AS jenis, SUM(quantity) AS total_qty FROM incoming_items GROUP BY incoming_id");
        foreach ($aggStmt as $row) {
            $incomingSummary[(int)$row['incoming_id']] = ['jenis' => (int)$row['jenis'], 'total_qty' => (int)$row['total_qty']];
        }
        $itemAggStmt = $pdo->query("SELECT ii.incoming_id, m.nama, u.name AS unit_name, ii.quantity 
                                    FROM incoming_items ii 
                                    JOIN master_items m ON ii.master_item_id = m.id 
                                    LEFT JOIN units u ON m.unit_id = u.id 
                                    ORDER BY ii.id");
        foreach ($itemAggStmt as $row) {
            $incomingItems[(int)$row['incoming_id']][] = $row;
        }

        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/incoming/index.php';
    }

    public function create() {
        AuthHelper::requireRole('ADMIN');
        $pdo = Database::getInstance()->getConnection();
        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();
        $items = $pdo->query("SELECT id, kode_barang, nama, is_serialized FROM master_items WHERE is_active = 1")->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/incoming/create.php';
    }

    public function store() {
        AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed.";
            header("Location: " . url('/incoming/create'));
            exit;
        }

        $warehouse_id = (int)$_POST['warehouse_id'];
        $supplier_info = trim($_POST['supplier_info']);
        $notes = trim($_POST['notes']);
        $item_ids = $_POST['item_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $sn_list = $_POST['sn_list'] ?? [];

        if (empty($item_ids)) {
            $_SESSION['error'] = "Tidak ada barang yang dipilih.";
            header("Location: " . url('/incoming/create'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $stockService = new StockService();
        $user_id = AuthHelper::user()['id'];
        $transaction_code = \App\Helpers\TransactionHelper::generateCode('INC', 'incoming');

        try {
            $pdo->beginTransaction();

            // Insert incoming record
            $stmt = $pdo->prepare("INSERT INTO incoming (transaction_code, warehouse_id, received_by, supplier_info, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$transaction_code, $warehouse_id, $user_id, $supplier_info, $notes]);
            $incoming_id = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO incoming_items (incoming_id, master_item_id, quantity) VALUES (?, ?, ?)");

            foreach ($item_ids as $index => $item_id) {
                $qty = (int)$quantities[$index];
                if ($qty <= 0) continue;

                // INVENTORY IDENTITY GUARD: master_item_id wajib valid & aktif. Jika tidak
                // valid, FAIL SAFELY (jangan fallback ke kategori/nama/barang pertama).
                $mItem = $pdo->query("SELECT is_serialized FROM master_items WHERE id = " . (int)$item_id . " AND is_active = 1")->fetch();
                if (!$mItem) {
                    throw new \Exception("Master Barang tidak valid atau nonaktif (ID: " . (int)$item_id . "). Transaksi dibatalkan — tidak ada stok yang diubah.");
                }

                if ($mItem['is_serialized']) {
                    $rawSn = $sn_list[$index] ?? '';
                    // parse SN
                    $snArray = array_filter(array_map('trim', preg_split('/[\n,]+/', $rawSn)));
                    if (count($snArray) !== $qty) {
                        throw new \Exception("Jumlah Serial Number tidak cocok dengan kuantitas untuk barang ID $item_id. Diminta: $qty, Diisi: " . count($snArray));
                    }
                    
                    // Validate duplicate SNs in the input array
                    if (count($snArray) !== count(array_unique($snArray))) {
                         throw new \Exception("Ada Serial Number duplikat pada input barang ID $item_id.");
                    }
                    
                    foreach ($snArray as $sn) {
                        // Check if SN already exists in DB
                        $chk = $pdo->prepare("SELECT id FROM serial_numbers WHERE serial_number = ?");
                        $chk->execute([$sn]);
                        if ($chk->fetch()) {
                            throw new \Exception("Serial Number '$sn' sudah terdaftar di sistem.");
                        }
                        
                        // Insert SN
                        $insSn = $pdo->prepare("INSERT INTO serial_numbers (master_item_id, serial_number, status, current_warehouse_id) VALUES (?, ?, 'AVAILABLE', ?)");
                        $insSn->execute([$item_id, $sn, $warehouse_id]);
                        $snId = $pdo->lastInsertId();
                        
                        // Insert movement
                        $insMov = $pdo->prepare("INSERT INTO serial_movements (serial_number_id, from_status, to_status, from_warehouse_id, to_warehouse_id, from_holder_id, to_holder_id, reference_type, reference_id, performed_by, notes) VALUES (?, NULL, 'AVAILABLE', NULL, ?, NULL, NULL, 'incoming', ?, ?, ?)");
                        $insMov->execute([$snId, $warehouse_id, $incoming_id, $user_id, "Barang masuk - " . $transaction_code]);
                    }
                }

                $itemStmt->execute([$incoming_id, $item_id, $qty]);

                // Mutate stock
                $stockService->mutateStock($item_id, $warehouse_id, $qty, 'INCOMING', 'incoming', $incoming_id, "Incoming $transaction_code");
            }

            // Audit Log
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'incoming', ?)");
            $auditStmt->execute([$user_id, $incoming_id]);

            $pdo->commit();
            $_SESSION['success'] = "Barang Masuk berhasil diproses.";
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal memproses barang masuk: " . $e->getMessage();
            header("Location: " . url('/incoming/create'));
            exit;
        }

        header("Location: " . url('/incoming'));
        exit;
    }
}
