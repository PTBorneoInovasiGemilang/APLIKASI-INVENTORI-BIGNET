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

                if ((int)$oldItemData['is_serialized'] !== (int)$is_serialized && $isLocked) {
                    throw new \Exception("Tidak dapat mengubah tipe barang (Serial/Non-Serial) karena barang ini sudah memiliki stok, histori nomor seri, atau transaksi.");
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
            $_SESSION['success'] = "Item updated successfully.";
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Failed to update item: " . $e->getMessage();
        }

        header("Location: " . url('/items'));
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
