<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class WarehouseController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClauses = [];
        $params = [];

        if ($search !== '') {
            $whereClauses[] = "(code LIKE ? OR name LIKE ? OR address LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status !== '') {
            $whereClauses[] = "is_active = ?";
            $params[] = $status;
        }

        $whereSql = '';
        if (count($whereClauses) > 0) {
            $whereSql = "WHERE " . implode(" AND ", $whereClauses);
        }

        $countSql = "SELECT COUNT(*) FROM warehouses $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "SELECT * FROM warehouses $whereSql ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $warehouses = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/inventory/warehouses/index.php';
    }

    public function store() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/warehouses'));
            exit;
        }

        // Kode gudang AUTO-GENERATED oleh sistem (GDG-xxxxxx) — user tidak mengisi kode.
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $_SESSION['error'] = "Nama gudang tidak boleh kosong.";
            header("Location: " . url('/warehouses'));
            exit;
        }

        if (strlen($name) > 100) {
            $_SESSION['error'] = "Nama gudang maksimal 100 karakter.";
            header("Location: " . url('/warehouses'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();

        $maxRetries = 5;
        $attempt = 0;
        $success = false;

        while ($attempt < $maxRetries && !$success) {
            try {
                $pdo->beginTransaction();

                $stmtMax = $pdo->query("SELECT code FROM warehouses WHERE code LIKE 'GDG-%' ORDER BY id DESC LIMIT 1 FOR UPDATE");
                $lastCode = $stmtMax->fetchColumn();

                if ($lastCode) {
                    $lastNum = (int)str_replace('GDG-', '', $lastCode);
                    $newNum = str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);
                } else {
                    $newNum = '000001';
                }
                $code = 'GDG-' . $newNum;

                $stmt = $pdo->prepare("INSERT INTO warehouses (code, name, address, description, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$code, $name, $address, $description, $is_active]);

                // Audit Log
                $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'warehouses', ?)");
                $auditStmt->execute([AuthHelper::user()['id'], $pdo->lastInsertId()]);

                $pdo->commit();
                $success = true;
            } catch (\PDOException $e) {
                $pdo->rollBack();
                // 1062 = duplicate entry (collision kode) — coba kode berikutnya.
                if ($e->errorInfo[1] == 1062) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        $_SESSION['error'] = "Gagal generate kode gudang setelah beberapa kali percobaan. Silakan coba lagi.";
                        header("Location: " . url('/warehouses'));
                        exit;
                    }
                } else {
                    $_SESSION['error'] = "Gagal membuat gudang.";
                    header("Location: " . url('/warehouses'));
                    exit;
                }
            }
        }

        header("Location: " . url('/warehouses'));
        exit;
    }

    public function bulkDelete() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/warehouses'));
            exit;
        }

        $rawIds = $_POST['ids'] ?? [];
        $ids = array_values(array_unique(array_filter(array_map('intval', is_array($rawIds) ? $rawIds : explode(',', (string)$rawIds)))));
        if (empty($ids)) {
            $_SESSION['error'] = "Tidak ada data yang dipilih untuk dihapus.";
            header("Location: " . url('/warehouses'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Gudang yang masih direferensikan TIDAK boleh dihapus (integritas banyak modul).
        $stmtUsed = $pdo->prepare("
            SELECT id FROM warehouses WHERE id IN ($placeholders) AND (
                EXISTS (SELECT 1 FROM locations WHERE warehouse_id = warehouses.id)
                OR EXISTS (SELECT 1 FROM stocks WHERE warehouse_id = warehouses.id)
                OR EXISTS (SELECT 1 FROM serial_numbers WHERE current_warehouse_id = warehouses.id)
                OR EXISTS (SELECT 1 FROM incoming WHERE warehouse_id = warehouses.id)
                OR EXISTS (SELECT 1 FROM outgoing WHERE warehouse_id = warehouses.id)
                OR EXISTS (SELECT 1 FROM transfers WHERE source_warehouse_id = warehouses.id OR destination_warehouse_id = warehouses.id)
                OR EXISTS (SELECT 1 FROM stock_ledgers WHERE warehouse_id = warehouses.id)
                OR EXISTS (SELECT 1 FROM stock_adjustments WHERE warehouse_id = warehouses.id)
                OR EXISTS (SELECT 1 FROM stock_opnames WHERE warehouse_id = warehouses.id)
                OR EXISTS (SELECT 1 FROM incidents WHERE warehouse_id = warehouses.id)
            )
        ");
        $stmtUsed->execute($ids);
        $usedIds = array_map('intval', $stmtUsed->fetchAll(PDO::FETCH_COLUMN));
        $safeIds = array_values(array_diff($ids, $usedIds));

        $deleted = 0;
        if (!empty($safeIds)) {
            $ph = implode(',', array_fill(0, count($safeIds), '?'));
            try {
                $pdo->beginTransaction();
                $del = $pdo->prepare("DELETE FROM warehouses WHERE id IN ($ph)");
                $del->execute($safeIds);
                foreach ($safeIds as $wid) {
                    $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'DELETE', 'warehouses', ?)");
                    $auditStmt->execute([AuthHelper::user()['id'], $wid]);
                }
                $pdo->commit();
                $deleted = count($safeIds);
            } catch (\PDOException $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Gagal menghapus data. Silakan coba lagi.";
                header("Location: " . url('/warehouses'));
                exit;
            }
        }

        $msg = "$deleted data berhasil dihapus.";
        if (!empty($usedIds)) {
            $msg .= " " . count($usedIds) . " data dilewati karena masih memiliki stok/transaksi/lokasi terkait.";
        }
        $_SESSION['success'] = $msg;
        header("Location: " . url('/warehouses'));
        exit;
    }

    public function edit($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM warehouses WHERE id = ?");
        $stmt->execute([$id]);
        $warehouse = $stmt->fetch();
        
        if (!$warehouse) {
            $_SESSION['error'] = "Warehouse not found.";
            header("Location: " . url('/warehouses'));
            exit;
        }

        require __DIR__ . '/../../resources/views/inventory/warehouses/edit.php';
    }

    public function update($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/warehouses'));
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $_SESSION['error'] = "Nama gudang tidak boleh kosong.";
            header("Location: " . url('/warehouses'));
            exit;
        }

        if (strlen($name) > 100) {
            $_SESSION['error'] = "Nama gudang maksimal 100 karakter.";
            header("Location: " . url('/warehouses'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("SELECT is_active FROM warehouses WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $oldWh = $stmt->fetch();
            
            if ($oldWh && (int)$oldWh['is_active'] === 1 && $is_active === 0) {
                // Check for stock
                $hasStock = $pdo->query("SELECT COUNT(*) FROM stocks WHERE warehouse_id = $id AND quantity > 0")->fetchColumn();
                // Check for available serials
                $hasSerials = $pdo->query("SELECT COUNT(*) FROM serial_numbers WHERE current_warehouse_id = $id AND status = 'AVAILABLE'")->fetchColumn();
                
                if ($hasStock > 0 || $hasSerials > 0) {
                    throw new \Exception("Tidak dapat menonaktifkan gudang ini karena masih memiliki stok atau serial aktif di dalamnya.");
                }
            }

            $stmt = $pdo->prepare("UPDATE warehouses SET name = ?, address = ?, description = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $address, $description, $is_active, $id]);
            
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'UPDATE', 'warehouses', ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $id]);
            
            $_SESSION['success'] = "Warehouse updated successfully.";
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: " . url('/warehouses'));
        exit;
    }
}
