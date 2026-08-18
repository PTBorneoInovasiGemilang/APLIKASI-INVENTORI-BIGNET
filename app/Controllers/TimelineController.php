<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class TimelineController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN', 'TEKNISI_NOC']);
    }

    public function index() {
        $type = $_GET['type'] ?? '';
        $ref_id = $_GET['ref_id'] ?? '';
        
        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();
        
        // LEVEL 2: Detail Perjalanan Barang
        if (!empty($type) && !empty($ref_id)) {
            $item_detail = null;
            $timeline = [];
            
            if ($type === 'SN') {
                $sqlSnItem = "SELECT sn.id, sn.serial_number as identifier, m.nama as item_name, m.kode_barang, w.name as location, u.name as holder, sn.status, 1 as quantity 
                        FROM serial_numbers sn 
                        JOIN master_items m ON sn.master_item_id = m.id 
                        LEFT JOIN warehouses w ON sn.current_warehouse_id = w.id 
                        LEFT JOIN users u ON sn.current_holder_id = u.id 
                        WHERE sn.id = ?";
                $params = [$ref_id];
                
                if ($user['role'] === 'TEKNISI_NOC') {
                    $sqlSnItem .= " AND (sn.current_holder_id = ? OR EXISTS (
                        SELECT 1 FROM serial_movements sm2 WHERE sm2.serial_number_id = sn.id 
                        AND (sm2.performed_by = ? OR sm2.from_holder_id = ? OR sm2.to_holder_id = ?)
                    ))";
                    array_push($params, $user['id'], $user['id'], $user['id'], $user['id']);
                }
                
                $stmt = $pdo->prepare($sqlSnItem);
                $stmt->execute($params);
                $item_detail = $stmt->fetch();
                
                if ($item_detail) {
                    $query = "SELECT sm.*, 
                              wf.name as from_warehouse, wt.name as to_warehouse, 
                              hf.name as from_holder, ht.name as to_holder,
                              u.name as user_name,
                              sn.serial_number, m.nama as item_name 
                              FROM serial_movements sm 
                              JOIN serial_numbers sn ON sm.serial_number_id = sn.id
                              JOIN master_items m ON sn.master_item_id = m.id
                              LEFT JOIN warehouses wf ON sm.from_warehouse_id = wf.id 
                              LEFT JOIN warehouses wt ON sm.to_warehouse_id = wt.id 
                              LEFT JOIN users hf ON sm.from_holder_id = hf.id 
                              LEFT JOIN users ht ON sm.to_holder_id = ht.id 
                              LEFT JOIN users u ON sm.performed_by = u.id 
                              WHERE sm.serial_number_id = ?
                              ORDER BY sm.created_at ASC";
                    $mStmt = $pdo->prepare($query);
                    $mStmt->execute([$ref_id]);
                    $timeline = $mStmt->fetchAll();
                }
            } else if ($type === 'BULK') {
                $parts = explode('-', $ref_id);
                if (count($parts) === 2) {
                    $master_item_id = (int)$parts[0];
                    $warehouse_id = (int)$parts[1];
                    
                    $sqlBulkItem = "SELECT CONCAT(s.master_item_id, '-', s.warehouse_id) as id, m.kode_barang as identifier, m.nama as item_name, m.kode_barang, w.name as location, NULL as holder, 'DI GUDANG' as status, s.quantity
                        FROM stocks s
                        JOIN master_items m ON s.master_item_id = m.id
                        JOIN warehouses w ON s.warehouse_id = w.id
                        WHERE s.master_item_id = ? AND s.warehouse_id = ?";
                    $params = [$master_item_id, $warehouse_id];
                    
                    if ($user['role'] === 'TEKNISI_NOC') {
                        $sqlBulkItem .= " AND EXISTS (SELECT 1 FROM stock_ledgers sl WHERE sl.master_item_id = s.master_item_id AND sl.warehouse_id = s.warehouse_id AND sl.performed_by = ?)";
                        $params[] = $user['id'];
                    }
                    
                    $stmt = $pdo->prepare($sqlBulkItem);
                    $stmt->execute($params);
                    $item_detail = $stmt->fetch();
                    
                    if ($item_detail) {
                        $query = "SELECT sl.*, u.name as user_name
                                  FROM stock_ledgers sl
                                  LEFT JOIN users u ON sl.performed_by = u.id
                                  WHERE sl.master_item_id = ? AND sl.warehouse_id = ?
                                  ORDER BY sl.created_at ASC";
                        $mStmt = $pdo->prepare($query);
                        $mStmt->execute([$master_item_id, $warehouse_id]);
                        $timeline = $mStmt->fetchAll();
                    }
                }
            }
            
            require __DIR__ . '/../../resources/views/inventory/timeline/show.php';
            return;
        }

        // LEVEL 1: Daftar Barang (Search)
        $search = trim($_GET['search'] ?? '');
        
        $results = [];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        // Build query serial tanpa LIMIT (untuk hitung total + pagination)
        $sqlSn = "SELECT 'SN' as type, sn.id as ref_id, sn.serial_number as identifier, m.nama as item_name, m.kode_barang, w.name as location, u.name as holder, sn.status, 1 as quantity 
                  FROM serial_numbers sn 
                  JOIN master_items m ON sn.master_item_id = m.id 
                  LEFT JOIN warehouses w ON sn.current_warehouse_id = w.id 
                  LEFT JOIN users u ON sn.current_holder_id = u.id 
                  WHERE 1=1";
        $paramsSn = [];
        
        if (!empty($search)) {
            $sqlSn .= " AND (sn.serial_number LIKE ? OR m.nama LIKE ? OR m.kode_barang LIKE ?)";
            array_push($paramsSn, "%$search%", "%$search%", "%$search%");
        }
        
        if ($user['role'] === 'TEKNISI_NOC') {
            $sqlSn .= " AND (sn.current_holder_id = ? OR EXISTS (
                SELECT 1 FROM serial_movements sm2 WHERE sm2.serial_number_id = sn.id 
                AND (sm2.performed_by = ? OR sm2.from_holder_id = ? OR sm2.to_holder_id = ?)
            ))";
            array_push($paramsSn, $user['id'], $user['id'], $user['id'], $user['id']);
        }
        
        // Build query bulk tanpa LIMIT
        if ($user['role'] !== 'TEKNISI_NOC') {
            $sqlBulk = "SELECT 'BULK' as type, CONCAT(s.master_item_id, '-', s.warehouse_id) as ref_id, m.kode_barang as identifier, m.nama as item_name, m.kode_barang, w.name as location, NULL as holder, 'DI GUDANG' as status, s.quantity 
                        FROM stocks s 
                        JOIN master_items m ON s.master_item_id = m.id 
                        JOIN warehouses w ON s.warehouse_id = w.id 
                        WHERE m.is_serialized = 0";
            $paramsBulk = [];

            if (!empty($search)) {
                $sqlBulk .= " AND (m.nama LIKE ? OR m.kode_barang LIKE ?)";
                array_push($paramsBulk, "%$search%", "%$search%");
            }
        } else {
            // Teknisi: only bulk they have touched (stock ledgers)
            $sqlBulk = "SELECT DISTINCT 'BULK' as type, CONCAT(s.master_item_id, '-', s.warehouse_id) as ref_id, m.kode_barang as identifier, m.nama as item_name, m.kode_barang, w.name as location, NULL as holder, 'DI GUDANG' as status, s.quantity 
                        FROM stocks s 
                        JOIN master_items m ON s.master_item_id = m.id 
                        JOIN warehouses w ON s.warehouse_id = w.id 
                        JOIN stock_ledgers sl ON sl.master_item_id = s.master_item_id AND sl.warehouse_id = s.warehouse_id
                        WHERE m.is_serialized = 0 AND sl.performed_by = ?";
            $paramsBulk = [$user['id']];
            
            if (!empty($search)) {
                $sqlBulk .= " AND (m.nama LIKE ? OR m.kode_barang LIKE ?)";
                array_push($paramsBulk, "%$search%", "%$search%");
            }
        }
        
        // Count total (SN + Bulk)
        $countSnSql = "SELECT COUNT(*) FROM ({$sqlSn}) AS sn_rows";
        $stmtCountSn = $pdo->prepare($countSnSql);
        $stmtCountSn->execute($paramsSn);
        $totalSn = (int)$stmtCountSn->fetchColumn();
        
        $countBulkSql = "SELECT COUNT(*) FROM ({$sqlBulk}) AS bulk_rows";
        $stmtCountBulk = $pdo->prepare($countBulkSql);
        $stmtCountBulk->execute($paramsBulk);
        $totalBulk = (int)$stmtCountBulk->fetchColumn();
        
        $totalItems = $totalSn + $totalBulk;
        $totalPages = max(1, (int)ceil($totalItems / $limit));
        
        // Fetch dengan LIMIT/OFFSET
        $sqlSn .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        $stmtSn = $pdo->prepare($sqlSn);
        $stmtSn->execute($paramsSn);
        $snResults = $stmtSn->fetchAll();
        
        $sqlBulk .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        $stmtBulk = $pdo->prepare($sqlBulk);
        $stmtBulk->execute($paramsBulk);
        $bulkResults = $stmtBulk->fetchAll();
        
        $results = array_merge($snResults, $bulkResults);
        
        // Sort by item name
        usort($results, function($a, $b) {
            return strcmp($a['item_name'], $b['item_name']);
        });
        
        require __DIR__ . '/../../resources/views/inventory/timeline/index.php';
    }
}
