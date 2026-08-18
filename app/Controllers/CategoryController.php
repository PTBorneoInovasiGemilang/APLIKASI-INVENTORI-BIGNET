<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class CategoryController {
    
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
        $statTotal = $pdo->query("SELECT COUNT(*) FROM item_categories")->fetchColumn();
        $statActive = $pdo->query("SELECT COUNT(*) FROM item_categories WHERE is_active = 1")->fetchColumn();
        $statInactive = $pdo->query("SELECT COUNT(*) FROM item_categories WHERE is_active = 0")->fetchColumn();

        $countSql = "SELECT COUNT(*) FROM item_categories $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "
            SELECT ic.*, 
                   (SELECT COUNT(id) FROM master_items WHERE item_category_id = ic.id) as used_count
            FROM item_categories ic 
            $whereSql 
            ORDER BY ic.id DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/inventory/categories/index.php';
    }

    public function store() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/categories'));
            exit;
        }

        // Code auto-generated
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $_SESSION['error'] = "Nama kategori tidak boleh kosong.";
            header("Location: " . url('/categories'));
            exit;
        }

        if (strlen($name) > 100) {
            $_SESSION['error'] = "Nama kategori tidak boleh lebih dari 100 karakter.";
            header("Location: " . url('/categories'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        $maxRetries = 5;
        $attempt = 0;
        $success = false;
        
        while ($attempt < $maxRetries && !$success) {
            try {
                $pdo->beginTransaction();
                
                $stmtMax = $pdo->query("SELECT code FROM item_categories WHERE code LIKE 'KAT-%' ORDER BY id DESC LIMIT 1 FOR UPDATE");
                $lastCode = $stmtMax->fetchColumn();
                
                if ($lastCode) {
                    $lastNum = (int)str_replace('KAT-', '', $lastCode);
                    $newNum = str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);
                } else {
                    $newNum = '000001';
                }
                $code = 'KAT-' . $newNum;

                $stmt = $pdo->prepare("INSERT INTO item_categories (code, name, description, is_active) VALUES (?, ?, ?, ?)");
                $stmt->execute([$code, $name, $description, $is_active]);
                
                // Audit Log
                $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'item_categories', ?)");
                $auditStmt->execute([AuthHelper::user()['id'], $pdo->lastInsertId()]);

                $pdo->commit();
                $success = true;
            } catch (\PDOException $e) {
                $pdo->rollBack();
                // 1062 is MySQL duplicate entry error code
                if ($e->errorInfo[1] == 1062) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        $_SESSION['error'] = "Gagal generate kode kategori setelah beberapa kali percobaan. Silakan coba lagi.";
                        header("Location: " . url('/categories'));
                        exit;
                    }
                } else {
                    $_SESSION['error'] = "Terjadi kesalahan sistem saat menyimpan kategori.";
                    header("Location: " . url('/categories'));
                    exit;
                }
            }
        }

        header("Location: " . url('/categories'));
        exit;
    }

    public function bulkDelete() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/categories'));
            exit;
        }

        $rawIds = $_POST['ids'] ?? [];
        $ids = array_values(array_unique(array_filter(array_map('intval', is_array($rawIds) ? $rawIds : explode(',', (string)$rawIds)))));
        if (empty($ids)) {
            $_SESSION['error'] = "Tidak ada data yang dipilih untuk dihapus.";
            header("Location: " . url('/categories'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Kategori yang masih dipakai item TIDAK boleh dihapus (audit/integrity).
        $stmtUsed = $pdo->prepare("SELECT id FROM item_categories WHERE id IN ($placeholders) AND EXISTS (SELECT 1 FROM master_items WHERE item_category_id = item_categories.id)");
        $stmtUsed->execute($ids);
        $usedIds = array_map('intval', $stmtUsed->fetchAll(PDO::FETCH_COLUMN));
        $safeIds = array_values(array_diff($ids, $usedIds));

        $deleted = 0;
        if (!empty($safeIds)) {
            $ph = implode(',', array_fill(0, count($safeIds), '?'));
            try {
                $pdo->beginTransaction();
                $del = $pdo->prepare("DELETE FROM item_categories WHERE id IN ($ph)");
                $del->execute($safeIds);
                foreach ($safeIds as $cid) {
                    $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'DELETE', 'item_categories', ?)");
                    $auditStmt->execute([AuthHelper::user()['id'], $cid]);
                }
                $pdo->commit();
                $deleted = count($safeIds);
            } catch (\PDOException $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Gagal menghapus data. Silakan coba lagi.";
                header("Location: " . url('/categories'));
                exit;
            }
        }

        $msg = "$deleted data berhasil dihapus.";
        if (!empty($usedIds)) {
            $msg .= " " . count($usedIds) . " data dilewati karena masih digunakan oleh master barang.";
        }
        $_SESSION['success'] = $msg;
        header("Location: " . url('/categories'));
        exit;
    }

    public function edit($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM item_categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        
        if (!$category) {
            $_SESSION['error'] = "Category not found.";
            header("Location: " . url('/categories'));
            exit;
        }

        require __DIR__ . '/../../resources/views/inventory/categories/edit.php';
    }

    public function update($id) {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF token validation failed.";
            header("Location: " . url('/categories'));
            exit;
        }

        // code cannot be updated
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $_SESSION['error'] = "Nama kategori tidak boleh kosong.";
            header("Location: " . url('/categories'));
            exit;
        }

        if (strlen($name) > 100) {
            $_SESSION['error'] = "Nama kategori tidak boleh lebih dari 100 karakter.";
            header("Location: " . url('/categories'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        try {
            $stmt = $pdo->prepare("UPDATE item_categories SET name = ?, description = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $description, $is_active, $id]);
            
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'UPDATE', 'item_categories', ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $id]);
            
            $_SESSION['success'] = "Category updated successfully.";
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Failed to update category.";
        }

        header("Location: " . url('/categories'));
        exit;
    }
}
