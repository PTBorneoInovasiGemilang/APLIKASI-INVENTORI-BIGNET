<?php

namespace App\Services;

use App\Core\Database;
use App\Helpers\AuthHelper;
use PDO;
use Exception;

class StockService {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Mutate stock and record ledger
     */
    public function mutateStock($masterItemId, $warehouseId, $quantityDelta, $transactionType, $referenceType, $referenceId, $notes = '') {
        $inTransaction = $this->pdo->inTransaction();
        try {
            if (!$inTransaction) {
                $this->pdo->beginTransaction();
            }

            // 1. Get current stock (Lock for update to prevent race conditions)
            $stmt = $this->pdo->prepare("SELECT quantity FROM stocks WHERE master_item_id = ? AND warehouse_id = ? FOR UPDATE");
            $stmt->execute([$masterItemId, $warehouseId]);
            $currentStock = $stmt->fetchColumn();

            if ($currentStock === false) {
                // No existing stock record, balance is 0
                $balanceBefore = 0;
                $balanceAfter = $quantityDelta;

                if ($balanceAfter < 0) {
                    throw new Exception("Insufficient stock. Cannot reduce stock below 0.");
                }

                // Insert new stock record
                $insertStmt = $this->pdo->prepare("INSERT INTO stocks (master_item_id, warehouse_id, quantity) VALUES (?, ?, ?)");
                $insertStmt->execute([$masterItemId, $warehouseId, $balanceAfter]);
            } else {
                $balanceBefore = (int)$currentStock;
                $balanceAfter = $balanceBefore + $quantityDelta;

                if ($balanceAfter < 0) {
                    throw new Exception("Insufficient stock. Cannot reduce stock below 0.");
                }

                // Update existing stock
                $updateStmt = $this->pdo->prepare("UPDATE stocks SET quantity = ? WHERE master_item_id = ? AND warehouse_id = ?");
                $updateStmt->execute([$balanceAfter, $masterItemId, $warehouseId]);
            }

            // 2. Insert into stock_ledgers
            $userId = AuthHelper::isLoggedIn() ? AuthHelper::user()['id'] : 0;
            $ledgerStmt = $this->pdo->prepare("INSERT INTO stock_ledgers 
                (master_item_id, warehouse_id, transaction_type, reference_type, reference_id, quantity_delta, balance_before, balance_after, performed_by, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ledgerStmt->execute([
                $masterItemId, $warehouseId, $transactionType, $referenceType, $referenceId, 
                $quantityDelta, $balanceBefore, $balanceAfter, $userId, $notes
            ]);

            if (!$inTransaction) {
                $this->pdo->commit();
            }
            return true;

        } catch (Exception $e) {
            if (!$inTransaction) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Mutate Serial Number state and log movement
     */
    public function moveSerial($serialNumberId, $toStatus, $toWarehouseId = null, $toLocationId = null, $toHolderId = null, $referenceType = null, $referenceId = null, $notes = '', $conditionStatus = null) {
        $inTransaction = $this->pdo->inTransaction();
        try {
            if (!$inTransaction) {
                $this->pdo->beginTransaction();
            }

            // 1. Get current serial state
            $stmt = $this->pdo->prepare("SELECT status, current_warehouse_id, current_holder_id FROM serial_numbers WHERE id = ? FOR UPDATE");
            $stmt->execute([$serialNumberId]);
            $serial = $stmt->fetch();

            if (!$serial) {
                throw new Exception("Serial Number not found.");
            }

            $fromStatus = $serial['status'];
            $fromWarehouseId = $serial['current_warehouse_id'];
            $fromHolderId = $serial['current_holder_id'];

            if ($conditionStatus !== null) {
                $updateStmt = $this->pdo->prepare("UPDATE serial_numbers SET 
                    status = ?, current_warehouse_id = ?, current_location_id = ?, current_holder_id = ?, condition_status = ? 
                    WHERE id = ?");
                $updateStmt->execute([$toStatus, $toWarehouseId, $toLocationId, $toHolderId, $conditionStatus, $serialNumberId]);
            } else {
                $updateStmt = $this->pdo->prepare("UPDATE serial_numbers SET 
                    status = ?, current_warehouse_id = ?, current_location_id = ?, current_holder_id = ? 
                    WHERE id = ?");
                $updateStmt->execute([$toStatus, $toWarehouseId, $toLocationId, $toHolderId, $serialNumberId]);
            }

            // 3. Log movement
            $userId = AuthHelper::isLoggedIn() ? AuthHelper::user()['id'] : 0;
            $moveStmt = $this->pdo->prepare("INSERT INTO serial_movements 
                (serial_number_id, from_status, to_status, from_warehouse_id, to_warehouse_id, 
                from_holder_id, to_holder_id, reference_type, reference_id, performed_by, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $moveStmt->execute([
                $serialNumberId, $fromStatus, $toStatus, 
                $fromWarehouseId, $toWarehouseId,
                $fromHolderId, $toHolderId,
                $referenceType, $referenceId, $userId, $notes
            ]);

            if (!$inTransaction) {
                $this->pdo->commit();
            }
            return true;

        } catch (Exception $e) {
            if (!$inTransaction) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
