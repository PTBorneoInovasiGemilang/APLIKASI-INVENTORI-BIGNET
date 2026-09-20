<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class UnitController {
    
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
            $whereClauses[] = "(code LIKE ? OR name LIKE ?)";
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

        // Stats
        $statTotal = $pdo->query("SELECT COUNT(*) FROM units")->fetchColumn();
        $statActive = $pdo->query("SELECT COUNT(*) FROM units WHERE is_active = 1")->fetchColumn();
        $statInactive = $pdo->query("SELECT COUNT(*) FROM units WHERE is_active = 0")->fetchColumn();

        $countSql = "SELECT COUNT(*) FROM units $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "
            SELECT u.*, 
                   (SELECT COUNT(id) FROM master_items WHERE unit_id = u.id) as used_count
            FROM units u 
            $whereSql 
            ORDER BY u.id DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $units = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/inventory/units/index.php';
    }

    public function store() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/units'));
            exit;
        }

        // Kode satuan AUTO-GENERATED oleh sistem (UNT-xxxxxx) — user tidak mengisi kode.
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $_SESSION['error'] = "Nama satuan tidak boleh kosong.";
            header("Location: " . url('/units'));
            exit;
        }

        if (strlen($name) > 100) {
            $_SESSION['error'] = "Nama satuan maksimal 100 karakter.";
            header("Location: " . url('/units'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();

        $maxRetries = 5;
        $attempt = 0;
        $success = false;

        while ($attempt < $maxRetries && !$success) {
            try {
                $pdo->beginTransaction();

                $stmtMax = $pdo->query("SELECT code FROM units WHERE code LIKE 'UNT-%' ORDER BY id DESC LIMIT 1 FOR UPDATE");
                $lastCode = $stmtMax->fetchColumn();

                if ($lastCode) {
                    $lastNum = (int)str_replace('UNT-', '', $lastCode);
                    $newNum = str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);
                } else {
                    $newNum = '000001';
                }
                $code = 'UNT-' . $newNum;

                $stmt = $pdo->prepare("INSERT INTO units (code, name, description, is_active) VALUES (?, ?, ?, ?)");
                $stmt->execute([$code, $name, $description, $is_active]);

                // Audit Log
                $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'units', ?)");
                $auditStmt->execute([AuthHelper::user()['id'], $pdo->lastInsertId()]);

                $pdo->commit();
                $success = true;
            } catch (\PDOException $e) {
                $pdo->rollBack();
                // 1062 = duplicate entry (collision kode) — coba kode berikutnya.
                if ($e->errorInfo[1] == 1062) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        $_SESSION['error'] = "Gagal generate kode satuan setelah beberapa kali percobaan. Silakan coba lagi.";
                        header("Location: " . url('/units'));
                        exit;
                    }
                } else {
                    $_SESSION['error'] = "Gagal membuat satuan.";
                    header("Location: " . url('/units'));
                    exit;
                }
            }
        }

        header("Location: " . url('/units'));
        exit;
    }

    public function bulkDelete() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/units'));
            exit;
        }

        $rawIds = $_POST['ids'] ?? [];
        $ids = array_values(array_unique(array_filter(array_map('intval', is_array($rawIds) ? $rawIds : explode(',', (string)$rawIds)))));
        if (empty($ids)) {
            $_SESSION['error'] = "Tidak ada data yang dipilih untuk dihapus.";
            header("Location: " . url('/units'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Satuan yang masih dipakai item TIDAK boleh dihapus.
        $stmtUsed = $pdo->prepare("SELECT id FROM units WHERE id IN ($placeholders) AND EXISTS (SELECT 1 FROM master_items WHERE unit_id = units.id)");
        $stmtUsed->execute($ids);
        $usedIds = array_map('intval', $stmtUsed->fetchAll(PDO::FETCH_COLUMN));
        $safeIds = array_values(array_diff($ids, $usedIds));

        $deleted = 0;
        if (!empty($safeIds)) {
            $ph = implode(',', array_fill(0, count($safeIds), '?'));
            try {
                $pdo->beginTransaction();
                $del = $pdo->prepare("DELETE FROM units WHERE id IN ($ph)");
                $del->execute($safeIds);
                foreach ($safeIds as $uid) {
                    $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'DELETE', 'units', ?)");
                    $auditStmt->execute([AuthHelper::user()['id'], $uid]);
                }
                $pdo->commit();
                $deleted = count($safeIds);
            } catch (\PDOException $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Gagal menghapus data. Silakan coba lagi.";
                header("Location: " . url('/units'));
                exit;
            }
        }

        $msg = "$deleted data berhasil dihapus.";
        if (!empty($usedIds)) {
            $msg .= " " . count($usedIds) . " data dilewati karena masih digunakan oleh master barang.";
        }
        $_SESSION['success'] = $msg;
        header("Location: " . url('/units'));
        exit;
    }

    public function edit($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM units WHERE id = ?");
        $stmt->execute([$id]);
        $unit = $stmt->fetch();
        
        if (!$unit) {
            $_SESSION['error'] = "Unit not found.";
            header("Location: " . url('/units'));
            exit;
        }

        require __DIR__ . '/../../resources/views/inventory/units/edit.php';
    }

    public function update($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/units'));
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $_SESSION['error'] = "Nama satuan tidak boleh kosong.";
            header("Location: " . url('/units'));
            exit;
        }

        if (strlen($name) > 100) {
            $_SESSION['error'] = "Nama satuan maksimal 100 karakter.";
            header("Location: " . url('/units'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        try {
            $stmt = $pdo->prepare("UPDATE units SET name = ?, description = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $description, $is_active, $id]);
            
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'UPDATE', 'units', ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $id]);
            
            $_SESSION['success'] = "Unit updated successfully.";
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Failed to update unit.";
        }

        header("Location: " . url('/units'));
        exit;
    }

    public function delete($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/units'));
            exit;
        }

        $id = (int)$id;
        if (!$id) {
            $_SESSION['error'] = "Invalid unit ID.";
            header("Location: " . url('/units'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        // Satuan yang masih dipakai item TIDAK boleh dihapus
        $stmtUsed = $pdo->prepare("SELECT COUNT(*) FROM master_items WHERE unit_id = ?");
        $stmtUsed->execute([$id]);
        if ($stmtUsed->fetchColumn() > 0) {
            $_SESSION['error'] = "Satuan tidak dapat dihapus karena masih digunakan oleh master barang.";
            header("Location: " . url('/units'));
            exit;
        }

        try {
            $pdo->beginTransaction();
            $del = $pdo->prepare("DELETE FROM units WHERE id = ?");
            $del->execute([$id]);
            
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'DELETE', 'units', ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $id]);
            
            $pdo->commit();
            $_SESSION['success'] = "Satuan berhasil dihapus.";
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal menghapus data. Silakan coba lagi.";
        }

        header("Location: " . url('/units'));
        exit;
    }
}
