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
                SELECT sn.serial_number, l.name as location_name
                FROM serial_numbers sn
                LEFT JOIN locations l ON sn.current_location_id = l.id
                WHERE sn.master_item_id = ? AND sn.current_warehouse_id = ? AND sn.status = 'AVAILABLE'
            ");
            $stmtSn->execute([$itemId, $warehouseId]);
            $response['serials'] = $stmtSn->fetchAll(\PDO::FETCH_ASSOC);
        }

        echo json_encode($response);
        exit;
    }
}
