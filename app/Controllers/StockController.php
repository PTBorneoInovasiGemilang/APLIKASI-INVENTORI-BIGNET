<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class StockController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        
        $warehouseId = isset($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : 0;
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $search = $_GET['search'] ?? '';
        $filter = $_GET['filter'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;
        
        $whereClauses = ["1=1"];
        $params = [];

        if ($warehouseId > 0) {
            $whereClauses[] = "s.warehouse_id = ?";
            $params[] = $warehouseId;
        }
        if ($categoryId > 0) {
            $whereClauses[] = "m.item_category_id = ?";
            $params[] = $categoryId;
        }
        if ($filter === 'low_stock') {
            $whereClauses[] = "s.quantity <= m.stok_minimum";
        }
        if ($search !== '') {
            $whereClauses[] = "(m.kode_barang LIKE ? OR m.nama LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        // Count for pagination
        $countSql = "SELECT COUNT(*) FROM stocks s 
                     JOIN master_items m ON s.master_item_id = m.id 
                     JOIN warehouses w ON s.warehouse_id = w.id $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);
        
        // Source of truth angka stok (konsisten dengan SearchController & MasterItemController::show):
        //  - Stok Fisik   = stocks.quantity (bulk) / jumlah serial di gudang (serialized)
        //  - Available    = jumlah serial AVAILABLE di gudang (serialized) / quantity (bulk)
        //  - Reserved     = 0 (placeholder existing project, belum ada transaksi pemesanan)
        //  - Allocated    = 0 (placeholder existing project, belum ada alokasi khusus)
        $sql = "SELECT s.*, m.kode_barang, m.nama as item_name, m.is_serialized, m.stok_minimum, m.is_active,
                       w.name as warehouse_name, c.name as category_name, u.name as unit_name,
                       (SELECT COUNT(*) FROM serial_numbers sn
                         WHERE sn.master_item_id = m.id AND sn.current_warehouse_id = s.warehouse_id
                           AND sn.status = 'AVAILABLE') as available_serials,
                       (SELECT COUNT(*) FROM serial_numbers sn2
                         WHERE sn2.master_item_id = m.id AND sn2.current_warehouse_id = s.warehouse_id) as physical_serials
                FROM stocks s 
                JOIN master_items m ON s.master_item_id = m.id 
                JOIN warehouses w ON s.warehouse_id = w.id 
                LEFT JOIN item_categories c ON m.item_category_id = c.id
                LEFT JOIN units u ON m.unit_id = u.id
                $whereSql
                ORDER BY w.name ASC, m.nama ASC
                LIMIT $limit OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $stocks = $stmt->fetchAll();

        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();
        $categories = $pdo->query("SELECT * FROM item_categories WHERE is_active = 1")->fetchAll();

        require __DIR__ . '/../../resources/views/inventory/stocks/index.php';
    }
}
