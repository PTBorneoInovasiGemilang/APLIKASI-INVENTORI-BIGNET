<?php
namespace App\Controllers;

use App\Core\Database;
use App\Helpers\AuthHelper;

class ApiController {

    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN', 'TEKNISI_NOC']);
    }

    public function stock() {
        header('Content-Type: application/json');
        
        $itemId = (int)($_GET['item_id'] ?? 0);
        $warehouseId = (int)($_GET['warehouse_id'] ?? 0);

        if (!$itemId || !$warehouseId) {
            echo json_encode(['error' => 'Missing parameters', 'quantity' => 0]);
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("SELECT is_serialized FROM master_items WHERE id = ?");
        $stmt->execute([$itemId]);
        $isSerialized = (bool)$stmt->fetchColumn();

        // Sum physical stock across all locations in that warehouse
        $stmt = $pdo->prepare("
            SELECT COALESCE(quantity, 0) as total_qty
            FROM stocks
            WHERE master_item_id = ? AND warehouse_id = ?
        ");
        $stmt->execute([$itemId, $warehouseId]);
        $qty = (int)$stmt->fetchColumn();

        $response = ['quantity' => $qty];

        if ($isSerialized) {
            $stmtSn = $pdo->prepare("
                SELECT sn.id, sn.serial_number, l.name as location_name
                FROM serial_numbers sn
                LEFT JOIN locations l ON sn.current_location_id = l.id
                WHERE sn.master_item_id = ? AND sn.current_warehouse_id = ? AND sn.status = 'AVAILABLE'
                ORDER BY sn.serial_number
            ");
            $stmtSn->execute([$itemId, $warehouseId]);
            $response['serials'] = $stmtSn->fetchAll(\PDO::FETCH_ASSOC);
        }

        echo json_encode($response);
        exit;
    }

    public function itemsByWarehouse() {
        header('Content-Type: application/json');
        
        $warehouseId = (int)($_GET['warehouse_id'] ?? 0);
        $purpose = $_GET['purpose'] ?? '';
        
        if (!$warehouseId) {
            echo json_encode([]);
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        if ($purpose === 'incoming') {
            // Incoming can accept any active item, regardless of stock
            $stmt = $pdo->prepare("SELECT id, kode_barang, nama, is_serialized FROM master_items WHERE is_active = 1 ORDER BY nama");
            $stmt->execute();
            echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
            exit;
        } else if ($purpose === 'outgoing') {
            // Outgoing requires items with > 0 stock in the given warehouse
            // For bulk: stocks.quantity > 0
            // For serialized: count of AVAILABLE serials > 0
            $sql = "
                SELECT m.id, m.kode_barang, m.nama, m.is_serialized,
                CASE 
                    WHEN m.is_serialized = 1 THEN (SELECT COUNT(*) FROM serial_numbers sn WHERE sn.master_item_id = m.id AND sn.current_warehouse_id = ? AND sn.status = 'AVAILABLE')
                    ELSE COALESCE((SELECT quantity FROM stocks s WHERE s.master_item_id = m.id AND s.warehouse_id = ? LIMIT 1), 0)
                END as available_qty
                FROM master_items m
                WHERE m.is_active = 1
                HAVING available_qty > 0
                ORDER BY m.nama
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$warehouseId, $warehouseId]);
            echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
            exit;
        }
        
        echo json_encode([]);
        exit;
    }
}