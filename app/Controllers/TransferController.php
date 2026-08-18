<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use App\Services\StockService;
use PDO;
use Exception;

class TransferController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClauses = ["1=1"];
        $params = [];
        if ($search !== '') {
            $whereClauses[] = "t.transaction_code LIKE ?";
            $params[] = "%$search%";
        }
        if ($status !== '') {
            $whereClauses[] = "t.status = ?";
            $params[] = $status;
        }
        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM transfers t $whereSql");
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $stmt = $pdo->prepare("SELECT t.*, sw.name as source_warehouse, dw.name as dest_warehouse, u.name as sender_name
                FROM transfers t
                JOIN warehouses sw ON t.source_warehouse_id = sw.id
                JOIN warehouses dw ON t.destination_warehouse_id = dw.id
                JOIN users u ON t.sent_by = u.id
                $whereSql
                ORDER BY t.id DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $transfers = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/transfers/index.php';
    }

    public function show() {
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare("SELECT t.*, sw.name as source_warehouse, dw.name as dest_warehouse, u.name as sender_name, ur.name as receiver_name
                FROM transfers t
                JOIN warehouses sw ON t.source_warehouse_id = sw.id
                JOIN warehouses dw ON t.destination_warehouse_id = dw.id
                JOIN users u ON t.sent_by = u.id
                LEFT JOIN users ur ON t.received_by = ur.id
                WHERE t.id = ?");
        $stmt->execute([$id]);
        $transfer = $stmt->fetch();

        if (!$transfer) {
            $_SESSION['error'] = "Transfer not found.";
            header("Location: " . url('/transfers'));
            exit;
        }

        $itemsStmt = $pdo->prepare("SELECT ti.*, m.kode_barang, m.nama, m.is_serialized
                                    FROM transfer_items ti
                                    JOIN master_items m ON ti.master_item_id = m.id
                                    WHERE ti.transfer_id = ?");
        $itemsStmt->execute([$id]);
        $items = $itemsStmt->fetchAll();

        $attachmentStmt = $pdo->prepare("SELECT * FROM attachments WHERE reference_id = ? AND reference_type IN ('transfer_dispatch', 'transfer_receive') ORDER BY id ASC");
        $attachmentStmt->execute([$id]);
        $attachments = $attachmentStmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/transfers/show.php';
    }

    public function create() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        $pdo = Database::getInstance()->getConnection();
        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();
        $items = $pdo->query("SELECT s.warehouse_id, s.master_item_id AS id, m.kode_barang, m.nama, m.is_serialized, s.quantity AS stock_quantity
                              FROM stocks s
                              JOIN master_items m ON m.id = s.master_item_id
                              WHERE m.is_active = 1 AND s.quantity > 0
                              ORDER BY m.nama ASC, s.warehouse_id ASC")->fetchAll();
        require __DIR__ . '/../../resources/views/transactions/transfers/create.php';
    }

    public function store() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF validation failed.";
            header("Location: " . url('/transfers/create'));
            exit;
        }

        $source_warehouse_id = (int)($_POST['source_warehouse_id'] ?? 0);
        $destination_warehouse_id = (int)($_POST['destination_warehouse_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $item_ids = $_POST['item_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $sn_list = $_POST['sn_list'] ?? [];

        if ($source_warehouse_id <= 0 || $destination_warehouse_id <= 0) {
            $_SESSION['error'] = "Gudang asal dan tujuan wajib dipilih.";
            header("Location: " . url('/transfers/create'));
            exit;
        }
        if ($source_warehouse_id === $destination_warehouse_id) {
            $_SESSION['error'] = "Gudang asal dan tujuan tidak boleh sama.";
            header("Location: " . url('/transfers/create'));
            exit;
        }
        if (empty($item_ids)) {
            $_SESSION['error'] = "Minimal satu barang wajib dipilih.";
            header("Location: " . url('/transfers/create'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $stockService = new StockService();
        $user_id = AuthHelper::user()['id'];
        $transaction_code = \App\Helpers\TransactionHelper::generateCode('TRF', 'transfers');
        $uploadedFilePath = null;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO transfers (transaction_code, source_warehouse_id, destination_warehouse_id, status, sent_by, notes) VALUES (?, ?, ?, 'IN_TRANSIT', ?, ?)");
            $stmt->execute([$transaction_code, $source_warehouse_id, $destination_warehouse_id, $user_id, $notes]);
            $transfer_id = (int)$pdo->lastInsertId();

            if (isset($_FILES['dispatch_photo']) && ($_FILES['dispatch_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $uploadedFilePath = $this->storeTransferAttachment($_FILES['dispatch_photo'], $transfer_id, 'transfer_dispatch', $user_id, $pdo);
            }

            $itemStmt = $pdo->prepare("INSERT INTO transfer_items (transfer_id, master_item_id, quantity_sent) VALUES (?, ?, ?)");
            foreach ($item_ids as $index => $item_id) {
                $qty = (int)($quantities[$index] ?? 0);
                if ($qty <= 0) continue;

                $itemStmt->execute([$transfer_id, (int)$item_id, $qty]);
                $transfer_item_id = $pdo->lastInsertId();

                $mItemStmt = $pdo->prepare("SELECT is_serialized FROM master_items WHERE id = ?");
                $mItemStmt->execute([(int)$item_id]);
                $mItem = $mItemStmt->fetch();

                if ($mItem && $mItem['is_serialized']) {
                    $rawSn = $sn_list[$index] ?? '';
                    $snArray = array_filter(array_map('trim', preg_split('/[\n,]+/', $rawSn)));
                    if (count($snArray) !== $qty) {
                        throw new Exception("Jumlah Serial Number tidak cocok dengan kuantitas untuk barang ID $item_id. Diminta: $qty, Diisi: " . count($snArray));
                    }
                    if (count($snArray) !== count(array_unique($snArray))) {
                        throw new Exception("Ada Serial Number duplikat pada input barang ID $item_id.");
                    }

                    foreach ($snArray as $sn) {
                        $chk = $pdo->prepare("SELECT id FROM serial_numbers WHERE serial_number = ? AND current_warehouse_id = ? AND status = 'AVAILABLE' FOR UPDATE");
                        $chk->execute([$sn, $source_warehouse_id]);
                        $snRow = $chk->fetch();
                        if (!$snRow) {
                            throw new Exception("Serial Number '$sn' tidak tersedia di Gudang Asal ini.");
                        }
                        $stockService->moveSerial($snRow['id'], 'IN_TRANSIT', null, null, null, 'transfer', $transfer_id, "Transfer Keluar - " . $transaction_code);
                        $pdo->prepare("INSERT INTO transfer_item_serials (transfer_item_id, serial_number_id, status) VALUES (?, ?, 'IN_TRANSIT')")->execute([$transfer_item_id, $snRow['id']]);
                    }
                }

                $stockService->mutateStock($item_id, $source_warehouse_id, -$qty, 'TRANSFER_OUT', 'transfers', $transfer_id, "Transfer Keluar $transaction_code");
            }

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'transfers', ?)");
            $auditStmt->execute([$user_id, $transfer_id]);
            $pdo->commit();
            $_SESSION['success'] = "Transfer created successfully.";
        } catch (Exception $e) {
            $pdo->rollBack();
            if ($uploadedFilePath) {
                $absolutePath = __DIR__ . '/../../storage/private_uploads/' . $uploadedFilePath;
                if (is_file($absolutePath)) @unlink($absolutePath);
            }
            $_SESSION['error'] = "Error processing transfer: " . $e->getMessage();
            header("Location: " . url('/transfers/create'));
            exit;
        }

        header("Location: " . url('/transfers'));
        exit;
    }

    public function receive() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF validation failed.";
            header("Location: " . url('/transfers'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $receive_qtys = $_POST['receive_qtys'] ?? [];
        $receive_sns = $_POST['receive_sns'] ?? [];
        $notes_receive = trim($_POST['notes_receive'] ?? '');

        $pdo = Database::getInstance()->getConnection();
        $stockService = new StockService();
        $user_id = AuthHelper::user()['id'];
        $uploadedFilePath = null;

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT * FROM transfers WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $transfer = $stmt->fetch();
            if (!$transfer || !in_array($transfer['status'], ['IN_TRANSIT', 'PARTIAL_RECEIVED'])) {
                throw new Exception("Transfer is not valid or already completed.");
            }

            if (isset($_FILES['receive_photo']) && ($_FILES['receive_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $uploadedFilePath = $this->storeTransferAttachment($_FILES['receive_photo'], $id, 'transfer_receive', $user_id, $pdo);
            }

            $itemsStmt = $pdo->prepare("SELECT ti.*, m.is_serialized FROM transfer_items ti JOIN master_items m ON ti.master_item_id = m.id WHERE ti.transfer_id = ? FOR UPDATE");
            $itemsStmt->execute([$id]);
            $items = $itemsStmt->fetchAll();
            $updItemStmt = $pdo->prepare("UPDATE transfer_items SET quantity_received = quantity_received + ? WHERE id = ?");
            $all_completed = true;

            foreach ($items as $item) {
                $receive_qty = (int)($receive_qtys[$item['id']] ?? 0);
                if ($receive_qty <= 0) {
                    if ($item['quantity_received'] < $item['quantity_sent']) $all_completed = false;
                    continue;
                }
                $remaining = $item['quantity_sent'] - $item['quantity_received'];
                if ($receive_qty > $remaining) {
                    throw new Exception("Kuantitas terima melebihi sisa yang belum diterima untuk barang ID " . $item['master_item_id']);
                }

                if ($item['is_serialized']) {
                    $rawSn = $receive_sns[$item['id']] ?? '';
                    $snArray = array_filter(array_map('trim', preg_split('/[\n,]+/', $rawSn)));
                    if (count($snArray) !== $receive_qty) {
                        throw new Exception("Jumlah Serial Number tidak cocok dengan kuantitas terima untuk barang ID " . $item['master_item_id']);
                    }
                    if (count($snArray) !== count(array_unique($snArray))) {
                        throw new Exception("Ada Serial Number duplikat pada form penerimaan barang ID " . $item['master_item_id']);
                    }
                    foreach ($snArray as $sn) {
                        $chk = $pdo->prepare("SELECT sn.id FROM serial_numbers sn JOIN transfer_item_serials tis ON sn.id = tis.serial_number_id WHERE sn.serial_number = ? AND tis.transfer_item_id = ? AND tis.status = 'IN_TRANSIT' AND sn.status = 'IN_TRANSIT' FOR UPDATE");
                        $chk->execute([$sn, $item['id']]);
                        $snRow = $chk->fetch();
                        if (!$snRow) {
                            throw new Exception("Serial Number '$sn' tidak terdaftar dalam pengiriman ini atau sudah diterima.");
                        }
                        $stockService->moveSerial($snRow['id'], 'AVAILABLE', $transfer['destination_warehouse_id'], null, null, 'transfer', $transfer['id'], "Transfer Diterima - " . $transfer['transaction_code']);
                        $pdo->prepare("UPDATE transfer_item_serials SET status = 'RECEIVED' WHERE transfer_item_id = ? AND serial_number_id = ?")->execute([$item['id'], $snRow['id']]);
                    }
                }

                $updItemStmt->execute([$receive_qty, $item['id']]);
                $stockService->mutateStock($item['master_item_id'], $transfer['destination_warehouse_id'], $receive_qty, 'TRANSFER_IN', 'transfers', $transfer['id'], "Transfer In " . $transfer['transaction_code']);
                if (($item['quantity_received'] + $receive_qty) < $item['quantity_sent']) $all_completed = false;
            }

            if ($all_completed) {
                $updTransStmt = $pdo->prepare("UPDATE transfers SET status = 'COMPLETED', received_by = ?, received_at = NOW() WHERE id = ?");
                $updTransStmt->execute([$user_id, $id]);
            } else {
                $updTransStmt = $pdo->prepare("UPDATE transfers SET status = 'PARTIAL_RECEIVED' WHERE id = ?");
                $updTransStmt->execute([$id]);
            }

            if (!empty($notes_receive)) {
                $newNotes = $transfer['notes'] . "\n[Penerimaan " . date('Y-m-d H:i') . "]: " . $notes_receive;
                $pdo->prepare("UPDATE transfers SET notes = ? WHERE id = ?")->execute([$newNotes, $id]);
            }

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'RECEIVE_TRANSFER', 'transfers', ?)");
            $auditStmt->execute([$user_id, $id]);
            $pdo->commit();
            $_SESSION['success'] = "Penerimaan transfer berhasil diproses.";
        } catch (Exception $e) {
            $pdo->rollBack();
            if ($uploadedFilePath) {
                $absolutePath = __DIR__ . '/../../storage/private_uploads/' . $uploadedFilePath;
                if (is_file($absolutePath)) @unlink($absolutePath);
            }
            $_SESSION['error'] = "Gagal menerima transfer: " . $e->getMessage();
        }

        header("Location: " . url('/transfers'));
        exit;
    }

    private function storeTransferAttachment(array $file, int $transferId, string $referenceType, int $userId, PDO $pdo): string {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new Exception("Upload foto gagal.");
        }
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            throw new Exception("Ukuran foto maksimal 5 MB.");
        }

        $tmpPath = $file['tmp_name'] ?? '';
        $mimeType = $tmpPath !== '' ? mime_content_type($tmpPath) : false;
        $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$mimeType || !isset($allowedMimes[$mimeType])) {
            throw new Exception("Format foto harus JPG, PNG, atau WEBP.");
        }

        $uploadDir = __DIR__ . '/../../storage/private_uploads/transfers/' . $transferId . '/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new Exception("Folder upload foto tidak dapat dibuat.");
        }

        $filename = $referenceType . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mimeType];
        $destination = $uploadDir . $filename;
        if (!move_uploaded_file($tmpPath, $destination)) {
            throw new Exception("Foto gagal disimpan.");
        }

        $relativePath = 'transfers/' . $transferId . '/' . $filename;
        $originalName = basename($file['name'] ?? $filename);
        $stmt = $pdo->prepare("INSERT INTO attachments (reference_type, reference_id, file_path, original_name, mime_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$referenceType, $transferId, $relativePath, $originalName, $mimeType, (int)$file['size'], $userId]);
        return $relativePath;
    }
}
