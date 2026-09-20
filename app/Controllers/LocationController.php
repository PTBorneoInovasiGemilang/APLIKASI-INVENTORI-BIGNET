<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class LocationController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        
        $warehouseId = isset($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : 0;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClauses = [];
        $params = [];

        if ($warehouseId > 0) {
            $whereClauses[] = "l.warehouse_id = ?";
            $params[] = $warehouseId;
        }

        if ($search !== '') {
            $whereClauses[] = "(l.code LIKE ? OR l.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status !== '') {
            $whereClauses[] = "l.is_active = ?";
            $params[] = $status;
        }

        $whereSql = '';
        if (count($whereClauses) > 0) {
            $whereSql = "WHERE " . implode(" AND ", $whereClauses);
        }

        $countSql = "SELECT COUNT(*) FROM locations l $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "SELECT l.*, w.name as warehouse_name,
                       (EXISTS (SELECT 1 FROM serial_numbers WHERE current_location_id = l.id)) as is_used
                FROM locations l 
                JOIN warehouses w ON l.warehouse_id = w.id 
                $whereSql
                ORDER BY l.id DESC
                LIMIT $limit OFFSET $offset";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $locations = $stmt->fetchAll();

        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();

        require __DIR__ . '/../../resources/views/inventory/locations/index.php';
    }

    public function store() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/locations'));
            exit;
        }

        $warehouse_id = $_POST['warehouse_id'] ?? null;
        // Kode lokasi AUTO-GENERATED oleh sistem (LOK-xxxxxx, global unique) — user tidak mengisi kode.
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($warehouse_id) || $name === '') {
            $_SESSION['error'] = "Gudang dan nama lokasi wajib diisi.";
            header("Location: " . url('/locations'));
            exit;
        }

        if (strlen($name) > 100) {
            $_SESSION['error'] = "Nama lokasi maksimal 100 karakter.";
            header("Location: " . url('/locations'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();

        $stmtWh = $pdo->prepare("SELECT id FROM warehouses WHERE id = ? AND is_active = 1");
        $stmtWh->execute([$warehouse_id]);
        if (!$stmtWh->fetch()) {
            $_SESSION['error'] = "Gudang tidak valid atau tidak aktif.";
            header("Location: " . url('/locations'));
            exit;
        }

        $maxRetries = 5;
        $attempt = 0;
        $success = false;

        while ($attempt < $maxRetries && !$success) {
            try {
                $pdo->beginTransaction();

                $stmtMax = $pdo->query("SELECT code FROM locations WHERE code LIKE 'LOK-%' ORDER BY id DESC LIMIT 1 FOR UPDATE");
                $lastCode = $stmtMax->fetchColumn();

                if ($lastCode) {
                    $lastNum = (int)str_replace('LOK-', '', $lastCode);
                    $newNum = str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);
                } else {
                    $newNum = '000001';
                }
                $code = 'LOK-' . $newNum;

                $stmt = $pdo->prepare("INSERT INTO locations (warehouse_id, code, name, description, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$warehouse_id, $code, $name, $description, $is_active]);

                // Audit Log
                $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'locations', ?)");
                $auditStmt->execute([AuthHelper::user()['id'], $pdo->lastInsertId()]);

                $pdo->commit();
                $success = true;
            } catch (\PDOException $e) {
                $pdo->rollBack();
                // 1062 = duplicate entry (collision kode) — coba kode berikutnya.
                if ($e->errorInfo[1] == 1062) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        $_SESSION['error'] = "Gagal generate kode lokasi setelah beberapa kali percobaan. Silakan coba lagi.";
                        header("Location: " . url('/locations'));
                        exit;
                    }
                } else {
                    $_SESSION['error'] = "Gagal membuat lokasi.";
                    header("Location: " . url('/locations'));
                    exit;
                }
            }
        }

        header("Location: " . url('/locations' . ($warehouse_id ? "?warehouse_id=$warehouse_id" : "")));
        exit;
    }

    public function bulkDelete() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/locations'));
            exit;
        }

        $rawIds = $_POST['ids'] ?? [];
        $ids = array_values(array_unique(array_filter(array_map('intval', is_array($rawIds) ? $rawIds : explode(',', (string)$rawIds)))));
        if (empty($ids)) {
            $_SESSION['error'] = "Tidak ada data yang dipilih untuk dihapus.";
            header("Location: " . url('/locations'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Lokasi yang masih dipakai nomor seri TIDAK boleh dihapus.
        $stmtUsed = $pdo->prepare("SELECT id FROM locations WHERE id IN ($placeholders) AND EXISTS (SELECT 1 FROM serial_numbers WHERE current_location_id = locations.id)");
        $stmtUsed->execute($ids);
        $usedIds = array_map('intval', $stmtUsed->fetchAll(PDO::FETCH_COLUMN));
        $safeIds = array_values(array_diff($ids, $usedIds));

        $deleted = 0;
        if (!empty($safeIds)) {
            $ph = implode(',', array_fill(0, count($safeIds), '?'));
            try {
                $pdo->beginTransaction();
                $del = $pdo->prepare("DELETE FROM locations WHERE id IN ($ph)");
                $del->execute($safeIds);
                foreach ($safeIds as $lid) {
                    $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'DELETE', 'locations', ?)");
                    $auditStmt->execute([AuthHelper::user()['id'], $lid]);
                }
                $pdo->commit();
                $deleted = count($safeIds);
            } catch (\PDOException $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Gagal menghapus data. Silakan coba lagi.";
                header("Location: " . url('/locations'));
                exit;
            }
        }

        $msg = "$deleted data berhasil dihapus.";
        if (!empty($usedIds)) {
            $msg .= " " . count($usedIds) . " data dilewati karena masih digunakan oleh nomor seri.";
        }
        $_SESSION['success'] = $msg;
        header("Location: " . url('/locations'));
        exit;
    }

    public function edit($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM locations WHERE id = ?");
        $stmt->execute([$id]);
        $location = $stmt->fetch();
        
        if (!$location) {
            $_SESSION['error'] = "Location not found.";
            header("Location: " . url('/locations'));
            exit;
        }

        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();
        require __DIR__ . '/../../resources/views/inventory/locations/edit.php';
    }

    public function update($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/locations'));
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $_SESSION['error'] = "Nama lokasi tidak boleh kosong.";
            header("Location: " . url('/locations'));
            exit;
        }

        if (strlen($name) > 100) {
            $_SESSION['error'] = "Nama lokasi maksimal 100 karakter.";
            header("Location: " . url('/locations'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("SELECT is_active, warehouse_id, code FROM locations WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $oldLoc = $stmt->fetch();
            
            if ($oldLoc && (int)$oldLoc['is_active'] === 1 && $is_active === 0) {
                // Check for available serials in this location
                $hasSerials = $pdo->query("SELECT COUNT(*) FROM serial_numbers WHERE current_location_id = $id AND status = 'AVAILABLE'")->fetchColumn();
                
                if ($hasSerials > 0) {
                    throw new \Exception("Tidak dapat menonaktifkan lokasi ini karena masih memiliki serial aktif di dalamnya.");
                }
            }

            $stmt = $pdo->prepare("UPDATE locations SET name = ?, description = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $description, $is_active, $id]);
            
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'UPDATE', 'locations', ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $id]);
            
            $_SESSION['success'] = "Location updated successfully.";
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: " . url('/locations'));
        exit;
    }

    public function delete($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/locations'));
            exit;
        }

        $id = (int)$id;
        if (!$id) {
            $_SESSION['error'] = "Invalid location ID.";
            header("Location: " . url('/locations'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        // Lokasi yang masih dipakai nomor seri TIDAK boleh dihapus.
        $stmtUsed = $pdo->prepare("SELECT COUNT(*) FROM serial_numbers WHERE current_location_id = ?");
        $stmtUsed->execute([$id]);
        if ($stmtUsed->fetchColumn() > 0) {
            $_SESSION['error'] = "Lokasi tidak dapat dihapus karena masih digunakan oleh nomor seri.";
            header("Location: " . url('/locations'));
            exit;
        }

        try {
            $pdo->beginTransaction();
            $del = $pdo->prepare("DELETE FROM locations WHERE id = ?");
            $del->execute([$id]);
            
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'DELETE', 'locations', ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $id]);
            
            $pdo->commit();
            $_SESSION['success'] = "Lokasi berhasil dihapus.";
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal menghapus data. Silakan coba lagi.";
        }

        header("Location: " . url('/locations'));
        exit;
    }
}
