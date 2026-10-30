<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class MasterItemController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        
        $search = $_GET['search'] ?? '';
        $category_id = $_GET['category_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClauses = [];
        $params = [];

        if ($search !== '') {
            $whereClauses[] = "(m.kode_barang LIKE ? OR m.nama LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($category_id !== '') {
            $whereClauses[] = "m.item_category_id = ?";
            $params[] = $category_id;
        }

        if ($status !== '') {
            $whereClauses[] = "m.is_active = ?";
            $params[] = $status;
        }

        $whereSql = '';
        if (count($whereClauses) > 0) {
            $whereSql = "WHERE " . implode(" AND ", $whereClauses);
        }

        // Count total for pagination
        $countSql = "SELECT COUNT(*) FROM master_items m $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);
        
        // is_used = barang sudah pernah memiliki stok/serial/histori transaksi (guard delete & keputusan Hapus vs Nonaktifkan)
        $sql = "SELECT m.*, c.name as category_name, u.name as unit_name,
                (SELECT file_path FROM master_item_attachments WHERE master_item_id = m.id ORDER BY id ASC LIMIT 1) as photo_path,
                CASE WHEN uu.master_item_id IS NULL THEN 0 ELSE 1 END as is_used
                FROM master_items m 
                LEFT JOIN item_categories c ON m.item_category_id = c.id 
                LEFT JOIN units u ON m.unit_id = u.id 
                LEFT JOIN (
                    SELECT master_item_id FROM stocks WHERE quantity > 0
                    UNION SELECT master_item_id FROM serial_numbers
                    UNION SELECT master_item_id FROM incoming_items
                    UNION SELECT master_item_id FROM outgoing_items
                    UNION SELECT master_item_id FROM transfer_items
                    UNION SELECT master_item_id FROM return_items
                    UNION SELECT master_item_id FROM loan_items
                    UNION SELECT master_item_id FROM installation_items
                    UNION SELECT master_item_id FROM stock_adjustment_items
                    UNION SELECT master_item_id FROM submission_items
                    UNION SELECT master_item_id FROM stock_ledgers
                ) uu ON uu.master_item_id = m.id
                $whereSql
                ORDER BY m.id DESC
                LIMIT $limit OFFSET $offset";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        // Stats
        $statTotal = (int)$pdo->query("SELECT COUNT(*) FROM master_items")->fetchColumn();
        $statActive = (int)$pdo->query("SELECT COUNT(*) FROM master_items WHERE is_active = 1")->fetchColumn();
        $statSerialized = (int)$pdo->query("SELECT COUNT(*) FROM master_items WHERE is_serialized = 1")->fetchColumn();
        $statBulk = (int)$pdo->query("SELECT COUNT(*) FROM master_items WHERE is_serialized = 0")->fetchColumn();

        // Get categories and units for the add form and filters
        $categories = $pdo->query("SELECT * FROM item_categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
        $units = $pdo->query("SELECT * FROM units WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

        require __DIR__ . '/../../resources/views/inventory/items/index.php';
    }

    public function store() {
        // Write Master Barang HANYA untuk ADMIN (SUPERADMIN read-only, TEKNISI/NOC tidak punya akses).
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/items'));
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        $item_category_id = $_POST['item_category_id'] ?? null;
        $unit_id = $_POST['unit_id'] ?? null;
        $is_serialized = isset($_POST['is_serialized']) ? 1 : 0;
        $stok_minimum = (int)($_POST['stok_minimum'] ?? 0);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($nama === '' || empty($item_category_id) || empty($unit_id)) {
            $_SESSION['error'] = "Nama, kategori, dan satuan wajib diisi.";
            header("Location: " . url('/items'));
            exit;
        }

        if (strlen($nama) > 100) {
            $_SESSION['error'] = "Nama barang maksimal 100 karakter.";
            header("Location: " . url('/items'));
            exit;
        }

        if ($stok_minimum < 0) {
            $_SESSION['error'] = "Stok minimum tidak boleh negatif.";
            header("Location: " . url('/items'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();

        // Check if category and unit exist and are active
        $stmtCheck = $pdo->prepare("SELECT id FROM item_categories WHERE id = ? AND is_active = 1");
        $stmtCheck->execute([$item_category_id]);
        if (!$stmtCheck->fetch()) {
            $_SESSION['error'] = "Kategori tidak valid atau tidak aktif.";
            header("Location: " . url('/items'));
            exit;
        }

        $stmtCheck = $pdo->prepare("SELECT id FROM units WHERE id = ? AND is_active = 1");
        $stmtCheck->execute([$unit_id]);
        if (!$stmtCheck->fetch()) {
            $_SESSION['error'] = "Satuan tidak valid atau tidak aktif.";
            header("Location: " . url('/items'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        $maxRetries = 5;
        $attempt = 0;
        $success = false;

        while ($attempt < $maxRetries && !$success) {
            try {
                $pdo->beginTransaction();
                
                $stmtCat = $pdo->prepare("SELECT code FROM item_categories WHERE id = ?");
                $stmtCat->execute([$item_category_id]);
                $catCode = $stmtCat->fetchColumn();
                if (!$catCode) $catCode = 'BRG';

                $stmtMax = $pdo->prepare("SELECT kode_barang FROM master_items WHERE kode_barang LIKE ? ORDER BY id DESC LIMIT 1 FOR UPDATE");
                $stmtMax->execute([$catCode . '-%']);
                $lastCode = $stmtMax->fetchColumn();

                if ($lastCode) {
                    $lastNum = (int)str_replace($catCode . '-', '', $lastCode);
                    $newNum = str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);
                } else {
                    $newNum = '000001';
                }
                $kode_barang = $catCode . '-' . $newNum;

                $stmt = $pdo->prepare("INSERT INTO master_items (kode_barang, nama, item_category_id, unit_id, is_serialized, stok_minimum, deskripsi, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$kode_barang, $nama, $item_category_id, $unit_id, $is_serialized, $stok_minimum, $deskripsi, $is_active]);
                
                $itemId = $pdo->lastInsertId();

                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $this->uploadPhoto($itemId, $_FILES['photo'], $pdo);
                }

                $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'master_items', ?)");
                $auditStmt->execute([AuthHelper::user()['id'], $itemId]);

                $pdo->commit();
                $success = true;
            } catch (\PDOException $e) {
                $pdo->rollBack();
                // 1062 is MySQL duplicate entry error code
                if ($e->errorInfo[1] == 1062) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        $_SESSION['error'] = "Gagal generate kode barang setelah beberapa kali percobaan. Silakan coba lagi.";
                        header("Location: " . url('/items'));
                        exit;
                    }
                } else {
                    $_SESSION['error'] = "Terjadi kesalahan sistem saat menyimpan barang.";
                    header("Location: " . url('/items'));
                    exit;
                }
            }
        }
        header("Location: " . url('/items'));
        exit;
    }

    private function uploadPhoto($itemId, $file, $pdo) {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedMimes)) return;
        if ($file['size'] > 5 * 1024 * 1024) return; 

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('item_') . '.' . $ext;

        $uploadDir = __DIR__ . '/../../storage/private_uploads/items/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $destination = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $stmt = $pdo->prepare("INSERT INTO master_item_attachments (master_item_id, file_path, original_name, mime_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $itemId, 
                'items/' . $filename, 
                $file['name'], 
                $file['type'], 
                $file['size'], 
                AuthHelper::user()['id']
            ]);
        }
    }

    public function show($id) {
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("SELECT m.*, c.name as category_name, u.name as unit_name 
                               FROM master_items m 
                               LEFT JOIN item_categories c ON m.item_category_id = c.id 
                               LEFT JOIN units u ON m.unit_id = u.id 
                               WHERE m.id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            $_SESSION['error'] = "Barang tidak ditemukan.";
            header("Location: " . url('/items'));
            exit;
        }

        $sn_id = $_GET['sn_id'] ?? null;
        $warehouse_id = $_GET['warehouse_id'] ?? null;

        // 1. Photos
        $stmtPhotos = $pdo->prepare("SELECT * FROM master_item_attachments WHERE master_item_id = ?");
        $stmtPhotos->execute([$id]);
        $attachments = $stmtPhotos->fetchAll();

        // 2. Stock per warehouse (Physical, Reserved, Allocated, Available)
        // Note: Reserved and Allocated are placeholders for Phase 7
        $stockStmt = $pdo->prepare("
            SELECT s.quantity as physical, w.name as warehouse_name, w.id as warehouse_id, 0 as reserved, 0 as allocated,
                   (SELECT COUNT(*) FROM serial_numbers sn WHERE sn.master_item_id = ? AND sn.current_warehouse_id = s.warehouse_id) as serials_in_wh,
                   (SELECT COUNT(*) FROM serial_numbers sn2 WHERE sn2.master_item_id = ? AND sn2.current_warehouse_id = s.warehouse_id AND sn2.status = 'AVAILABLE') as avail_serials
            FROM stocks s 
            JOIN warehouses w ON s.warehouse_id = w.id 
            WHERE s.master_item_id = ? AND s.quantity > 0
        ");
        $stockStmt->execute([$id, $id, $id]);
        $stocks = $stockStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Serialized (If SN)
        $serials = [];
        $snDetail = null;
        if ($item['is_serialized']) {
            if ($sn_id) {
                // Focus on this SN
                $snStmt = $pdo->prepare("SELECT sn.*, w.name as warehouse_name, u.name as holder_name FROM serial_numbers sn LEFT JOIN warehouses w ON sn.current_warehouse_id = w.id LEFT JOIN users u ON sn.current_holder_id = u.id WHERE sn.id = ? AND sn.master_item_id = ?");
                $snStmt->execute([$sn_id, $id]);
                $snDetail = $snStmt->fetch();
            } else {
                // Fetch top SNs
                $snStmt = $pdo->prepare("SELECT sn.*, w.name as warehouse_name, u.name as holder_name FROM serial_numbers sn LEFT JOIN warehouses w ON sn.current_warehouse_id = w.id LEFT JOIN users u ON sn.current_holder_id = u.id WHERE sn.master_item_id = ? ORDER BY sn.id DESC LIMIT 100");
                $snStmt->execute([$id]);
                $serials = $snStmt->fetchAll();
            }
        }

        // 4. History / Timeline / Audit 
        // We will pass this data to the view, or fetch via AJAX, but doing it in PHP is fine for now.
        $history = [];
        if ($snDetail) {
            // Fetch usage cycles for this SN
            // Incoming -> Installation/Loan/Transfer
            $historyStmt = $pdo->prepare("
                SELECT sm.*, u.name as user_name, wf.name as from_warehouse, wt.name as to_warehouse, hf.name as from_holder, ht.name as to_holder
                FROM serial_movements sm
                LEFT JOIN users u ON sm.performed_by = u.id
                LEFT JOIN warehouses wf ON sm.from_warehouse_id = wf.id
                LEFT JOIN warehouses wt ON sm.to_warehouse_id = wt.id
                LEFT JOIN users hf ON sm.from_holder_id = hf.id
                LEFT JOIN users ht ON sm.to_holder_id = ht.id
                WHERE sm.serial_number_id = ?
                ORDER BY sm.created_at DESC
            ");
            $historyStmt->execute([$sn_id]);
            $history = $historyStmt->fetchAll();
        } else {
            // Bulk or General Item History
            $historyStmt = $pdo->prepare("
                SELECT sl.*, u.name as user_name, w.name as warehouse_name
                FROM stock_ledgers sl
                LEFT JOIN users u ON sl.performed_by = u.id
                LEFT JOIN warehouses w ON sl.warehouse_id = w.id
                WHERE sl.master_item_id = ?
                ORDER BY sl.created_at DESC LIMIT 100
            ");
            $historyStmt->execute([$id]);
            $history = $historyStmt->fetchAll();

            // Resolve transaction reference code + route untuk setiap ledger row
            // (reference_type/reference_id -> transaction_code pada tabel transaksi).
            $refTables = [
                'incoming' => ['table' => 'incoming', 'code_col' => 'transaction_code', 'route' => null],
                'outgoing' => ['table' => 'outgoing', 'code_col' => 'transaction_code', 'route' => '/outgoing/show?id='],
                'loans'    => ['table' => 'loans', 'code_col' => 'transaction_code', 'route' => '/loans/show?id='],
                'returns'  => ['table' => 'returns', 'code_col' => 'transaction_code', 'route' => '/returns/show?id='],
                'transfers'=> ['table' => 'transfers', 'code_col' => 'transaction_code', 'route' => '/transfers/show?id='],
                'stock_adjustments' => ['table' => 'stock_adjustments', 'code_col' => 'transaction_code', 'route' => '/adjustments/show?id='],
                'submissions' => ['table' => 'submissions', 'code_col' => 'submission_code', 'route' => '/submissions/show?id='],
                'incidents' => ['table' => 'incidents', 'code_col' => 'transaction_code', 'route' => '/incidents/show?id='],
            ];
            foreach ($history as &$h) {
                $h['ref_code'] = null;
                $h['ref_route'] = null;
                $rt = $h['reference_type'] ?? '';
                $rid = (int)($h['reference_id'] ?? 0);
                if ($rt !== '' && $rid > 0 && isset($refTables[$rt])) {
                    $meta = $refTables[$rt];
                    try {
                        $st = $pdo->prepare("SELECT " . $meta['code_col'] . " AS code FROM " . $meta['table'] . " WHERE id = ?");
                        $st->execute([$rid]);
                        $code = $st->fetchColumn();
                        if ($code) {
                            $h['ref_code'] = $code;
                            if ($meta['route']) $h['ref_route'] = $meta['route'] . $rid;
                        }
                    } catch (\Exception $e) {
                        // non-fatal: reference tetap tampil sebagai ID bila lookup gagal
                    }
                }
            }
            unset($h);
        }
        
        // 5. Inventory 360 Summary: satu ringkasan lintas modul untuk barang ini.
        $inventory360 = [
            'physical_total' => 0,
            'available_total' => 0,
            'borrowed_total' => 0,
            'in_transit_total' => 0,
            'installed_total' => 0,
            'incoming_total' => 0,
            'outgoing_total' => 0,
            'loan_total' => 0,
            'return_total' => 0,
            'transfer_total' => 0,
            'submission_total' => 0,
            'adjustment_total' => 0
        ];

        if ((int)$item['is_serialized'] === 1) {
            $st = $pdo->prepare("SELECT COUNT(*)
                                 FROM serial_numbers
                                 WHERE master_item_id = ?");
            $st->execute([$id]);
            $inventory360['physical_total'] = (int)$st->fetchColumn();

            $st = $pdo->prepare("SELECT COUNT(*)
                                 FROM serial_numbers
                                 WHERE master_item_id = ? AND status = 'AVAILABLE'");
            $st->execute([$id]);
            $inventory360['available_total'] = (int)$st->fetchColumn();

            $st = $pdo->prepare("SELECT COUNT(*)
                                 FROM loan_item_serials lis
                                 JOIN loan_items li ON li.id = lis.loan_item_id
                                 JOIN loans l ON l.id = li.loan_id
                                 WHERE li.master_item_id = ?
                                   AND l.status IN ('BORROWED','PARTIAL_RETURNED')
                                   AND lis.status = 'BORROWED'");
            $st->execute([$id]);
            $inventory360['borrowed_total'] = (int)$st->fetchColumn();

            $st = $pdo->prepare("SELECT COUNT(*)
                                 FROM installation_items ii
                                 JOIN installations ins ON ins.id = ii.installation_id
                                 WHERE ii.master_item_id = ?
                                   AND ins.status = 'INSTALLED'");
            $st->execute([$id]);
            $inventory360['installed_total'] = (int)$st->fetchColumn();
        } else {
            $st = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM stocks WHERE master_item_id = ?");
            $st->execute([$id]);
            $inventory360['physical_total'] = (int)$st->fetchColumn();
            $inventory360['available_total'] = $inventory360['physical_total'];

            $st = $pdo->prepare("SELECT COALESCE(SUM(li.quantity - li.returned_quantity),0)
                                 FROM loan_items li
                                 JOIN loans l ON l.id = li.loan_id
                                 WHERE li.master_item_id = ?
                                   AND l.status IN ('BORROWED','PARTIAL_RETURNED')");
            $st->execute([$id]);
            $inventory360['borrowed_total'] = max(0, (int)$st->fetchColumn());

            $st = $pdo->prepare("SELECT COALESCE(SUM(ii.quantity),0)
                                 FROM installation_items ii
                                 JOIN installations ins ON ins.id = ii.installation_id
                                 WHERE ii.master_item_id = ?
                                   AND ins.status = 'INSTALLED'");
            $st->execute([$id]);
            $inventory360['installed_total'] = max(0, (int)$st->fetchColumn());
        }

        $st = $pdo->prepare("SELECT COALESCE(SUM(ti.quantity_sent - ti.quantity_received),0)
                             FROM transfer_items ti
                             JOIN transfers t ON t.id = ti.transfer_id
                             WHERE ti.master_item_id = ?
                               AND t.status IN ('IN_TRANSIT','PARTIAL_RECEIVED')
                               AND ti.quantity_sent > ti.quantity_received");
        $st->execute([$id]);
        $inventory360['in_transit_total'] = max(0, (int)$st->fetchColumn());

        $st = $pdo->prepare("SELECT COALESCE(SUM(ii.quantity),0) FROM incoming_items ii JOIN incoming i ON i.id = ii.incoming_id WHERE ii.master_item_id = ?");
        $st->execute([$id]);
        $inventory360['incoming_total'] = max(0, (int)$st->fetchColumn());

        $st = $pdo->prepare("SELECT COALESCE(SUM(oi.quantity),0) FROM outgoing_items oi JOIN outgoing o ON o.id = oi.outgoing_id WHERE oi.master_item_id = ? AND o.status = 'COMPLETED'");
        $st->execute([$id]);
        $inventory360['outgoing_total'] = max(0, (int)$st->fetchColumn());

        $st = $pdo->prepare("SELECT COALESCE(SUM(li.quantity),0) FROM loan_items li JOIN loans l ON l.id = li.loan_id WHERE li.master_item_id = ? AND l.status NOT IN ('PENDING_APPROVAL','REJECTED')");
        $st->execute([$id]);
        $inventory360['loan_total'] = max(0, (int)$st->fetchColumn());

        $st = $pdo->prepare("SELECT COALESCE(SUM(ri.quantity),0) FROM return_items ri JOIN returns r ON r.id = ri.return_id WHERE ri.master_item_id = ?");
        $st->execute([$id]);
        $inventory360['return_total'] = max(0, (int)$st->fetchColumn());

        $st = $pdo->prepare("SELECT COALESCE(SUM(ti.quantity_sent),0) FROM transfer_items ti JOIN transfers t ON t.id = ti.transfer_id WHERE ti.master_item_id = ?");
        $st->execute([$id]);
        $inventory360['transfer_total'] = max(0, (int)$st->fetchColumn());

        $st = $pdo->prepare("SELECT COUNT(*) FROM submission_items si WHERE si.master_item_id = ?");
        $st->execute([$id]);
        $inventory360['submission_total'] = max(0, (int)$st->fetchColumn());

        $st = $pdo->prepare("SELECT COALESCE(SUM(ABS(sai.difference)),0)
                             FROM stock_adjustment_items sai
                             WHERE sai.master_item_id = ?");
        $st->execute([$id]);
        $inventory360['adjustment_total'] = max(0, (int)$st->fetchColumn());

        // Timeline gabungan seluruh lifecycle barang.
        $inventory360TimelineSql = "
            SELECT event_time, event_type, reference_code, actor_name, warehouse_name,
                   quantity_delta, description, detail_url
            FROM (
                SELECT i.created_at AS event_time,
                       'INCOMING' AS event_type,
                       i.transaction_code AS reference_code,
                       u.name AS actor_name,
                       w.name AS warehouse_name,
                       ii.quantity AS quantity_delta,
                       CONCAT('Barang masuk ', ii.quantity, ' unit') AS description,
                       NULL AS detail_url
                FROM incoming_items ii
                JOIN incoming i ON i.id = ii.incoming_id
                LEFT JOIN users u ON u.id = i.received_by
                LEFT JOIN warehouses w ON w.id = i.warehouse_id
                WHERE ii.master_item_id = ?

                UNION ALL

                SELECT o.created_at,
                       'OUTGOING',
                       o.transaction_code,
                       u.name,
                       w.name,
                       -oi.quantity,
                       CONCAT('Barang keluar ', oi.quantity, ' unit'),
                       CONCAT('/outgoing/show?id=', o.id)
                FROM outgoing_items oi
                JOIN outgoing o ON o.id = oi.outgoing_id
                LEFT JOIN users u ON u.id = o.processed_by
                LEFT JOIN warehouses w ON w.id = o.warehouse_id
                WHERE oi.master_item_id = ?

                UNION ALL

                SELECT t.sent_at,
                       'TRANSFER',
                       t.transaction_code,
                       u.name,
                       ws.name,
                       -ti.quantity_sent,
                       CONCAT('Transfer keluar ', ti.quantity_sent, ' unit → ', wd.name),
                       CONCAT('/transfers/show?id=', t.id)
                FROM transfer_items ti
                JOIN transfers t ON t.id = ti.transfer_id
                LEFT JOIN users u ON u.id = t.sent_by
                LEFT JOIN warehouses ws ON ws.id = t.source_warehouse_id
                LEFT JOIN warehouses wd ON wd.id = t.destination_warehouse_id
                WHERE ti.master_item_id = ?

                UNION ALL

                SELECT t.received_at,
                       'TRANSFER_RECEIVED',
                       t.transaction_code,
                       u.name,
                       wd.name,
                       ti.quantity_received,
                       CONCAT('Transfer diterima ', ti.quantity_received, ' unit'),
                       CONCAT('/transfers/show?id=', t.id)
                FROM transfer_items ti
                JOIN transfers t ON t.id = ti.transfer_id
                LEFT JOIN users u ON u.id = t.received_by
                LEFT JOIN warehouses wd ON wd.id = t.destination_warehouse_id
                WHERE ti.master_item_id = ? AND t.received_at IS NOT NULL

                UNION ALL

                SELECT l.created_at,
                       'LOAN',
                       l.transaction_code,
                       u.name,
                       w.name,
                       -li.quantity,
                       CONCAT('Dipinjam ', li.quantity, ' unit'),
                       CONCAT('/loans/show?id=', l.id)
                FROM loan_items li
                JOIN loans l ON l.id = li.loan_id
                LEFT JOIN users u ON u.id = l.borrower_id
                LEFT JOIN warehouses w ON w.id = l.warehouse_id
                WHERE li.master_item_id = ? AND l.status NOT IN ('PENDING_APPROVAL','REJECTED')

                UNION ALL

                SELECT r.created_at,
                       'RETURN',
                       r.transaction_code,
                       u.name,
                       w.name,
                       ri.quantity,
                       CONCAT('Dikembalikan ', ri.quantity, ' unit'),
                       CONCAT('/returns/show?id=', r.id)
                FROM return_items ri
                JOIN returns r ON r.id = ri.return_id
                LEFT JOIN users u ON u.id = r.processed_by
                LEFT JOIN warehouses w ON w.id = r.warehouse_id
                WHERE ri.master_item_id = ?

                UNION ALL

                SELECT ins.installed_at,
                       'INSTALLATION',
                       ins.transaction_code,
                       u.name,
                       NULL,
                       -ii.quantity,
                       CONCAT('Dipakai untuk instalasi ', ii.quantity, ' unit di ', ins.customer_site),
                       CONCAT('/installations/show?id=', ins.id)
                FROM installation_items ii
                JOIN installations ins ON ins.id = ii.installation_id
                LEFT JOIN users u ON u.id = ins.technician_id
                WHERE ii.master_item_id = ?

                UNION ALL

                SELECT s.created_at,
                       'SUBMISSION',
                       s.submission_code,
                       u.name,
                       NULL,
                       si.approved_quantity,
                       CONCAT('Pengajuan disetujui ', si.approved_quantity, ' unit'),
                       CONCAT('/submissions/show?id=', s.id)
                FROM submission_items si
                JOIN submissions s ON s.id = si.submission_id
                LEFT JOIN users u ON u.id = s.submitted_by
                WHERE si.master_item_id = ? AND si.approved_quantity > 0

                UNION ALL

                SELECT sa.created_at,
                       'ADJUSTMENT',
                       sa.transaction_code,
                       u.name,
                       w.name,
                       sai.difference,
                       CONCAT('Penyesuaian stok ', CASE WHEN sai.difference >= 0 THEN '+' ELSE '' END, sai.difference, ' unit'),
                       CONCAT('/adjustments/show?id=', sa.id)
                FROM stock_adjustment_items sai
                JOIN stock_adjustments sa ON sa.id = sai.stock_adjustment_id
                LEFT JOIN users u ON u.id = sa.processed_by
                LEFT JOIN warehouses w ON w.id = sa.warehouse_id
                WHERE sai.master_item_id = ?
            ) inventory_events
            ORDER BY event_time DESC
            LIMIT 100";
        $timelineStmt = $pdo->prepare($inventory360TimelineSql);
        $timelineStmt->execute(array_fill(0, 9, $id));
        $inventory360Timeline = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Installations / Customer / Site
        $installations = [];
        if ($snDetail) {
            // Multiple cycles
            $instStmt = $pdo->prepare("
                SELECT i.*, i.installed_at 
                FROM installation_items ii
                JOIN installations i ON ii.installation_id = i.id
                WHERE ii.serial_number_id = ?
                ORDER BY i.installed_at DESC
            ");
            $instStmt->execute([$sn_id]);
            $installations = $instStmt->fetchAll();
        }

        require __DIR__ . '/../../resources/views/inventory/items/show.php';
    }

    public function incomingEvidenceHistory($id) {
        // Read-only Inventory 360 evidence history for ADMIN/SUPERADMIN.
        \App\Helpers\AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
        $pdo = Database::getInstance()->getConnection();
        $id = (int)$id;
        $warehouseId = isset($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : 0;

        $stmt = $pdo->prepare("SELECT m.id, m.kode_barang, m.nama, m.is_active, m.is_serialized,
                                      c.name AS category_name, u.name AS unit_name
                               FROM master_items m
                               LEFT JOIN item_categories c ON m.item_category_id = c.id
                               LEFT JOIN units u ON m.unit_id = u.id
                               WHERE m.id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            $_SESSION['error'] = "Barang tidak ditemukan.";
            header("Location: " . url('/items'));
            exit;
        }

        $photoStmt = $pdo->prepare("SELECT id, file_path, original_name, mime_type, file_size, created_at
                                    FROM master_item_attachments
                                    WHERE master_item_id = ?
                                    ORDER BY id ASC");
        $photoStmt->execute([$id]);
        $masterPhotos = $photoStmt->fetchAll(PDO::FETCH_ASSOC);

        $sql = "SELECT i.id AS incoming_id, i.transaction_code, i.created_at, i.supplier_info,
                       i.warehouse_id, w.name AS warehouse_name,
                       a.id AS attachment_id, a.file_path, a.original_name, a.mime_type,
                       a.file_size, a.created_at AS attachment_created_at,
                       u.name AS uploader_name
                FROM incoming_items ii
                JOIN incoming i ON i.id = ii.incoming_id
                JOIN warehouses w ON w.id = i.warehouse_id
                LEFT JOIN attachments a
                  ON a.reference_type = 'INCOMING'
                 AND a.reference_id = i.id
                LEFT JOIN users u ON u.id = a.uploaded_by
                WHERE ii.master_item_id = ?";
        $params = [$id];

        if ($warehouseId > 0) {
            $sql .= " AND i.warehouse_id = ?";
            $params[] = $warehouseId;
        }

        $sql .= " ORDER BY i.created_at DESC, a.created_at ASC, a.id ASC";

        $incomingStmt = $pdo->prepare($sql);
        $incomingStmt->execute($params);
        $rows = $incomingStmt->fetchAll(PDO::FETCH_ASSOC);

        $incomingEvidence = [];
        $incomingCount = 0;
        $photoCount = 0;

        foreach ($rows as $row) {
            $incomingId = (int)$row['incoming_id'];
            if (!isset($incomingEvidence[$incomingId])) {
                $incomingEvidence[$incomingId] = [
                    'incoming_id' => $incomingId,
                    'transaction_code' => $row['transaction_code'],
                    'created_at' => $row['created_at'],
                    'supplier_info' => $row['supplier_info'],
                    'warehouse_id' => (int)$row['warehouse_id'],
                    'warehouse_name' => $row['warehouse_name'],
                    'photos' => [],
                ];
                $incomingCount++;
            }

            if (!empty($row['attachment_id'])) {
                $incomingEvidence[$incomingId]['photos'][] = [
                    'id' => (int)$row['attachment_id'],
                    'file_path' => $row['file_path'],
                    'original_name' => $row['original_name'],
                    'mime_type' => $row['mime_type'],
                    'file_size' => (int)$row['file_size'],
                    'created_at' => $row['attachment_created_at'],
                    'uploader_name' => $row['uploader_name'] ?? 'System',
                ];
                $photoCount++;
            }
        }

        $incomingEvidence = array_values($incomingEvidence);
        $selectedWarehouseName = null;
        if ($warehouseId > 0) {
            $whStmt = $pdo->prepare("SELECT name FROM warehouses WHERE id = ?");
            $whStmt->execute([$warehouseId]);
            $selectedWarehouseName = $whStmt->fetchColumn() ?: null;
        }

        require __DIR__ . '/../../resources/views/inventory/items/incoming_evidence.php';
    }

    public function edit($id) {
        // Write Master Barang HANYA untuk ADMIN.
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM master_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            $_SESSION['error'] = "Item not found.";
            header("Location: " . url('/items'));
            exit;
        }

        $catStmt = $pdo->prepare("SELECT * FROM item_categories WHERE is_active = 1 OR id = ?");
        $catStmt->execute([$item['item_category_id']]);
        $categories = $catStmt->fetchAll();
        
        $unitStmt = $pdo->prepare("SELECT * FROM units WHERE is_active = 1 OR id = ?");
        $unitStmt->execute([$item['unit_id']]);
        $units = $unitStmt->fetchAll();
        
        $stmt = $pdo->prepare("SELECT * FROM master_item_attachments WHERE master_item_id = ?");
        $stmt->execute([$id]);
        $attachments = $stmt->fetchAll();

        // Cek apakah barang sudah pernah memiliki transaksi hilir (keluar/pakai)
        $downstreamStmt = $pdo->prepare("SELECT COUNT(*) FROM (
            SELECT master_item_id FROM outgoing_items WHERE master_item_id = ?
            UNION SELECT master_item_id FROM return_items WHERE master_item_id = ?
            UNION SELECT master_item_id FROM loan_items WHERE master_item_id = ?
            UNION SELECT master_item_id FROM installation_items WHERE master_item_id = ?
            UNION SELECT master_item_id FROM transfer_items WHERE master_item_id = ?
            UNION SELECT master_item_id FROM stock_adjustment_items WHERE master_item_id = ?
            UNION SELECT master_item_id FROM incident_items WHERE master_item_id = ?
        ) as t");
        $downstreamStmt->execute([$id, $id, $id, $id, $id, $id, $id]);
        $hasDownstream = (int)$downstreamStmt->fetchColumn() > 0;

        // Ambil stok saat ini per gudang
        $stocksStmt = $pdo->prepare("SELECT s.warehouse_id, w.name AS warehouse_name, s.quantity 
            FROM stocks s 
            JOIN warehouses w ON s.warehouse_id = w.id 
            WHERE s.master_item_id = ? AND s.quantity > 0 
            ORDER BY w.name ASC");
        $stocksStmt->execute([$id]);
        $currentStocks = $stocksStmt->fetchAll();

        require __DIR__ . '/../../resources/views/inventory/items/edit.php';
    }

    public function update($id) {
        // Write Master Barang HANYA untuk ADMIN.
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/items'));
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        $item_category_id = $_POST['item_category_id'] ?? null;
        $unit_id = $_POST['unit_id'] ?? null;
        $is_serialized = isset($_POST['is_serialized']) ? 1 : 0;
        $stok_minimum = (int)($_POST['stok_minimum'] ?? 0);
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($nama === '' || empty($item_category_id) || empty($unit_id)) {
            $_SESSION['error'] = "Nama, kategori, dan satuan wajib diisi.";
            header("Location: " . url('/items'));
            exit;
        }

        if (strlen($nama) > 100) {
            $_SESSION['error'] = "Nama barang maksimal 100 karakter.";
            header("Location: " . url('/items'));
            exit;
        }

        if ($stok_minimum < 0) {
            $_SESSION['error'] = "Stok minimum tidak boleh negatif.";
            header("Location: " . url('/items'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        // Status TIDAK berubah hanya karena form edit lain disimpan: jika field is_active
        // tidak dikirim, pertahankan status existing (mencegah bug "field tidak terkirim -> 0").
        $curActiveStmt = $pdo->prepare("SELECT is_active FROM master_items WHERE id = ?");
        $curActiveStmt->execute([$id]);
        $curActive = (int)$curActiveStmt->fetchColumn();
        $is_active = array_key_exists('is_active', $_POST) ? (isset($_POST['is_active']) ? 1 : 0) : $curActive;

        try {
            $pdo->beginTransaction();

            $oldItem = $pdo->prepare("SELECT is_active, is_serialized, unit_id, item_category_id FROM master_items WHERE id = ? FOR UPDATE");
            $oldItem->execute([$id]);
            $oldItemData = $oldItem->fetch();
            
            if ($oldItemData) {
                // If changing category, validate new category is active
                if ((int)$oldItemData['item_category_id'] !== (int)$item_category_id) {
                    $stmtCheck = $pdo->prepare("SELECT id FROM item_categories WHERE id = ? AND is_active = 1");
                    $stmtCheck->execute([$item_category_id]);
                    if (!$stmtCheck->fetch()) {
                        throw new \Exception("Kategori baru tidak valid atau tidak aktif.");
                    }
                }

                // If changing unit, validate new unit is active
                if ((int)$oldItemData['unit_id'] !== (int)$unit_id) {
                    $stmtCheck = $pdo->prepare("SELECT id FROM units WHERE id = ? AND is_active = 1");
                    $stmtCheck->execute([$unit_id]);
                    if (!$stmtCheck->fetch()) {
                        throw new \Exception("Satuan baru tidak valid atau tidak aktif.");
                    }
                }

                $hasStock = $pdo->query("SELECT COUNT(*) FROM stocks WHERE master_item_id = $id AND quantity > 0")->fetchColumn();
                $hasSerials = $pdo->query("SELECT COUNT(*) FROM serial_numbers WHERE master_item_id = $id")->fetchColumn();
                $hasActiveSerials = $pdo->query("SELECT COUNT(*) FROM serial_numbers WHERE master_item_id = $id AND status = 'AVAILABLE'")->fetchColumn();
                $hasTransactions = $pdo->query("SELECT COUNT(*) FROM (
                    SELECT master_item_id FROM incoming_items WHERE master_item_id = $id
                    UNION SELECT master_item_id FROM outgoing_items WHERE master_item_id = $id
                    UNION SELECT master_item_id FROM return_items WHERE master_item_id = $id
                    UNION SELECT master_item_id FROM loan_items WHERE master_item_id = $id
                    UNION SELECT master_item_id FROM installation_items WHERE master_item_id = $id
                ) as t")->fetchColumn();

                $isLocked = ($hasStock > 0 || $hasSerials > 0 || $hasTransactions > 0);

                // Cek apakah barang sudah pernah memiliki transaksi hilir (keluar/pakai)
                $downstreamStmt = $pdo->prepare("SELECT COUNT(*) FROM (
                    SELECT master_item_id FROM outgoing_items WHERE master_item_id = ?
                    UNION SELECT master_item_id FROM return_items WHERE master_item_id = ?
                    UNION SELECT master_item_id FROM loan_items WHERE master_item_id = ?
                    UNION SELECT master_item_id FROM installation_items WHERE master_item_id = ?
                    UNION SELECT master_item_id FROM transfer_items WHERE master_item_id = ?
                    UNION SELECT master_item_id FROM stock_adjustment_items WHERE master_item_id = ?
                    UNION SELECT master_item_id FROM incident_items WHERE master_item_id = ?
                ) as t");
                $downstreamStmt->execute([$id, $id, $id, $id, $id, $id, $id]);
                $hasDownstream = (int)$downstreamStmt->fetchColumn() > 0;

                $registeredSnsCount = 0;

                if ((int)$oldItemData['is_serialized'] !== (int)$is_serialized) {
                    if ($hasDownstream) {
                        throw new \Exception("Tidak dapat mengubah tipe barang (Serial/Non-Serial) karena barang ini sudah memiliki transaksi keluar, peminjaman, transfer, atau instalasi.");
                    }

                    if ((int)$oldItemData['is_serialized'] === 0 && (int)$is_serialized === 1) {
                        // Konversi dari Non-Serial ke Serial (misal salah input saat awal dan baru masuk stok)
                        $stkStmt = $pdo->prepare("SELECT s.warehouse_id, w.name AS warehouse_name, s.quantity 
                            FROM stocks s 
                            JOIN warehouses w ON s.warehouse_id = w.id 
                            WHERE s.master_item_id = ? AND s.quantity > 0");
                        $stkStmt->execute([$id]);
                        $whStocks = $stkStmt->fetchAll();

                        $conversionSerials = $_POST['conversion_serials'] ?? [];
                        $userId = AuthHelper::user()['id'] ?? null;

                        $incStmt = $pdo->prepare("SELECT incoming_id FROM incoming_items WHERE master_item_id = ? ORDER BY id DESC LIMIT 1");
                        $incStmt->execute([$id]);
                        $refIncomingId = $incStmt->fetchColumn() ?: null;

                        $chkSn = $pdo->prepare("SELECT id FROM serial_numbers WHERE serial_number = ?");
                        $insSn = $pdo->prepare("INSERT INTO serial_numbers (master_item_id, serial_number, status, current_warehouse_id) VALUES (?, ?, 'AVAILABLE', ?)");
                        $insMov = $pdo->prepare("INSERT INTO serial_movements (serial_number_id, from_status, to_status, from_warehouse_id, to_warehouse_id, reference_type, reference_id, performed_by, notes) VALUES (?, NULL, 'AVAILABLE', NULL, ?, 'incoming', ?, ?, ?)");

                        $allSns = [];
                        foreach ($whStocks as $ws) {
                            $whId = (int)$ws['warehouse_id'];
                            $needed = (int)$ws['quantity'];
                            $whName = $ws['warehouse_name'];
                            $rawSn = trim($conversionSerials[$whId] ?? '');

                            $sns = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $rawSn))));

                            if (count($sns) !== $needed) {
                                throw new \Exception("Jumlah nomor seri untuk gudang '$whName' harus tepat $needed unit (terisi: " . count($sns) . ").");
                            }

                            if (count($sns) !== count(array_unique($sns))) {
                                throw new \Exception("Terdapat nomor seri duplikat pada input untuk gudang '$whName'.");
                            }

                            foreach ($sns as $sn) {
                                if (in_array($sn, $allSns, true)) {
                                    throw new \Exception("Nomor seri '$sn' diinput lebih dari satu kali antar gudang.");
                                }
                                $allSns[] = $sn;

                                $chkSn->execute([$sn]);
                                if ($chkSn->fetch()) {
                                    throw new \Exception("Nomor seri '$sn' sudah terdaftar di sistem.");
                                }
                                $insSn->execute([$id, $sn, $whId]);
                                $snId = $pdo->lastInsertId();
                                $insMov->execute([$snId, $whId, $refIncomingId, $userId, "Konversi Master Barang ke Serial"]);
                            }
                        }
                        $registeredSnsCount = count($allSns);
                    } elseif ((int)$oldItemData['is_serialized'] === 1 && (int)$is_serialized === 0) {
                        // Konversi dari Serial ke Non-Serial
                        $nonAvail = (int)$pdo->query("SELECT COUNT(*) FROM serial_numbers WHERE master_item_id = $id AND status != 'AVAILABLE'")->fetchColumn();
                        if ($nonAvail > 0) {
                            throw new \Exception("Tidak dapat mengubah ke Non-Serial karena ada nomor seri yang sedang tidak berstatus AVAILABLE.");
                        }
                        $pdo->prepare("DELETE sm FROM serial_movements sm JOIN serial_numbers sn ON sm.serial_number_id = sn.id WHERE sn.master_item_id = ?")->execute([$id]);
                        $pdo->prepare("DELETE FROM serial_numbers WHERE master_item_id = ?")->execute([$id]);
                    }
                }

                if ((int)$oldItemData['unit_id'] !== (int)$unit_id && $isLocked) {
                    throw new \Exception("Tidak dapat mengubah Satuan karena barang ini sudah memiliki histori transaksi.");
                }
                if ((int)$oldItemData['item_category_id'] !== (int)$item_category_id && $isLocked) {
                    throw new \Exception("Tidak dapat mengubah Kategori karena barang ini sudah memiliki histori transaksi.");
                }
                
                if ((int)$oldItemData['is_active'] === 1 && $is_active === 0) {
                    if ($hasStock > 0 || $hasActiveSerials > 0) {
                        throw new \Exception("Barang tidak dapat dinonaktifkan karena masih memiliki stok fisik atau serial aktif. Selesaikan stok atau pergerakan barang terlebih dahulu.");
                    }
                }
            }

            $stmt = $pdo->prepare("UPDATE master_items SET nama = ?, item_category_id = ?, unit_id = ?, is_serialized = ?, stok_minimum = ?, deskripsi = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$nama, $item_category_id, $unit_id, $is_serialized, $stok_minimum, $deskripsi, $is_active, $id]);
            
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $this->uploadPhoto($id, $_FILES['photo'], $pdo);
            }

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'UPDATE', 'master_items', ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $id]);
            
            $pdo->commit();
            $_SESSION['success'] = "Master barang berhasil diperbarui" . ($registeredSnsCount > 0 ? " dan $registeredSnsCount nomor seri berhasil didaftarkan." : ".");
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal memperbarui barang: " . $e->getMessage();
            header("Location: " . url('/items/edit/' . $id));
            exit;
        }

        header("Location: " . url('/items'));
        exit;
    }

    public function deletePhoto($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/items'));
            exit;
        }

        $id = (int)$id;
        $pdo = \App\Core\Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM master_item_attachments WHERE id = ?");
        $stmt->execute([$id]);
        $photo = $stmt->fetch();
        
        if (!$photo) {
            $_SESSION['error'] = "Foto tidak ditemukan.";
            header("Location: " . url('/items'));
            exit;
        }

        try {
            $file = __DIR__ . '/../../storage/private_uploads/' . $photo['file_path'];
            if (is_file($file)) {
                @unlink($file);
            }
            
            $pdo->prepare("DELETE FROM master_item_attachments WHERE id = ?")->execute([$id]);
            
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'DELETE_PHOTO', 'master_items', ?)");
            $auditStmt->execute([\App\Helpers\AuthHelper::user()['id'], $photo['master_item_id']]);
            
            $_SESSION['success'] = "Foto berhasil dihapus.";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Gagal menghapus foto: " . $e->getMessage();
        }

        header("Location: " . url('/items/edit/' . $photo['master_item_id']));
        exit;
    }

    public function replacePhoto($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/items'));
            exit;
        }

        $id = (int)$id;
        $pdo = \App\Core\Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM master_item_attachments WHERE id = ?");
        $stmt->execute([$id]);
        $photo = $stmt->fetch();
        
        if (!$photo) {
            $_SESSION['error'] = "Foto tidak ditemukan.";
            header("Location: " . url('/items'));
            exit;
        }

        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Silakan pilih foto baru yang valid.";
            header("Location: " . url('/items/edit/' . $photo['master_item_id']));
            exit;
        }
        
        $file = $_FILES['photo'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedMimes)) {
            $_SESSION['error'] = "Tipe file tidak diizinkan. Hanya JPG, PNG, GIF, WEBP.";
            header("Location: " . url('/items/edit/' . $photo['master_item_id']));
            exit;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = "Ukuran file maksimal 5MB.";
            header("Location: " . url('/items/edit/' . $photo['master_item_id']));
            exit;
        }

        try {
            // Delete old file
            $oldFile = __DIR__ . '/../../storage/private_uploads/' . $photo['file_path'];
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }

            // Upload new file
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('item_') . '.' . $ext;
            $uploadDir = __DIR__ . '/../../storage/private_uploads/items/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $updStmt = $pdo->prepare("UPDATE master_item_attachments SET file_path = ?, original_name = ?, mime_type = ?, file_size = ?, uploaded_by = ? WHERE id = ?");
                $updStmt->execute([
                    'items/' . $filename,
                    $file['name'],
                    $file['type'],
                    $file['size'],
                    \App\Helpers\AuthHelper::user()['id'],
                    $id
                ]);
                
                $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'REPLACE_PHOTO', 'master_items', ?)");
                $auditStmt->execute([\App\Helpers\AuthHelper::user()['id'], $photo['master_item_id']]);
                
                $_SESSION['success'] = "Foto berhasil diganti.";
            } else {
                $_SESSION['error'] = "Gagal mengunggah foto baru.";
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        }

        header("Location: " . url('/items/edit/' . $photo['master_item_id']));
        exit;
    }

    /**
     * Hapus Permanen Master Barang — HANYA jika barang belum pernah digunakan
     * (tanpa stok, tanpa nomor seri, tanpa histori transaksi). Backend guard WAJIB;
     * frontend bukan security control.
     */
    public function destroy($id) {
        // Backend guard wajib: (1) user adalah ADMIN, (2) barang ada, (3) barang belum pernah digunakan.
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/items'));
            exit;
        }

        $id = (int)$id;
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare("SELECT id, kode_barang, nama, is_active FROM master_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        if (!$item) {
            $_SESSION['error'] = "Barang tidak ditemukan.";
            header("Location: " . url('/items'));
            exit;
        }

        // Guard: barang yang sudah pernah digunakan tidak boleh dihapus permanen.
        $used = $this->isItemUsed($pdo, $id);
        if ($used) {
            $_SESSION['error'] = "Barang tidak dapat dihapus karena sudah memiliki riwayat transaksi. Gunakan Nonaktifkan Barang.";
            header("Location: " . url('/items'));
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Hapus foto/lampiran master (baris DB; file fisik ikut dibersihkan bila ada).
            $attStmt = $pdo->prepare("SELECT file_path FROM master_item_attachments WHERE master_item_id = ?");
            $attStmt->execute([$id]);
            foreach ($attStmt->fetchAll() as $att) {
                $file = __DIR__ . '/../../storage/private_uploads/' . $att['file_path'];
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $pdo->prepare("DELETE FROM master_item_attachments WHERE master_item_id = ?")->execute([$id]);

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'DELETE', 'master_items', ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $id]);

            $pdo->prepare("DELETE FROM master_items WHERE id = ?")->execute([$id]);
            $pdo->commit();

            $_SESSION['success'] = "Barang '" . $item['kode_barang'] . "' berhasil dihapus permanen.";
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal menghapus barang: " . $e->getMessage();
        }

        header("Location: " . url('/items'));
        exit;
    }

    /**
     * Nonaktifkan / Aktifkan Master Barang.
     * Menonaktifkan hanya diperbolehkan bila barang tidak lagi memiliki stok fisik
     * atau serial aktif (rule sama dengan update()). Riwayat tetap tersimpan dan
     * laporan tetap utuh; barang nonaktif tidak dapat dipakai transaksi baru
     * (incoming/transfer/submission mem-filter is_active = 1).
     */
    public function toggleActive($id) {
        // Nonaktifkan/Aktifkan Master Barang HANYA untuk ADMIN.
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/items'));
            exit;
        }

        $id = (int)$id;
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare("SELECT id, kode_barang, nama, is_active FROM master_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        if (!$item) {
            $_SESSION['error'] = "Barang tidak ditemukan.";
            header("Location: " . url('/items'));
            exit;
        }

        try {
            $pdo->beginTransaction();

            if ((int)$item['is_active'] === 1) {
                // Nonaktifkan — guard: tidak boleh selama masih ada stok fisik atau serial aktif.
                $hasStock = $pdo->query("SELECT COUNT(*) FROM stocks WHERE master_item_id = $id AND quantity > 0")->fetchColumn();
                $hasActiveSerials = $pdo->query("SELECT COUNT(*) FROM serial_numbers WHERE master_item_id = $id AND status = 'AVAILABLE'")->fetchColumn();
                if ($hasStock > 0 || $hasActiveSerials > 0) {
                    throw new \Exception("Barang tidak dapat dinonaktifkan karena masih memiliki stok fisik atau serial aktif. Selesaikan stok atau pergerakan barang terlebih dahulu.");
                }
                $pdo->prepare("UPDATE master_items SET is_active = 0 WHERE id = ?")->execute([$id]);
                $action = 'DEACTIVATE_ITEM';
                $msg = "Barang '" . $item['kode_barang'] . "' berhasil dinonaktifkan.";
            } else {
                $pdo->prepare("UPDATE master_items SET is_active = 1 WHERE id = ?")->execute([$id]);
                $action = 'ACTIVATE_ITEM';
                $msg = "Barang '" . $item['kode_barang'] . "' berhasil diaktifkan.";
            }

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, ?, 'master_items', ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $action, $id]);

            $pdo->commit();
            $_SESSION['success'] = $msg;
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal mengubah status barang: " . $e->getMessage();
        }

        header("Location: " . url('/items'));
        exit;
    }

    /**
     * Cek apakah master item sudah pernah digunakan (stok, serial, atau histori transaksi).
     * Dipakai sebagai guard delete (backend), konsisten dengan definisi "is_used" di index().
     */
    private function isItemUsed($pdo, $id) {
        $id = (int)$id;
        $sql = "SELECT COUNT(*) FROM (
            SELECT master_item_id FROM stocks WHERE master_item_id = $id AND quantity > 0
            UNION SELECT master_item_id FROM serial_numbers WHERE master_item_id = $id
            UNION SELECT master_item_id FROM incoming_items WHERE master_item_id = $id
            UNION SELECT master_item_id FROM outgoing_items WHERE master_item_id = $id
            UNION SELECT master_item_id FROM transfer_items WHERE master_item_id = $id
            UNION SELECT master_item_id FROM return_items WHERE master_item_id = $id
            UNION SELECT master_item_id FROM loan_items WHERE master_item_id = $id
            UNION SELECT master_item_id FROM installation_items WHERE master_item_id = $id
            UNION SELECT master_item_id FROM stock_adjustment_items WHERE master_item_id = $id
            UNION SELECT master_item_id FROM submission_items WHERE master_item_id = $id
            UNION SELECT master_item_id FROM stock_ledgers WHERE master_item_id = $id
        ) as t";
        return (int)$pdo->query($sql)->fetchColumn() > 0;
    }
}
