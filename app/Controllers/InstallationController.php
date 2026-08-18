<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use App\Services\StockService;
use PDO;

class InstallationController {
    
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

        if ($search !== '') {
            $whereClauses[] = "(i.transaction_code LIKE ? OR i.customer_site LIKE ? OR i.customer_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        // Count for pagination
        $countSql = "SELECT COUNT(*) FROM installations i 
                     JOIN users u ON i.technician_id = u.id 
                     $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "SELECT i.*, u.name as technician_name 
                FROM installations i 
                JOIN users u ON i.technician_id = u.id 
                $whereSql
                ORDER BY i.id DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $installations = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/installations/index.php';
    }

    public function show() {
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("SELECT i.*, u.name as technician_name 
                FROM installations i 
                JOIN users u ON i.technician_id = u.id 
                WHERE i.id = ?");
        $stmt->execute([$id]);
        $installation = $stmt->fetch();

        if (!$installation) {
            $_SESSION['error'] = "Instalasi tidak ditemukan.";
            header("Location: " . url('/installations'));
            exit;
        }

        $itemsStmt = $pdo->prepare("SELECT ii.*, m.kode_barang, m.nama, m.is_serialized 
                                    FROM installation_items ii 
                                    JOIN master_items m ON ii.master_item_id = m.id 
                                    WHERE ii.installation_id = ?");
        $itemsStmt->execute([$id]);
        $items = $itemsStmt->fetchAll();

        // Get SNs for each serialized item
        foreach ($items as &$item) {
            if ($item['is_serialized']) {
                $snStmt = $pdo->prepare("SELECT sn.serial_number FROM serial_movements sm JOIN serial_numbers sn ON sm.serial_number_id = sn.id WHERE sm.reference_type = 'installation' AND sm.reference_id = ? AND sn.master_item_id = ?");
                $snStmt->execute([$id, $item['master_item_id']]);
                $sns = $snStmt->fetchAll(PDO::FETCH_COLUMN);
                $item['sn_list_display'] = implode(', ', $sns);
            }
        }
        unset($item);

        require __DIR__ . '/../../resources/views/transactions/installations/show.php';
    }

    public function create() {
        \App\Helpers\AuthHelper::requireRole(['TEKNISI_NOC']);
        $pdo = Database::getInstance()->getConnection();
        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();
        $items = $pdo->query("SELECT id, kode_barang, nama, is_serialized FROM master_items WHERE is_active = 1")->fetchAll();
        $users = [];
        if (AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])) {
            $users = $pdo->query("SELECT id, name FROM users WHERE is_active = 1")->fetchAll();
        }

        require __DIR__ . '/../../resources/views/transactions/installations/create.php';
    }

    public function store() {
        \App\Helpers\AuthHelper::requireRole(['TEKNISI_NOC']);
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed.";
            header("Location: " . url('/installations/create'));
            exit;
        }

        $customer_name = trim($_POST['customer_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $reference_code = trim($_POST['reference_code'] ?? '');
        $customer_site = $customer_name;
        if (!empty($location)) {
            $customer_site .= ' - ' . $location;
        }
        $notes = trim($_POST['notes']);
        $installation_date = $_POST['installation_date'] ?? date('Y-m-d');
        $installed_at = $installation_date . ' ' . date('H:i:s');
        
        $technician_id = AuthHelper::user()['id'];

        $photo_path = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../storage/private_uploads/installations/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileInfo = pathinfo($_FILES['photo']['name']);
            $ext = strtolower($fileInfo['extension']);
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed)) {
                $_SESSION['error'] = "Format foto tidak didukung (hanya JPG, PNG, WEBP).";
                header("Location: " . url('/installations/create'));
                exit;
            }
            
            $mimeType = mime_content_type($_FILES['photo']['tmp_name']);
            if (strpos($mimeType, 'image/') !== 0) {
                $_SESSION['error'] = "File yang diupload bukan gambar valid.";
                header("Location: " . url('/installations/create'));
                exit;
            }
            
            if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                $_SESSION['error'] = "Ukuran foto maksimal 2MB.";
                header("Location: " . url('/installations/create'));
                exit;
            }
            
            $filename = uniqid('inst_') . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                $photo_path = 'installations/' . $filename;
            }
        }

        $item_ids = $_POST['item_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $sn_list = $_POST['sn_list'] ?? [];

        if (empty($item_ids)) {
            $_SESSION['error'] = "Tidak ada barang yang dipilih.";
            header("Location: " . url('/installations/create'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $stockService = new StockService(); 
        
        $transaction_code = \App\Helpers\TransactionHelper::generateCode('INS', 'installations');

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO installations (transaction_code, customer_site, customer_name, technician_id, installed_at, status, notes, photo_path, reference_code) VALUES (?, ?, ?, ?, ?, 'INSTALLED', ?, ?, ?)");
            $stmt->execute([$transaction_code, $customer_site, $customer_name, $technician_id, $installed_at, $notes, $photo_path, $reference_code]);
            $installation_id = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO installation_items (installation_id, master_item_id, quantity) VALUES (?, ?, ?)");
            $itemSnStmt = $pdo->prepare("INSERT INTO installation_items (installation_id, master_item_id, quantity, serial_number_id) VALUES (?, ?, 1, ?)");

            foreach ($item_ids as $index => $item_id) {
                $qty = (int)$quantities[$index];
                if ($qty <= 0) continue;
                
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
                        $chk = $pdo->prepare("SELECT id, status, current_warehouse_id, current_holder_id, master_item_id FROM serial_numbers WHERE serial_number = ? FOR UPDATE");
                        $chk->execute([$sn]);
                        $snRow = $chk->fetch();
                        
                        if (!$snRow) {
                            throw new \Exception("Serial Number '$sn' tidak ditemukan.");
                        }

                        if ($snRow['master_item_id'] != $item_id) {
                            throw new \Exception("Serial Number '$sn' tidak cocok dengan Master Item yang dipilih.");
                        }
                        
                        // Validasi ketat: SN harus berada di scope teknisi (sudah dikeluarkan dari gudang)
                        if (!in_array($snRow['status'], ['ISSUED', 'LOANED'])) {
                            throw new \Exception("Serial Number '$sn' berstatus {$snRow['status']}. SN harus berstatus ISSUED atau LOANED (sudah dikeluarkan dari gudang) untuk dapat diinstal.");
                        }

                        if ($snRow['current_holder_id'] != $technician_id) {
                            throw new \Exception("Serial Number '$sn' tidak sedang dipegang oleh Teknisi yang bertugas.");
                        }
                        
                        $oldStatus = $snRow['status'];
                        $oldWh = $snRow['current_warehouse_id'];
                        $oldHolder = $snRow['current_holder_id'];

                        // Update SN status and record movement using StockService
                        $stockService->moveSerial(
                            $snRow['id'], 
                            'INSTALLED', 
                            null, 
                            null, 
                            null, 
                            'installation', 
                            $installation_id, 
                            "Instalasi - " . $transaction_code
                        );
                        
                        // Insert installation item explicit with serial_number_id
                        $itemSnStmt->execute([$installation_id, $item_id, $snRow['id']]);
                    }
                } else {
                    // Non-serialized item
                    $itemStmt->execute([$installation_id, $item_id, $qty]);
                }
            }

            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'installations', ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $installation_id]);

            $pdo->commit();
            $_SESSION['success'] = "Instalasi berhasil dicatat.";
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal mencatat instalasi: " . $e->getMessage();
            header("Location: " . url('/installations/create'));
            exit;
        }

        // Notification ke ADMIN (business event: teknisi mencatat instalasi).
        try {
            $actorName = AuthHelper::user()['name'];
            \App\Helpers\NotificationHelper::createForRoleOnce(
                'ADMIN',
                'Instalasi Baru Dicatat',
                $actorName . ' mencatat instalasi ' . $transaction_code . ' untuk ' . ($customer_name ?: '-') . '.',
                'info',
                '/installations/show?id=' . $installation_id
            );
        } catch (\Exception $notifEx) {
            error_log('[BIGNET] Notification gagal (installation create #' . $installation_id . '): ' . $notifEx->getMessage());
        }

        header("Location: " . url('/installations'));
        exit;
    }

    public function myItems() {
        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();
        
        $sql = "SELECT ii.*, i.transaction_code, i.customer_site, i.installed_at, m.kode_barang, m.nama as item_name 
                FROM installation_items ii 
                JOIN installations i ON ii.installation_id = i.id 
                JOIN master_items m ON ii.master_item_id = m.id ";
                
        if (!AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])) {
            $sql .= " WHERE i.technician_id = " . (int)$user['id'];
        }
        $sql .= " ORDER BY i.installed_at DESC";
        
        $stmt = $pdo->query($sql);
        $items = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/installations/my_items.php';
    }

    public function photos() {
        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();
        
        $sql = "SELECT id, transaction_code, customer_site, photo_path, installed_at, notes 
                FROM installations 
                WHERE photo_path IS NOT NULL ";
                
        if (!AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])) {
            $sql .= " AND technician_id = " . (int)$user['id'];
        }
        $sql .= " ORDER BY installed_at DESC";
        
        $stmt = $pdo->query($sql);
        $photos = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/installations/photos.php';
    }
}
