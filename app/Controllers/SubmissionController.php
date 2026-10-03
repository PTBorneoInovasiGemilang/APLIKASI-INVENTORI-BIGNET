<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class SubmissionController {
    
    public function __construct() {
        AuthHelper::requireLogin();
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClauses = ["1=1"];
        $params = [];

        // TEKNISI_NOC can only see their own submissions
        if (!AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])) {
            $whereClauses[] = "s.submitted_by = ?";
            $params[] = (int)$user['id'];
        }

        if ($search !== '') {
            $whereClauses[] = "s.submission_code LIKE ?";
            $params[] = "%$search%";
        }

        if ($status !== '') {
            $whereClauses[] = "s.status = ?";
            $params[] = $status;
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        // Count for pagination
        $countSql = "SELECT COUNT(*) FROM submissions s 
                     JOIN users u ON s.submitted_by = u.id 
                     $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "SELECT s.*, u.name as submitter_name 
                FROM submissions s 
                JOIN users u ON s.submitted_by = u.id 
                $whereSql
                ORDER BY s.id DESC LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $submissions = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/submissions/index.php';
    }

    public function create() {
        // Only TEKNISI_NOC can create submissions (Admin = operator, not requester)
        AuthHelper::requireRole(['TEKNISI_NOC']);
        // Any user can create a submission, but usually TEKNISI_NOC
        $pdo = Database::getInstance()->getConnection();
        $items = $pdo->query("SELECT id, kode_barang, nama, is_serialized FROM master_items WHERE is_active = 1 ORDER BY nama")->fetchAll();
        $warehouses = $pdo->query("SELECT id, name FROM warehouses WHERE is_active = 1 ORDER BY name")->fetchAll();

        // Availability per gudang: hanya item dengan stok fisik > 0 yang boleh dipilih Teknisi.
        // Identity stok = master_item_id + warehouse_id.
        $stockRows = $pdo->query("SELECT master_item_id, warehouse_id, quantity FROM stocks WHERE quantity > 0")->fetchAll(PDO::FETCH_ASSOC);
        $stockAvailability = [];
        foreach ($stockRows as $row) {
            $whId = (int)$row['warehouse_id'];
            $itemId = (int)$row['master_item_id'];
            if (!isset($stockAvailability[$whId])) {
                $stockAvailability[$whId] = [];
            }
            $stockAvailability[$whId][$itemId] = (int)$row['quantity'];
        }

        require __DIR__ . '/../../resources/views/transactions/submissions/create.php';
    }

    public function store() {
        AuthHelper::requireRole(['TEKNISI_NOC']);
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/submissions'));
            exit;
        }

        $source_types = $_POST['source_types'] ?? [];
        $item_ids = $_POST['item_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $warehouse_ids = $_POST['warehouse_ids'] ?? [];
        $manual_names = $_POST['manual_names'] ?? [];
        $manual_specs = $_POST['manual_specs'] ?? [];
        $manual_units = $_POST['manual_units'] ?? [];
        $notes_list = $_POST['notes'] ?? [];

        $hasStock = false;
        $hasManual = false;
        
        // Validation
        $valid_lines = 0;
        foreach ($source_types as $index => $type) {
            $qty = (int)($quantities[$index] ?? 0);
            if ($qty <= 0) continue;
            
            if ($type === 'STOCK') {
                $item_id = $item_ids[$index] ?? null;
                $wh_id = (int)($warehouse_ids[$index] ?? 0);
                if (!empty($item_id) && $wh_id > 0) {
                    $valid_lines++;
                    $hasStock = true;
                }
            } else if ($type === 'MANUAL') {
                $name = trim($manual_names[$index] ?? '');
                if (!empty($name)) {
                    $valid_lines++;
                    $hasManual = true;
                }
            }
        }
        
        if ($valid_lines === 0) {
            $_SESSION['error'] = "Pengajuan harus memiliki minimal 1 barang yang valid (dengan kuantitas > 0). Untuk barang dari Stok, Gudang Pemenuhan wajib dipilih.";
            header("Location: " . url('/submissions/create'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();

        // Validasi gudang pemenuhan: hanya gudang aktif yang boleh dipilih.
        $activeWarehouseIds = array_map('intval', $pdo->query("SELECT id FROM warehouses WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN));

        // Guard server-side: item STOCK wajib benar-benar tersedia (> 0) pada gudang pemenuhan.
        // Jangan bergantung pada filter JavaScript karena request dapat dimanipulasi.
        $stockAvailabilityStmt = $pdo->prepare("SELECT quantity FROM stocks WHERE master_item_id = ? AND warehouse_id = ?");

        $submission_type = 'MIXED';
        if ($hasStock && !$hasManual) $submission_type = 'STOCK';
        if (!$hasStock && $hasManual) $submission_type = 'MANUAL';

        $submission_code = \App\Helpers\TransactionHelper::generateCode('REQ', 'submissions', 'submission_code');
        $user_id = AuthHelper::user()['id'];

        try {
            $pdo->beginTransaction();

            $header_note = trim($_POST['header_note'] ?? '');

            $stmt = $pdo->prepare("INSERT INTO submissions (submission_code, submitted_by, status, notes, submission_type) VALUES (?, ?, 'PENDING', ?, ?)");
            $stmt->execute([$submission_code, $user_id, $header_note, $submission_type]);
            $submission_id = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO submission_items (submission_id, source_type, warehouse_id, master_item_id, manual_name, manual_spec, manual_unit, requested_quantity, remaining_quantity, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $summaryParts = []; // ringkasan barang untuk notifikasi

            foreach ($source_types as $index => $type) {
                $qty = (int)($quantities[$index] ?? 0);
                if ($qty <= 0) continue;
                
                $line_note = trim($notes_list[$index] ?? '');

                if ($type === 'STOCK') {
                    $item_id = $item_ids[$index] ?? null;
                    $wh_id = (int)($warehouse_ids[$index] ?? 0);
                    if (!empty($item_id) && $wh_id > 0) {
                        if (!in_array($wh_id, $activeWarehouseIds, true)) {
                            throw new \Exception("Gudang pemenuhan tidak valid atau nonaktif (ID: $wh_id).");
                        }

                        $stockAvailabilityStmt->execute([(int)$item_id, $wh_id]);
                        $availableStock = (int)$stockAvailabilityStmt->fetchColumn();
                        if ($availableStock <= 0) {
                            throw new \Exception("Barang yang dipilih tidak tersedia di gudang pemenuhan tersebut. Silakan pilih barang lain atau gudang lain yang memiliki stok.");
                        }

                        $itemStmt->execute([$submission_id, 'STOCK', $wh_id, $item_id, null, null, null, $qty, 0, $line_note]);
                        $nm = $pdo->query("SELECT nama FROM master_items WHERE id = " . (int)$item_id)->fetchColumn();
                        $nm = preg_replace('/\\s*\\(\\s*\\)/u', '', (string)$nm);
                        if ($nm) $summaryParts[] = $qty . ' ' . $nm;
                    }
                } else if ($type === 'MANUAL') {
                    $name = trim($manual_names[$index] ?? '');
                    $name = preg_replace('/\\s*\\(\\s*\\)/u', '', $name);
                    $spec = trim($manual_specs[$index] ?? '');
                    $unit = trim($manual_units[$index] ?? '');
                    if (!empty($name)) {
                        $itemStmt->execute([$submission_id, 'MANUAL', null, null, $name, $spec, $unit, $qty, 0, $line_note]);
                        $summaryParts[] = $qty . ' ' . $name;
                    }
                }
            }

            // Audit
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'submissions', ?)");
            $auditStmt->execute([$user_id, $submission_id]);

            $pdo->commit();
            $_SESSION['success'] = "Pengajuan berhasil dibuat.";
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Error creating submission: " . $e->getMessage();
            header("Location: " . url('/submissions/create'));
            exit;
        }

        // Notification ke ADMIN (business event: teknisi submit pengajuan).
        // Auxiliary side-effect: kegagalan notification TIDAK merusak transaksi bisnis.
        try {
            $summary = implode(', ', $summaryParts);
            $ownerName = AuthHelper::user()['name'];
            \App\Helpers\NotificationHelper::createForRoleOnce(
                'ADMIN',
                'Pengajuan Barang Baru',
                $ownerName . ' mengajukan ' . ($summary ?: 'pengajuan barang') . ' (' . $submission_code . ').',
                'info',
                '/submissions/show?id=' . $submission_id
            );
        } catch (\Exception $notifEx) {
            error_log('[BIGNET] Notification gagal (submission create #' . $submission_id . '): ' . $notifEx->getMessage());
        }

        // Setelah pengajuan berhasil dibuat, teknisi langsung diarahkan ke detail pengajuan
        // yang baru saja dibuat agar dapat melihat barang, status, dan progres persetujuan.
        header("Location: " . url('/submissions/show?id=' . (int)$submission_id));
        exit;
    }

    public function show() {
        $id = (int)$_GET['id'];
        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();
        
        $stmt = $pdo->prepare("SELECT s.*, u.name as submitter_name, a.name as approver_name 
            FROM submissions s 
            JOIN users u ON s.submitted_by = u.id 
            LEFT JOIN users a ON s.approved_by = a.id 
            WHERE s.id = ?");
        $stmt->execute([$id]);
        $submission = $stmt->fetch();

        if (!$submission) {
            $_SESSION['error'] = "Submission not found.";
            header("Location: " . url('/submissions'));
            exit;
        }

        // Check privacy
        if ($user['role'] === 'TEKNISI_NOC' && $submission['submitted_by'] !== $user['id']) {
            $_SESSION['error'] = "403 Forbidden";
            header("Location: " . url('/submissions'));
            exit;
        }

        $stmt = $pdo->prepare("SELECT si.*, m.kode_barang, m.nama, m.is_serialized 
                                   FROM submission_items si 
                                   LEFT JOIN master_items m ON si.master_item_id = m.id 
                                   WHERE si.submission_id = ?");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();

        // Info stok per item: gudang pemenuhan + stok tersedia DI GUDANG TERSEBUT.
        // Identity stock = master_item_id + warehouse_id (bukan SUM lintas gudang).
        $itemStockInfo = [];
        foreach ($items as $item) {
            if ($item['source_type'] !== 'STOCK') continue;
            $whId = (int)($item['warehouse_id'] ?? 0);
            if ($whId <= 0) {
                $itemStockInfo[$item['id']] = ['warehouse_id' => null, 'warehouse_name' => null, 'available' => null];
                continue;
            }
            $whRow = $pdo->prepare("SELECT w.name, COALESCE(s.quantity, 0) AS qty
                                     FROM warehouses w
                                     LEFT JOIN stocks s ON s.warehouse_id = w.id AND s.master_item_id = ?
                                     WHERE w.id = ?");
            $whRow->execute([(int)$item['master_item_id'], $whId]);
            $whData = $whRow->fetch();
            $itemStockInfo[$item['id']] = [
                'warehouse_id' => $whId,
                'warehouse_name' => $whData['name'] ?? ('Gudang #' . $whId),
                'available' => (int)($whData['qty'] ?? 0),
            ];
        }

        // Fetch Timeline / Audit logs
        $stmt = $pdo->prepare("SELECT a.*, u.name as user_name 
                               FROM audit_logs a 
                               LEFT JOIN users u ON a.user_id = u.id 
                               WHERE a.entity = 'submissions' AND a.entity_id = ? 
                               ORDER BY a.created_at DESC");
        $stmt->execute([$id]);
        $timeline = $stmt->fetchAll();

        // Fetch Master Items for manual mapping
        $master_items = [];
        if ($submission['submission_type'] === 'MANUAL' && $submission['status'] === 'PENDING') {
            $master_items = $pdo->query("SELECT id, kode_barang, nama FROM master_items WHERE is_active = 1")->fetchAll();
        }

        require __DIR__ . '/../../resources/views/transactions/submissions/show.php';
    }

    public function edit() {
        AuthHelper::requireRole(['TEKNISI_NOC']);
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();

        $stmt = $pdo->prepare("SELECT s.*, u.name as submitter_name
                               FROM submissions s
                               JOIN users u ON s.submitted_by = u.id
                               WHERE s.id = ? AND s.submitted_by = ?");
        $stmt->execute([$id, (int)$user['id']]);
        $submission = $stmt->fetch();

        if (!$submission) {
            $_SESSION['error'] = "Pengajuan tidak ditemukan atau bukan milik Anda.";
            header("Location: " . url('/submissions'));
            exit;
        }

        if ($submission['status'] !== 'PENDING') {
            $_SESSION['error'] = "Pengajuan hanya bisa diedit sebelum disetujui Admin.";
            header("Location: " . url('/submissions/show?id=' . $id));
            exit;
        }

        $stmt = $pdo->prepare("SELECT si.*, m.kode_barang, m.nama, m.is_serialized
                               FROM submission_items si
                               LEFT JOIN master_items m ON si.master_item_id = m.id
                               WHERE si.submission_id = ?
                               ORDER BY si.id");
        $stmt->execute([$id]);
        $existingItems = $stmt->fetchAll();

        $items = $pdo->query("SELECT id, kode_barang, nama, is_serialized FROM master_items WHERE is_active = 1 ORDER BY nama")->fetchAll();
        $warehouses = $pdo->query("SELECT id, name FROM warehouses WHERE is_active = 1 ORDER BY name")->fetchAll();

        $stockRows = $pdo->query("SELECT master_item_id, warehouse_id, quantity FROM stocks WHERE quantity > 0")->fetchAll(PDO::FETCH_ASSOC);
        $stockAvailability = [];
        foreach ($stockRows as $row) {
            $whId = (int)$row['warehouse_id'];
            $itemId = (int)$row['master_item_id'];
            if (!isset($stockAvailability[$whId])) $stockAvailability[$whId] = [];
            $stockAvailability[$whId][$itemId] = (int)$row['quantity'];
        }

        $editMode = true;
        require __DIR__ . '/../../resources/views/transactions/submissions/create.php';
    }

    public function update() {
        AuthHelper::requireRole(['TEKNISI_NOC']);
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/submissions'));
            exit;
        }

        $id = (int)($_POST['submission_id'] ?? 0);
        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();

        if ($id <= 0) {
            $_SESSION['error'] = "Pengajuan tidak valid.";
            header("Location: " . url('/submissions'));
            exit;
        }

        // Lock status + ownership check in one transaction so an approval cannot race an edit.
        try {
            $pdo->beginTransaction();
            $subStmt = $pdo->prepare("SELECT * FROM submissions WHERE id = ? AND submitted_by = ? FOR UPDATE");
        $subStmt->execute([$id, (int)$user['id']]);
        $submission = $subStmt->fetch();

        if (!$submission) {
            $pdo->rollBack();
            $_SESSION['error'] = "Pengajuan tidak ditemukan atau bukan milik Anda.";
            header("Location: " . url('/submissions'));
            exit;
        }
        if ($submission['status'] !== 'PENDING') {
            $pdo->rollBack();
            $_SESSION['error'] = "Pengajuan tidak bisa diedit karena sudah diproses Admin.";
            header("Location: " . url('/submissions/show?id=' . $id));
            exit;
        }

        $source_types = $_POST['source_types'] ?? [];
        $item_ids = $_POST['item_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $warehouse_ids = $_POST['warehouse_ids'] ?? [];
        $manual_names = $_POST['manual_names'] ?? [];
        $manual_specs = $_POST['manual_specs'] ?? [];
        $manual_units = $_POST['manual_units'] ?? [];
        $notes_list = $_POST['notes'] ?? [];
        $header_note = trim($_POST['header_note'] ?? '');

        $activeWarehouseIds = array_map('intval', $pdo->query("SELECT id FROM warehouses WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN));
        $stockAvailabilityStmt = $pdo->prepare("SELECT quantity FROM stocks WHERE master_item_id = ? AND warehouse_id = ?");

        $hasStock = false;
        $hasManual = false;
        $validLines = 0;
        foreach ($source_types as $index => $type) {
            $qty = (int)($quantities[$index] ?? 0);
            if ($qty <= 0) continue;

            if ($type === 'STOCK') {
                $itemId = (int)($item_ids[$index] ?? 0);
                $whId = (int)($warehouse_ids[$index] ?? 0);
                if ($itemId <= 0 || $whId <= 0) {
                    throw new \Exception("Barang dari stok dan Gudang Pemenuhan wajib dipilih.");
                }
                if (!in_array($whId, $activeWarehouseIds, true)) {
                    throw new \Exception("Gudang pemenuhan tidak valid atau nonaktif.");
                }
                $stockAvailabilityStmt->execute([$itemId, $whId]);
                $availableStock = (int)$stockAvailabilityStmt->fetchColumn();
                if ($availableStock <= 0) {
                    $itemNameStmt = $pdo->prepare("SELECT nama FROM master_items WHERE id = ?");
                    $itemNameStmt->execute([$itemId]);
                    $itemName = $itemNameStmt->fetchColumn() ?: 'Barang';
                    throw new \Exception("{$itemName} tidak tersedia di gudang pemenuhan yang dipilih.");
                }
                $hasStock = true;
                $validLines++;
            } elseif ($type === 'MANUAL') {
                $name = trim($manual_names[$index] ?? '');
                if ($name === '') {
                    throw new \Exception("Nama barang manual wajib diisi.");
                }
                $hasManual = true;
                $validLines++;
            }
        }

        if ($validLines === 0) {
            $pdo->rollBack();
            $_SESSION['error'] = "Pengajuan harus memiliki minimal 1 barang yang valid.";
            header("Location: " . url('/submissions/edit?id=' . $id));
            exit;
        }

        $submissionType = 'MIXED';
        if ($hasStock && !$hasManual) $submissionType = 'STOCK';
        if (!$hasStock && $hasManual) $submissionType = 'MANUAL';

            $updateSubmission = $pdo->prepare("UPDATE submissions SET notes = ?, submission_type = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'PENDING' AND submitted_by = ?");
            $updateSubmission->execute([$header_note, $submissionType, $id, (int)$user['id']]);

            $pdo->prepare("DELETE FROM submission_items WHERE submission_id = ?")->execute([$id]);

            $itemStmt = $pdo->prepare("INSERT INTO submission_items (submission_id, source_type, warehouse_id, master_item_id, manual_name, manual_spec, manual_unit, requested_quantity, approved_quantity, fulfilled_quantity, remaining_quantity, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, 'PENDING', ?)");
            $summaryParts = [];

            foreach ($source_types as $index => $type) {
                $qty = (int)($quantities[$index] ?? 0);
                if ($qty <= 0) continue;
                $lineNote = trim($notes_list[$index] ?? '');

                if ($type === 'STOCK') {
                    $itemId = (int)($item_ids[$index] ?? 0);
                    $whId = (int)($warehouse_ids[$index] ?? 0);
                    $itemStmt->execute([$id, 'STOCK', $whId, $itemId, null, null, null, $qty, $lineNote]);
                    $nmStmt = $pdo->prepare("SELECT nama FROM master_items WHERE id = ?");
                    $nmStmt->execute([$itemId]);
                    $nm = $nmStmt->fetchColumn();
                    if ($nm) $summaryParts[] = $qty . ' ' . $nm;
                } else {
                    $name = trim($manual_names[$index] ?? '');
                    $spec = trim($manual_specs[$index] ?? '');
                    $unit = trim($manual_units[$index] ?? '');
                    $itemStmt->execute([$id, 'MANUAL', null, null, $name, $spec, $unit, $qty, $lineNote]);
                    $summaryParts[] = $qty . ' ' . $name;
                }
            }

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'UPDATE', 'submissions', ?)");
            $auditStmt->execute([(int)$user['id'], $id]);

            $pdo->commit();
            $_SESSION['success'] = "Pengajuan berhasil diperbarui.";
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = "Gagal memperbarui pengajuan: " . $e->getMessage();
            header("Location: " . url('/submissions/edit?id=' . $id));
            exit;
        }

        // Admin tetap menerima notifikasi agar perubahan pada pengajuan pending terlihat.
        try {
            $summary = implode(', ', $summaryParts);
            \App\Helpers\NotificationHelper::createForRoleOnce(
                'ADMIN',
                'Pengajuan Barang Diperbarui',
                $user['name'] . ' memperbarui pengajuan ' . $submission['submission_code'] . ($summary ? ': ' . $summary : '.'),
                'info',
                '/submissions/show?id=' . $id
            );
        } catch (\Throwable $notifEx) {
            error_log('[BIGNET] Notification gagal (submission update #' . $id . '): ' . $notifEx->getMessage());
        }

        header("Location: " . url('/submissions/show?id=' . $id));
        exit;
    }

    public function approve() {
        AuthHelper::requireRole(['ADMIN']);
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed";
            header("Location: " . url('/submissions'));
            exit;
        }

        $id = (int)$_POST['id'];
        $approval_notes = trim($_POST['approval_notes'] ?? '');
        $approved_quantities = $_POST['approved_quantities'] ?? [];
        $rejection_reasons = $_POST['rejection_reasons'] ?? [];
        $mapped_items = $_POST['mapped_item_ids'] ?? [];
        $overall_action = $_POST['action']; // 'APPROVE' or 'REJECT_ALL'

        $pdo = Database::getInstance()->getConnection();
        $user_id = AuthHelper::user()['id'];

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $submission = $stmt->fetch();

            if ($submission['status'] !== 'PENDING') {
                throw new \Exception("Submission is already processed.");
            }

            if ($overall_action === 'REJECT_ALL' && empty($approval_notes)) {
                throw new \Exception("Alasan penolakan wajib diisi untuk penolakan keseluruhan.");
            }

            $itemsStmt = $pdo->prepare("SELECT * FROM submission_items WHERE submission_id = ? FOR UPDATE");
            $itemsStmt->execute([$id]);
            $items = $itemsStmt->fetchAll();

            $all_approved = true;
            $all_rejected = true;
            
            $updateItemStmt = $pdo->prepare("UPDATE submission_items SET status = ?, approved_quantity = ?, remaining_quantity = ?, rejection_reason = ?, master_item_id = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");

            foreach ($items as $item) {
                $item_id = $item['id'];
                $requested = $item['requested_quantity'];
                
                if ($overall_action === 'REJECT_ALL') {
                    $updateItemStmt->execute(['REJECTED', 0, 0, $approval_notes, $item['master_item_id'], $user_id, $item_id]);
                    continue;
                }
                
                // Per-item logic
                $qty = isset($approved_quantities[$item_id]) ? (int)$approved_quantities[$item_id] : 0;
                $reason = trim($rejection_reasons[$item_id] ?? '');
                $mapped = $item['master_item_id'];
                
                if ($item['source_type'] === 'MANUAL') {
                    if (isset($mapped_items[$item_id]) && !empty($mapped_items[$item_id])) {
                        $mapped = $mapped_items[$item_id];
                    }
                    if ($qty > 0 && empty($mapped)) {
                        throw new \Exception("Barang manual '{$item['manual_name']}' yang disetujui harus di-mapping ke master barang (Kuantitas > 0).");
                    }
                }
                
                if ($qty > $requested) {
                    throw new \Exception("Kuantitas yang disetujui tidak boleh melebihi yang diminta.");
                }

                // STOCK SUFFICIENCY GUARD (server-side, saat keputusan approval):
                // jumlah yang disetujui (qty > 0) wajib ditutup oleh stok aktual PADA GUDANG
                // PEMENUHAN yang direkam di submission item (master_item_id + warehouse_id).
                // TIDAK memakai SUM lintas gudang — identity stock = master_item_id + warehouse_id.
                if ($item['source_type'] === 'STOCK' && $qty > 0) {
                    $whId = (int)($item['warehouse_id'] ?? 0);
                    if ($whId <= 0) {
                        throw new \Exception("Gudang Pemenuhan belum ditentukan untuk item ini (data pengajuan lama tanpa gudang). Mohon ajukan ulang pengajuan dengan memilih Gudang Pemenuhan.");
                    }
                    $whNameStmt = $pdo->prepare("SELECT name FROM warehouses WHERE id = ?");
                    $whNameStmt->execute([$whId]);
                    $whName = $whNameStmt->fetchColumn() ?: "Gudang #$whId";

                    $availStmt = $pdo->prepare("SELECT quantity FROM stocks WHERE master_item_id = ? AND warehouse_id = ?");
                    $availStmt->execute([(int)$mapped, $whId]);
                    $available = (int)$availStmt->fetchColumn();
                    if ($available < $qty) {
                        $label = $item['manual_name'] ?: 'barang';
                        throw new \Exception("Stok Tidak Mencukupi di Gudang Pemenuhan ($whName). Diminta: $qty Pcs, stok tersedia di gudang tersebut: $available Pcs. Kurangi Qty disetujui, pilih gudang lain, atau tolak item ini.");
                    }
                }
                
                if ($qty == 0) {
                    if (empty($reason)) {
                        throw new \Exception("Alasan penolakan wajib diisi untuk barang yang ditolak (QTY = 0).");
                    }
                    $status = 'REJECTED';
                    $all_approved = false;
                } else if ($qty < $requested) {
                    $status = 'PARTIAL_APPROVED';
                    $all_approved = false;
                    $all_rejected = false;
                } else {
                    $status = 'APPROVED';
                    $all_rejected = false;
                }

                $updateItemStmt->execute([$status, $qty, $qty, $reason, $mapped, $user_id, $item_id]);
            }

            if ($overall_action === 'REJECT_ALL') {
                $overall_status = 'REJECTED';
            } else if ($all_rejected) {
                $overall_status = 'REJECTED';
            } else if ($all_approved) {
                $overall_status = 'APPROVED';
            } else {
                $overall_status = 'PARTIAL_APPROVED';
            }

            // Update Submission
            $updateStmt = $pdo->prepare("UPDATE submissions SET status = ?, approved_by = ?, approved_at = NOW(), approval_notes = ? WHERE id = ?");
            $updateStmt->execute([$overall_status, $user_id, $approval_notes, $id]);

            // Audit Log
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, new_data) VALUES (?, ?, 'submissions', ?, ?)");
            $auditStmt->execute([$user_id, $overall_action === 'REJECT_ALL' ? 'REJECT' : 'APPROVE', $id, json_encode(['status' => $overall_status])]);

            $pdo->commit();
            $_SESSION['success'] = "Pengajuan berhasil diproses dengan status: $overall_status";
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header("Location: " . url('/submissions/show?id=' . $id));
            exit;
        }

        // Notification ke Teknisi pemilik (business event: keputusan Admin).
        // Auxiliary side-effect: kegagalan notification TIDAK merusak transaksi bisnis.
        try {
            $ownerId = (int)$submission['submitted_by'];
            $route = '/submissions/show?id=' . $id;
            if ($overall_status === 'APPROVED') {
                \App\Helpers\NotificationHelper::createForUsersOnce([$ownerId], 'Pengajuan Disetujui', 'Pengajuan ' . $submission['submission_code'] . ' telah disetujui oleh Admin.', 'success', $route);
            } elseif ($overall_status === 'PARTIAL_APPROVED') {
                \App\Helpers\NotificationHelper::createForUsersOnce([$ownerId], 'Pengajuan Disetujui Sebagian', 'Sebagian barang dari pengajuan ' . $submission['submission_code'] . ' disetujui. Cek detail untuk rincian quantity.', 'info', $route);
            } else {
                \App\Helpers\NotificationHelper::createForUsersOnce([$ownerId], 'Pengajuan Ditolak', 'Pengajuan ' . $submission['submission_code'] . ' ditolak.' . ($approval_notes ? ' Alasan: ' . $approval_notes : ''), 'danger', $route);
            }
        } catch (\Exception $notifEx) {
            error_log('[BIGNET] Notification gagal (submission approve #' . $id . '): ' . $notifEx->getMessage());
        }

        header("Location: " . url('/submissions/show?id=' . $id));
        exit;
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
            
            $stmt = $pdo->prepare("SELECT si.id as submission_item_id, si.master_item_id, si.warehouse_id, m.nama, m.kode_barang, m.is_serialized, si.approved_quantity, si.remaining_quantity 
                                   FROM submission_items si 
                                   JOIN master_items m ON si.master_item_id = m.id 
                                   WHERE si.submission_id = ? AND si.remaining_quantity > 0");
            $stmt->execute([$id]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode($items);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
