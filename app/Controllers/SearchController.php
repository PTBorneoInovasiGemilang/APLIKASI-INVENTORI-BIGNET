<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class SearchController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        $q = trim($_GET['q'] ?? '');
        $barcode = trim($_GET['barcode'] ?? ''); // Mode scan barcode (exact match), bukan LIKE
        $type = $_GET['tipe'] ?? '';
        $category_id = $_GET['kategori'] ?? '';
        $warehouse_id = $_GET['gudang'] ?? '';
        $status = $_GET['status'] ?? '';
        $condition = $_GET['kondisi'] ?? '';
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $pdo = Database::getInstance()->getConnection();
        
        // Fetch categories and warehouses for filters
        $categories = $pdo->query("SELECT id, name FROM item_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        $total = 0;

        // If no keyword and no filters, return default list (all items basically, but we limit to recent or just paginate)
        // Wait, "tanpa keyword tetap tampilkan daftar default".
        // Let's build the query dynamically.
        
        $params = [];
        $whereSn = ["1=1"];
        $whereBulk = ["1=1"];

        if ($barcode !== '') {
            // Mode scan barcode: exact match pada serial_number / kode_barang (parameterized).
            // Barcode tidak diperlakukan sebagai kata kunci nama biasa (bukan LIKE).
            $whereSn[] = "(sn.serial_number = ? OR m.kode_barang = ?)";
            $whereBulk[] = "m.kode_barang = ?";
            $params = array_merge($params, [$barcode, $barcode]);
            $paramsBulkQuery = [$barcode];
        } elseif ($q !== '') {
            $search = "%$q%";
            
            // For SN:
            // nama, kode, SN, gudang, lokasi, holder, customer/site, transaction, status, kondisi
            $snSearchSql = "(
                m.nama LIKE ? OR 
                m.kode_barang LIKE ? OR 
                sn.serial_number LIKE ? OR 
                w.name LIKE ? OR 
                holder.name LIKE ? OR 
                sn.status LIKE ? OR 
                sn.condition_status LIKE ? OR
                EXISTS (
                    SELECT 1 FROM installation_items ii 
                    JOIN installations i ON ii.installation_id = i.id 
                    WHERE ii.serial_number_id = sn.id 
                    AND (i.transaction_code LIKE ? OR i.customer_name LIKE ? OR i.customer_site LIKE ?)
                ) OR
                EXISTS (
                    SELECT 1 FROM serial_movements sm
                    JOIN outgoing o ON sm.reference_id = o.id
                    WHERE sm.serial_number_id = sn.id
                    AND sm.reference_type = 'OUTGOING'
                    AND (o.transaction_code LIKE ? OR o.notes LIKE ?)
                ) OR
                EXISTS (
                    SELECT 1 FROM loan_item_serials lis
                    JOIN loan_items li ON lis.loan_item_id = li.id
                    JOIN loans l ON li.loan_id = l.id
                    WHERE lis.serial_number_id = sn.id
                    AND (l.transaction_code LIKE ? OR l.notes LIKE ?)
                )
            )";
            $whereSn[] = $snSearchSql;
            // 14 parameters
            $params = array_merge($params, array_fill(0, 14, $search));
            
            // For Bulk:
            // nama, kode, gudang, lokasi, status, kondisi
            $bulkSearchSql = "(
                m.nama LIKE ? OR 
                m.kode_barang LIKE ? OR 
                w.name LIKE ? OR
                EXISTS (
                    SELECT 1 FROM stock_ledgers sl
                    WHERE sl.master_item_id = m.id AND sl.warehouse_id = s.warehouse_id
                    AND (sl.transaction_type LIKE ? OR sl.notes LIKE ?)
                )
            )";
            $whereBulk[] = $bulkSearchSql;
            // 5 parameters
            $paramsBulkQuery = array_fill(0, 5, $search);
        } else {
            $paramsBulkQuery = [];
        }

        // Apply Filters (hanya untuk pencarian manual; mode scan = exact barcode tanpa filter tambahan)
        if ($barcode === '') {
            if ($category_id !== '') {
                $whereSn[] = "m.item_category_id = ?";
                $whereBulk[] = "m.item_category_id = ?";
                $params[] = $category_id;
                $paramsBulkQuery[] = $category_id;
            }
            if ($warehouse_id !== '') {
                $whereSn[] = "sn.current_warehouse_id = ?";
                $whereBulk[] = "s.warehouse_id = ?";
                $params[] = $warehouse_id;
                $paramsBulkQuery[] = $warehouse_id;
            }
            if ($status !== '') {
                $whereSn[] = "sn.status = ?";
                $whereBulk[] = "'AVAILABLE' = ?"; // Bulk status is pseudo
                $params[] = $status;
                $paramsBulkQuery[] = $status;
            }
            if ($condition !== '') {
                $whereSn[] = "sn.condition_status = ?";
                $whereBulk[] = "'BAIK' = ?"; // Bulk condition is pseudo
                $params[] = $condition;
                $paramsBulkQuery[] = $condition;
            }
        }

        $whereSnStr = implode(' AND ', $whereSn);
        $whereBulkStr = implode(' AND ', $whereBulk);

        $sqlSn = "
            SELECT 
                'SERIALIZED' as type,
                sn.id as ref_id,
                m.id as master_item_id,
                m.kode_barang,
                m.nama as nama_barang,
                sn.serial_number as sn_sku,
                w.name as lokasi,
                holder.name as pemegang,
                sn.status,
                sn.condition_status,
                1 as physical_stock,
                0 as reserved,
                0 as allocated,
                1 as available
            FROM serial_numbers sn
            JOIN master_items m ON sn.master_item_id = m.id
            LEFT JOIN warehouses w ON sn.current_warehouse_id = w.id
            LEFT JOIN users holder ON sn.current_holder_id = holder.id
            WHERE m.is_serialized = 1 AND $whereSnStr
        ";

        $sqlBulk = "
            SELECT 
                'BULK' as type,
                s.warehouse_id as ref_id,
                m.id as master_item_id,
                m.kode_barang,
                m.nama as nama_barang,
                '-' as sn_sku,
                w.name as lokasi,
                '-' as pemegang,
                'AVAILABLE' as status,
                'BAIK' as condition_status,
                s.quantity as physical_stock,
                0 as reserved,
                0 as allocated,
                s.quantity as available
            FROM stocks s
            JOIN master_items m ON s.master_item_id = m.id
            JOIN warehouses w ON s.warehouse_id = w.id
            WHERE m.is_serialized = 0 AND $whereBulkStr
        ";

        $finalParams = [];
        $unionQueries = [];

        if ($type === 'SERIALIZED' || $type === '') {
            $unionQueries[] = $sqlSn;
            $finalParams = array_merge($finalParams, $params);
        }
        if ($type === 'BULK' || $type === '') {
            $unionQueries[] = $sqlBulk;
            $finalParams = array_merge($finalParams, $paramsBulkQuery);
        }

        if (count($unionQueries) > 0) {
            $unionSql = implode(' UNION ALL ', $unionQueries);
            
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM ($unionSql) as t");
            $stmtCount->execute($finalParams);
            $total = $stmtCount->fetchColumn();

            $unionSql .= " ORDER BY nama_barang ASC, sn_sku ASC LIMIT $limit OFFSET $offset";
            $stmt = $pdo->prepare($unionSql);
            $stmt->execute($finalParams);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Live Search JSON endpoint (used by the debounced fetcher on /search)
        if (($_GET['format'] ?? '') === 'json') {
            header('Content-Type: application/json; charset=utf-8');
            $totalPages = max(1, (int)ceil($total / $limit));
            $payload = array_map(function ($r) {
                return [
                    'type' => $r['type'],
                    'ref_id' => (int)$r['ref_id'],
                    'master_item_id' => (int)$r['master_item_id'],
                    'kode_barang' => $r['kode_barang'],
                    'nama_barang' => $r['nama_barang'],
                    'sn_sku' => $r['sn_sku'],
                    'lokasi' => $r['lokasi'],
                    'pemegang' => $r['pemegang'],
                    'status' => $r['status'],
                    'condition_status' => $r['condition_status'],
                    'physical_stock' => (int)$r['physical_stock'],
                    'reserved' => (int)$r['reserved'],
                    'allocated' => (int)$r['allocated'],
                    'available' => (int)$r['available'],
                    'aksi_url' => url('/items/show/' . $r['master_item_id'] . ($r['type'] === 'SERIALIZED' ? '?sn_id=' . $r['ref_id'] : '?warehouse_id=' . $r['ref_id'])),
                ];
            }, $results);
            echo json_encode([
                'total' => (int)$total,
                'page' => $page,
                'totalPages' => $totalPages,
                'results' => $payload,
            ]);
            exit;
        }

        require __DIR__ . '/../../resources/views/search/index.php';
    }
}
