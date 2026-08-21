<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use App\Services\StockService;
use PDO;
use Exception;

class TransferController
{
    public function __construct()
    {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index()
    {
        $pdo = Database::getInstance()->getConnection();
        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClauses = ['1=1'];
        $params = [];
        if ($search !== '') {
            $whereClauses[] = 't.transaction_code LIKE ?';
            $params[] = "%$search%";
        }
        if ($status !== '') {
            $whereClauses[] = 't.status = ?';
            $params[] = $status;
        }
        $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);

        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM transfers t $whereSql");
        $stmtCount->execute($params);
        $totalItems = (int)$stmtCount->fetchColumn();
        $totalPages = (int)ceil($totalItems / $limit);

        $stmt = $pdo->prepare("SELECT t.*, sw.name AS source_warehouse, dw.name AS dest_warehouse, u.name AS sender_name
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

    public function show()
    {
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare("SELECT t.*, sw.name AS source_warehouse, dw.name AS dest_warehouse,
                    u.name AS sender_name,
                    r.receiver_name, r.receiver_role, r.receiver_phone, r.received_at AS receiver_received_at,
                    r.notes AS receiver_notes
                FROM transfers t
                JOIN warehouses sw ON t.source_warehouse_id = sw.id
                JOIN warehouses dw ON t.destination_warehouse_id = dw.id
                JOIN users u ON t.sent_by = u.id
                LEFT JOIN transfer_receipts r ON r.id = (
                    SELECT tr2.id FROM transfer_receipts tr2
                    WHERE tr2.transfer_id = t.id
                    ORDER BY tr2.id DESC LIMIT 1
                )
                WHERE t.id = ?");
        $stmt->execute([$id]);
        $transfer = $stmt->fetch();

        if (!$transfer) {
            $_SESSION['error'] = 'Transfer tidak ditemukan.';
            header('Location: ' . url('/transfers'));
            exit;
        }

        $itemsStmt = $pdo->prepare("SELECT ti.*, m.kode_barang, m.nama, m.is_serialized
            FROM transfer_items ti
            JOIN master_items m ON ti.master_item_id = m.id
            WHERE ti.transfer_id = ?
            ORDER BY ti.id ASC");
        $itemsStmt->execute([$id]);
        $items = $itemsStmt->fetchAll();

        $attachmentStmt = $pdo->prepare("SELECT * FROM attachments
            WHERE reference_id = ? AND reference_type IN ('transfer_dispatch', 'transfer_receive')
            ORDER BY id ASC");
        $attachmentStmt->execute([$id]);
        $attachments = $attachmentStmt->fetchAll();

        $receiptStmt = $pdo->prepare("SELECT tr.*, u.name AS processed_by_name
            FROM transfer_receipts tr
            LEFT JOIN users u ON tr.processed_by = u.id
            WHERE tr.transfer_id = ?
            ORDER BY tr.id DESC");
        $receiptStmt->execute([$id]);
        $receipts = $receiptStmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/transfers/show.php';
    }

    public function create()
    {
        AuthHelper::requireRole('ADMIN');
        $pdo = Database::getInstance()->getConnection();
        $warehouses = $pdo->query('SELECT * FROM warehouses WHERE is_active = 1 ORDER BY name ASC')->fetchAll();
        $items = $pdo->query("SELECT s.warehouse_id, s.master_item_id AS id, m.kode_barang, m.nama, m.is_serialized, s.quantity AS stock_quantity
            FROM stocks s
            JOIN master_items m ON m.id = s.master_item_id
            WHERE m.is_active = 1 AND s.quantity > 0
            ORDER BY m.nama ASC, s.warehouse_id ASC")->fetchAll();
        require __DIR__ . '/../../resources/views/transactions/transfers/create.php';
    }

    public function store()
    {
        AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'CSRF validation failed.';
            header('Location: ' . url('/transfers/create'));
            exit;
        }

        $sourceWarehouseId = (int)($_POST['source_warehouse_id'] ?? 0);
        $destinationWarehouseId = (int)($_POST['destination_warehouse_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $driverName = trim($_POST['driver_name'] ?? '');
        $driverPhone = trim($_POST['driver_phone'] ?? '');
        $vehicleNumber = trim($_POST['vehicle_number'] ?? '');
        $itemIds = $_POST['item_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $snList = $_POST['sn_list'] ?? [];

        if ($sourceWarehouseId <= 0 || $destinationWarehouseId <= 0) {
            $_SESSION['error'] = 'Gudang asal dan tujuan wajib dipilih.';
            header('Location: ' . url('/transfers/create'));
            exit;
        }
        if ($sourceWarehouseId === $destinationWarehouseId) {
            $_SESSION['error'] = 'Gudang asal dan tujuan tidak boleh sama.';
            header('Location: ' . url('/transfers/create'));
            exit;
        }
        if ($driverName === '') {
            $_SESSION['error'] = 'Nama supir / pembawa barang wajib diisi.';
            header('Location: ' . url('/transfers/create'));
            exit;
        }
        if (empty($itemIds)) {
            $_SESSION['error'] = 'Minimal satu barang wajib dipilih.';
            header('Location: ' . url('/transfers/create'));
            exit;
        }
        if (!isset($_FILES['dispatch_photo']) || ($_FILES['dispatch_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Foto bukti pengiriman wajib diunggah.';
            header('Location: ' . url('/transfers/create'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $stockService = new StockService();
        $userId = (int)AuthHelper::user()['id'];
        $transactionCode = \App\Helpers\TransactionHelper::generateCode('TRF', 'transfers');
        $uploadedFilePath = null;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO transfers
                (transaction_code, source_warehouse_id, destination_warehouse_id, status, sent_by, driver_name, driver_phone, vehicle_number, notes)
                VALUES (?, ?, ?, 'IN_TRANSIT', ?, ?, ?, ?, ?)");
            $stmt->execute([
                $transactionCode,
                $sourceWarehouseId,
                $destinationWarehouseId,
                $userId,
                $driverName,
                $driverPhone !== '' ? $driverPhone : null,
                $vehicleNumber !== '' ? $vehicleNumber : null,
                $notes
            ]);
            $transferId = (int)$pdo->lastInsertId();

            $uploadedFilePath = $this->storeTransferAttachment($_FILES['dispatch_photo'], $transferId, 'transfer_dispatch', $userId, $pdo);

            $itemStmt = $pdo->prepare('INSERT INTO transfer_items (transfer_id, master_item_id, quantity_sent) VALUES (?, ?, ?)');
            foreach ($itemIds as $index => $rawItemId) {
                $itemId = (int)$rawItemId;
                $qty = (int)($quantities[$index] ?? 0);
                if ($itemId <= 0 || $qty <= 0) continue;

                $stockStmt = $pdo->prepare('SELECT quantity FROM stocks WHERE warehouse_id = ? AND master_item_id = ? FOR UPDATE');
                $stockStmt->execute([$sourceWarehouseId, $itemId]);
                $availableStock = (int)($stockStmt->fetchColumn() ?? 0);
                if ($availableStock < $qty) {
                    throw new Exception("Stok tidak mencukupi di gudang asal. Tersedia: $availableStock, diminta: $qty.");
                }

                $itemStmt->execute([$transferId, $itemId, $qty]);
                $transferItemId = (int)$pdo->lastInsertId();

                $mItemStmt = $pdo->prepare('SELECT is_serialized FROM master_items WHERE id = ?');
                $mItemStmt->execute([$itemId]);
                $mItem = $mItemStmt->fetch();

                if ($mItem && (int)$mItem['is_serialized'] === 1) {
                    $rawSn = $snList[$index] ?? '';
                    $snArray = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $rawSn))));
                    if (count($snArray) !== $qty) {
                        throw new Exception("Jumlah nomor seri tidak cocok dengan jumlah barang.");
                    }
                    if (count($snArray) !== count(array_unique($snArray))) {
                        throw new Exception('Ada nomor seri duplikat pada barang yang dipilih.');
                    }

                    foreach ($snArray as $sn) {
                        $chk = $pdo->prepare("SELECT id FROM serial_numbers
                            WHERE serial_number = ? AND current_warehouse_id = ? AND status = 'AVAILABLE' FOR UPDATE");
                        $chk->execute([$sn, $sourceWarehouseId]);
                        $snRow = $chk->fetch();
                        if (!$snRow) {
                            throw new Exception("Nomor seri '$sn' tidak tersedia di gudang asal ini.");
                        }

                        $stockService->moveSerial(
                            $snRow['id'],
                            'IN_TRANSIT',
                            null,
                            null,
                            null,
                            'transfer',
                            $transferId,
                            "Transfer Keluar - $transactionCode"
                        );
                        $pdo->prepare("INSERT INTO transfer_item_serials (transfer_item_id, serial_number_id, status) VALUES (?, ?, 'IN_TRANSIT')")
                            ->execute([$transferItemId, $snRow['id']]);
                    }
                }

                $stockService->mutateStock($itemId, $sourceWarehouseId, -$qty, 'TRANSFER_OUT', 'transfers', $transferId, "Transfer Keluar $transactionCode");
            }

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, new_data) VALUES (?, 'CREATE', 'transfers', ?, ?)");
            $auditStmt->execute([$userId, $transferId, json_encode([
                'transaction_code' => $transactionCode,
                'driver_name' => $driverName,
                'driver_phone' => $driverPhone,
                'vehicle_number' => $vehicleNumber,
            ], JSON_UNESCAPED_UNICODE)]);

            $pdo->commit();
            $_SESSION['success'] = 'Transfer berhasil dibuat.';
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($uploadedFilePath) {
                $absolutePath = __DIR__ . '/../../storage/private_uploads/' . $uploadedFilePath;
                if (is_file($absolutePath)) @unlink($absolutePath);
            }
            $_SESSION['error'] = 'Gagal membuat transfer: ' . $e->getMessage();
            header('Location: ' . url('/transfers/create'));
            exit;
        }

        header('Location: ' . url('/transfers'));
        exit;
    }

    public function delete()
    {
        AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'CSRF validation failed.';
            header('Location: ' . url('/transfers'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $pdo = Database::getInstance()->getConnection();
        $stockService = new StockService();
        $userId = (int)AuthHelper::user()['id'];
        $filesToDelete = [];

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT * FROM transfers WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $transfer = $stmt->fetch();
            if (!$transfer) throw new Exception('Transfer tidak ditemukan.');
            if ($transfer['status'] !== 'IN_TRANSIT') throw new Exception('Transfer hanya dapat dibatalkan saat masih Dalam Pengiriman.');

            $itemsStmt = $pdo->prepare('SELECT ti.*, m.is_serialized FROM transfer_items ti JOIN master_items m ON m.id = ti.master_item_id WHERE ti.transfer_id = ? FOR UPDATE');
            $itemsStmt->execute([$id]);
            foreach ($itemsStmt->fetchAll() as $item) {
                $qty = (int)$item['quantity_sent'];
                if ($item['is_serialized']) {
                    $snStmt = $pdo->prepare("SELECT sn.id FROM serial_numbers sn JOIN transfer_item_serials tis ON tis.serial_number_id = sn.id WHERE tis.transfer_item_id = ? AND tis.status = 'IN_TRANSIT' FOR UPDATE");
                    $snStmt->execute([$item['id']]);
                    foreach ($snStmt->fetchAll(PDO::FETCH_COLUMN) as $snId) {
                        $stockService->moveSerial((int)$snId, 'AVAILABLE', $transfer['source_warehouse_id'], null, null, 'transfer_delete', $id, 'Pembatalan transfer - serial dikembalikan ke gudang asal');
                    }
                }
                if ($qty > 0) {
                    $stockService->mutateStock($item['master_item_id'], $transfer['source_warehouse_id'], $qty, 'TRANSFER_CANCEL', 'transfers', $id, 'Pembatalan transfer ' . $transfer['transaction_code']);
                }
            }

            $attStmt = $pdo->prepare("SELECT file_path FROM attachments WHERE reference_id = ? AND reference_type IN ('transfer_dispatch', 'transfer_receive')");
            $attStmt->execute([$id]);
            $filesToDelete = $attStmt->fetchAll(PDO::FETCH_COLUMN);
            $pdo->prepare("DELETE FROM attachments WHERE reference_id = ? AND reference_type IN ('transfer_dispatch', 'transfer_receive')")->execute([$id]);
            $pdo->prepare('DELETE FROM transfer_item_serials WHERE transfer_item_id IN (SELECT id FROM transfer_items WHERE transfer_id = ?)')->execute([$id]);
            $pdo->prepare('DELETE FROM transfer_items WHERE transfer_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM transfer_receipts WHERE transfer_id = ?')->execute([$id]);

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, old_data, new_data) VALUES (?, 'DELETE', 'transfers', ?, ?, ?)");
            $auditStmt->execute([$userId, $id, json_encode(['transaction_code' => $transfer['transaction_code'], 'status' => $transfer['status']]), json_encode(['reason' => 'ADMIN_CANCEL_TRANSFER'])]);
            $pdo->prepare('DELETE FROM transfers WHERE id = ?')->execute([$id]);

            $pdo->commit();
            foreach ($filesToDelete as $relativePath) {
                $absolutePath = __DIR__ . '/../../storage/private_uploads/' . ltrim($relativePath, '/');
                if (is_file($absolutePath)) @unlink($absolutePath);
            }
            $_SESSION['success'] = 'Transfer dibatalkan. Stok dan nomor seri dikembalikan ke gudang asal.';
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = 'Gagal membatalkan transfer: ' . $e->getMessage();
        }

        header('Location: ' . url('/transfers'));
        exit;
    }

    public function receive()
    {
        AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'CSRF validation failed.';
            header('Location: ' . url('/transfers'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $receiveQtys = $_POST['receive_qtys'] ?? [];
        $receiveSns = $_POST['receive_sns'] ?? [];
        $notesReceive = trim($_POST['notes_receive'] ?? '');
        $receiverName = trim($_POST['receiver_name'] ?? '');
        $receiverRole = trim($_POST['receiver_role'] ?? '');
        $receiverPhone = trim($_POST['receiver_phone'] ?? '');

        if ($receiverName === '') {
            $_SESSION['error'] = 'Nama penerima wajib diisi.';
            header('Location: ' . url('/transfers/show?id=' . $id));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $stockService = new StockService();
        $userId = (int)AuthHelper::user()['id'];
        $uploadedFilePath = null;

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT * FROM transfers WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $transfer = $stmt->fetch();
            if (!$transfer || !in_array($transfer['status'], ['IN_TRANSIT', 'PARTIAL_RECEIVED'], true)) {
                throw new Exception('Transfer tidak valid atau sudah selesai.');
            }
            if (!isset($_FILES['receive_photo']) || ($_FILES['receive_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new Exception('Foto bukti penerimaan wajib diunggah.');
            }

            $hasReceivedQty = false;
            foreach ($receiveQtys as $qty) {
                if ((int)$qty > 0) { $hasReceivedQty = true; break; }
            }
            if (!$hasReceivedQty) throw new Exception('Minimal satu barang harus diterima.');

            $uploadedFilePath = $this->storeTransferAttachment($_FILES['receive_photo'], $id, 'transfer_receive', $userId, $pdo);

            $itemsStmt = $pdo->prepare('SELECT ti.*, m.is_serialized FROM transfer_items ti JOIN master_items m ON ti.master_item_id = m.id WHERE ti.transfer_id = ? FOR UPDATE');
            $itemsStmt->execute([$id]);
            $items = $itemsStmt->fetchAll();
            $updItemStmt = $pdo->prepare('UPDATE transfer_items SET quantity_received = quantity_received + ? WHERE id = ?');
            $allCompleted = true;
            $receivedSummary = [];

            foreach ($items as $item) {
                $receiveQty = (int)($receiveQtys[$item['id']] ?? 0);
                if ($receiveQty <= 0) {
                    if ((int)$item['quantity_received'] < (int)$item['quantity_sent']) $allCompleted = false;
                    continue;
                }

                $remaining = (int)$item['quantity_sent'] - (int)$item['quantity_received'];
                if ($receiveQty > $remaining) throw new Exception('Kuantitas terima melebihi sisa barang.');

                if ($item['is_serialized']) {
                    $rawSn = $receiveSns[$item['id']] ?? '';
                    $snArray = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $rawSn))));
                    if (count($snArray) !== $receiveQty) throw new Exception('Jumlah nomor seri tidak cocok dengan jumlah yang diterima.');
                    if (count($snArray) !== count(array_unique($snArray))) throw new Exception('Ada nomor seri duplikat pada penerimaan.');

                    foreach ($snArray as $sn) {
                        $chk = $pdo->prepare("SELECT sn.id FROM serial_numbers sn
                            JOIN transfer_item_serials tis ON sn.id = tis.serial_number_id
                            WHERE sn.serial_number = ? AND tis.transfer_item_id = ? AND tis.status = 'IN_TRANSIT' AND sn.status = 'IN_TRANSIT' FOR UPDATE");
                        $chk->execute([$sn, $item['id']]);
                        $snRow = $chk->fetch();
                        if (!$snRow) throw new Exception("Nomor seri '$sn' tidak terdaftar dalam transfer ini atau sudah diterima.");

                        $stockService->moveSerial($snRow['id'], 'AVAILABLE', $transfer['destination_warehouse_id'], null, null, 'transfer', $transfer['id'], 'Transfer Diterima - ' . $transfer['transaction_code']);
                        $pdo->prepare("UPDATE transfer_item_serials SET status = 'RECEIVED' WHERE transfer_item_id = ? AND serial_number_id = ?")
                            ->execute([$item['id'], $snRow['id']]);
                    }
                }

                $updItemStmt->execute([$receiveQty, $item['id']]);
                $stockService->mutateStock($item['master_item_id'], $transfer['destination_warehouse_id'], $receiveQty, 'TRANSFER_IN', 'transfers', $transfer['id'], 'Transfer In ' . $transfer['transaction_code']);
                $receivedSummary[] = ['item_id' => (int)$item['id'], 'quantity' => $receiveQty];

                if (((int)$item['quantity_received'] + $receiveQty) < (int)$item['quantity_sent']) {
                    $allCompleted = false;
                }
            }

            if ($notesReceive !== '') {
                $newNotes = trim(($transfer['notes'] ?? '') . "\n[Penerimaan " . date('Y-m-d H:i') . "]: " . $notesReceive);
                $pdo->prepare('UPDATE transfers SET notes = ? WHERE id = ?')->execute([$newNotes, $id]);
            }

            $receiptStmt = $pdo->prepare("INSERT INTO transfer_receipts
                (transfer_id, processed_by, receiver_name, receiver_role, receiver_phone, notes, received_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $receiptStmt->execute([
                $id,
                $userId,
                $receiverName,
                $receiverRole !== '' ? $receiverRole : null,
                $receiverPhone !== '' ? $receiverPhone : null,
                $notesReceive !== '' ? $notesReceive : null
            ]);

            if ($allCompleted) {
                $pdo->prepare("UPDATE transfers SET status = 'COMPLETED', received_by = ?, received_at = NOW() WHERE id = ?")
                    ->execute([$userId, $id]);
            } else {
                $pdo->prepare("UPDATE transfers SET status = 'PARTIAL_RECEIVED', received_by = ?, received_at = NOW() WHERE id = ?")
                    ->execute([$userId, $id]);
            }

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, new_data) VALUES (?, 'RECEIVE_TRANSFER', 'transfers', ?, ?)");
            $auditStmt->execute([$userId, $id, json_encode([
                'receiver_name' => $receiverName,
                'receiver_role' => $receiverRole,
                'receiver_phone' => $receiverPhone,
                'status_after' => $allCompleted ? 'COMPLETED' : 'PARTIAL_RECEIVED',
                'items' => $receivedSummary,
                'photo' => true,
            ], JSON_UNESCAPED_UNICODE)]);

            $pdo->commit();
            $_SESSION['success'] = 'Penerimaan transfer berhasil dicatat.';
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($uploadedFilePath) {
                $absolutePath = __DIR__ . '/../../storage/private_uploads/' . $uploadedFilePath;
                if (is_file($absolutePath)) @unlink($absolutePath);
            }
            $_SESSION['error'] = 'Gagal menerima transfer: ' . $e->getMessage();
        }

        header('Location: ' . url('/transfers/show?id=' . $id));
        exit;
    }

    private function storeTransferAttachment(array $file, int $transferId, string $referenceType, int $userId, PDO $pdo): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new Exception('Upload foto gagal.');
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) throw new Exception('Ukuran foto maksimal 5 MB.');
        $tmpPath = $file['tmp_name'] ?? '';
        $mimeType = $tmpPath !== '' ? mime_content_type($tmpPath) : false;
        $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$mimeType || !isset($allowedMimes[$mimeType])) throw new Exception('Format foto harus JPG, PNG, atau WEBP.');

        $uploadDir = __DIR__ . '/../../storage/private_uploads/transfers/' . $transferId . '/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new Exception('Folder upload foto tidak dapat dibuat.');
        }
        $filename = $referenceType . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mimeType];
        $destination = $uploadDir . $filename;
        if (!move_uploaded_file($tmpPath, $destination)) throw new Exception('Foto gagal disimpan.');

        $relativePath = 'transfers/' . $transferId . '/' . $filename;
        $originalName = basename($file['name'] ?? $filename);
        $stmt = $pdo->prepare('INSERT INTO attachments (reference_type, reference_id, file_path, original_name, mime_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$referenceType, $transferId, $relativePath, $originalName, $mimeType, (int)$file['size'], $userId]);
        return $relativePath;
    }
}
