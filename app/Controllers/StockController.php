<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class StockController
{
    public function __construct()
    {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index()
    {
        $pdo = Database::getInstance()->getConnection();
        $warehouseId = isset($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : 0;
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $tracking = $_GET['tracking'] ?? '';
        $filter = $_GET['filter'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $where = ['1=1'];
        $params = [];

        if ($warehouseId > 0) {
            $where[] = 's.warehouse_id = ?';
            $params[] = $warehouseId;
        }
        if ($categoryId > 0) {
            $where[] = 'm.item_category_id = ?';
            $params[] = $categoryId;
        }
        if ($tracking === 'serialized') {
            $where[] = 'm.is_serialized = 1';
        } elseif ($tracking === 'non_serialized') {
            $where[] = 'm.is_serialized = 0';
        }
        if ($search !== '') {
            $where[] = '(m.kode_barang LIKE ? OR m.nama LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $availableExpression = "CASE WHEN m.is_serialized = 1
            THEN (SELECT COUNT(*) FROM serial_numbers sn
                  WHERE sn.master_item_id = m.id
                    AND sn.current_warehouse_id = s.warehouse_id
                    AND sn.status = 'AVAILABLE')
            ELSE s.quantity END";

        $borrowedExpression = "CASE WHEN m.is_serialized = 1
            THEN (SELECT COUNT(*) FROM loan_item_serials lis
                  JOIN loan_items li ON li.id = lis.loan_item_id
                  JOIN loans l ON l.id = li.loan_id
                  WHERE li.master_item_id = m.id
                    AND l.warehouse_id = s.warehouse_id
                    AND l.status IN ('BORROWED','PARTIAL_RETURNED')
                    AND lis.status = 'BORROWED')
            ELSE (SELECT COALESCE(SUM(li.quantity - li.returned_quantity), 0)
                  FROM loan_items li
                  JOIN loans l ON l.id = li.loan_id
                  WHERE li.master_item_id = m.id
                    AND l.warehouse_id = s.warehouse_id
                    AND l.status IN ('BORROWED','PARTIAL_RETURNED')) END";

        $inTransitExpression = "(SELECT COALESCE(SUM(ti.quantity_sent - ti.quantity_received), 0)
            FROM transfer_items ti
            JOIN transfers t ON t.id = ti.transfer_id
            WHERE ti.master_item_id = m.id
              AND t.source_warehouse_id = s.warehouse_id
              AND t.status IN ('IN_TRANSIT','PARTIAL_RECEIVED')
              AND ti.quantity_sent > ti.quantity_received)";

        if ($filter === 'low_stock') {
            $where[] = "($availableExpression <= m.stok_minimum AND m.stok_minimum > 0)";
        } elseif ($filter === 'borrowed') {
            $where[] = "($borrowedExpression > 0)";
        } elseif ($filter === 'in_transit') {
            $where[] = "($inTransitExpression > 0)";
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countSql = "SELECT COUNT(*)
            FROM stocks s
            JOIN master_items m ON s.master_item_id = m.id
            JOIN warehouses w ON s.warehouse_id = w.id
            {$whereSql}";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($totalItems / $limit);

        $sql = "SELECT
                s.*,
                m.id AS master_item_id,
                m.nama AS item_name,
                m.is_serialized,
                m.stok_minimum,
                m.is_active,
                w.name AS warehouse_name,
                c.name AS category_name,
                u.name AS unit_name,
                ($availableExpression) AS available_quantity,
                ($borrowedExpression) AS borrowed_quantity,
                ($inTransitExpression) AS in_transit_quantity
            FROM stocks s
            JOIN master_items m ON s.master_item_id = m.id
            JOIN warehouses w ON s.warehouse_id = w.id
            LEFT JOIN item_categories c ON m.item_category_id = c.id
            LEFT JOIN units u ON m.unit_id = u.id
            {$whereSql}
            ORDER BY w.name ASC, m.nama ASC
            LIMIT {$limit} OFFSET {$offset}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($stocks as &$row) {
            $row['available_quantity'] = max(0, (int)$row['available_quantity']);
            $row['borrowed_quantity'] = max(0, (int)$row['borrowed_quantity']);
            $row['in_transit_quantity'] = max(0, (int)$row['in_transit_quantity']);

            // Stok Fisik = barang yang benar-benar masih berada di gudang.
            // Barang dipinjam dan dalam pengiriman ditampilkan terpisah.
            if ((int)$row['is_serialized'] === 1) {
                $row['physical_quantity'] = (int)$pdo->query(
                    "SELECT COUNT(*) FROM serial_numbers WHERE master_item_id = " . (int)$row['master_item_id'] .
                    " AND current_warehouse_id = " . (int)$row['warehouse_id']
                )->fetchColumn();
            } else {
                $row['physical_quantity'] = max(0, (int)$row['quantity'] - $row['borrowed_quantity']);
                $row['available_quantity'] = $row['physical_quantity'];
            }

            $row['is_low_stock'] = (int)$row['stok_minimum'] > 0
                && $row['physical_quantity'] <= (int)$row['stok_minimum'];
        }
        unset($row);

        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1 ORDER BY name")->fetchAll();
        $categories = $pdo->query("SELECT * FROM item_categories WHERE is_active = 1 ORDER BY name")->fetchAll();

        $stats = [
            'total_items' => $totalItems,
            'low_stock' => (int)$pdo->query("SELECT COUNT(*) FROM stocks s JOIN master_items m ON s.master_item_id = m.id WHERE m.stok_minimum > 0 AND s.quantity <= m.stok_minimum " . ($warehouseId > 0 ? "AND s.warehouse_id = $warehouseId" : ""))->fetchColumn(),
            'active_loans' => (int)$pdo->query("SELECT COUNT(*) FROM loans WHERE status IN ('BORROWED','PARTIAL_RETURNED') " . ($warehouseId > 0 ? "AND warehouse_id = $warehouseId" : ""))->fetchColumn(),
            'in_transit' => (int)$pdo->query("SELECT COUNT(*) FROM transfers WHERE status IN ('IN_TRANSIT','PARTIAL_RECEIVED') " . ($warehouseId > 0 ? "AND source_warehouse_id = $warehouseId" : ""))->fetchColumn(),
        ];

        require __DIR__ . '/../../resources/views/inventory/stocks/index.php';
    }
}
