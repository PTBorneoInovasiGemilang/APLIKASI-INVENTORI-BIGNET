<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class SerialNumberController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN', 'TEKNISI_NOC']);
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

        // TEKNISI_NOC can only see their own serials
        if ($user['role'] === 'TEKNISI_NOC') {
            $whereClauses[] = "sn.current_holder_id = ?";
            $params[] = (int)$user['id'];
        }

        if ($search !== '') {
            $whereClauses[] = "(sn.serial_number LIKE ? OR m.nama LIKE ? OR m.kode_barang LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status !== '') {
            $whereClauses[] = "sn.status = ?";
            $params[] = $status;
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        // Count for pagination
        $countSql = "SELECT COUNT(*) FROM serial_numbers sn 
                     JOIN master_items m ON sn.master_item_id = m.id 
                     LEFT JOIN warehouses w ON sn.current_warehouse_id = w.id 
                     LEFT JOIN users u ON sn.current_holder_id = u.id 
                     $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);
        
        $sql = "SELECT sn.*, m.kode_barang, m.nama as item_name, w.name as warehouse_name, u.name as holder_name 
                FROM serial_numbers sn 
                JOIN master_items m ON sn.master_item_id = m.id 
                LEFT JOIN warehouses w ON sn.current_warehouse_id = w.id 
                LEFT JOIN users u ON sn.current_holder_id = u.id 
                $whereSql
                ORDER BY sn.id DESC LIMIT $limit OFFSET $offset";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $serials = $stmt->fetchAll();

        require __DIR__ . '/../../resources/views/inventory/serials/index.php';
    }

    // Method timeline() moved to TimelineController
}
