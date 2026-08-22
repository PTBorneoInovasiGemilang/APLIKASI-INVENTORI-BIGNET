<?php

class TransferReceiptSnapshots {
    public function up(PDO $pdo) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS transfer_receipt_item_snapshots (
            id INT AUTO_INCREMENT PRIMARY KEY,
            receipt_id INT NOT NULL,
            transfer_item_id INT NOT NULL,
            master_item_id INT NOT NULL,
            cumulative_quantity INT NOT NULL DEFAULT 0,
            serial_numbers_json LONGTEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_receipt_item_snapshot (receipt_id, transfer_item_id),
            KEY idx_receipt_snapshot_receipt (receipt_id),
            KEY idx_receipt_snapshot_transfer_item (transfer_item_id),
            CONSTRAINT fk_receipt_snapshot_receipt FOREIGN KEY (receipt_id) REFERENCES transfer_receipts(id) ON DELETE CASCADE,
            CONSTRAINT fk_receipt_snapshot_transfer_item FOREIGN KEY (transfer_item_id) REFERENCES transfer_items(id) ON DELETE CASCADE,
            CONSTRAINT fk_receipt_snapshot_master_item FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("DROP TRIGGER IF EXISTS trg_transfer_receipt_snapshot_after_insert");
        $pdo->exec("CREATE TRIGGER trg_transfer_receipt_snapshot_after_insert
            AFTER INSERT ON transfer_receipts
            FOR EACH ROW
            BEGIN
                INSERT INTO transfer_receipt_item_snapshots
                    (receipt_id, transfer_item_id, master_item_id, cumulative_quantity, serial_numbers_json)
                SELECT
                    NEW.id,
                    ti.id,
                    ti.master_item_id,
                    ti.quantity_received,
                    (
                        SELECT CONCAT('[', GROUP_CONCAT(JSON_QUOTE(sn.serial_number) ORDER BY sn.serial_number SEPARATOR ','), ']')
                        FROM transfer_item_serials tis
                        JOIN serial_numbers sn ON sn.id = tis.serial_number_id
                        WHERE tis.transfer_item_id = ti.id AND tis.status = 'RECEIVED'
                    )
                FROM transfer_items ti
                WHERE ti.transfer_id = NEW.transfer_id;
            END");

        // Backfill only transfers with exactly one receipt; the cumulative snapshot
        // is then also the exact quantity received in that receipt.
        $pdo->exec("INSERT IGNORE INTO transfer_receipt_item_snapshots
            (receipt_id, transfer_item_id, master_item_id, cumulative_quantity, serial_numbers_json)
            SELECT
                tr.id,
                ti.id,
                ti.master_item_id,
                ti.quantity_received,
                (
                    SELECT CONCAT('[', GROUP_CONCAT(JSON_QUOTE(sn.serial_number) ORDER BY sn.serial_number SEPARATOR ','), ']')
                    FROM transfer_item_serials tis
                    JOIN serial_numbers sn ON sn.id = tis.serial_number_id
                    WHERE tis.transfer_item_id = ti.id AND tis.status = 'RECEIVED'
                )
            FROM transfer_receipts tr
            JOIN transfers t ON t.id = tr.transfer_id
            JOIN transfer_items ti ON ti.transfer_id = t.id
            JOIN (
                SELECT transfer_id
                FROM transfer_receipts
                GROUP BY transfer_id
                HAVING COUNT(*) = 1
            ) only_one ON only_one.transfer_id = tr.transfer_id");
    }
}
