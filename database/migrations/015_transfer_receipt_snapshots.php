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

        // The receive workflow updates transfer_items before inserting transfer_receipts.
        // This BEFORE trigger therefore enriches the existing history notes with the
        // exact delta received in this event, without changing the controller contract.
        $pdo->exec("DROP TRIGGER IF EXISTS trg_transfer_receipt_history_note");
        $pdo->exec("CREATE TRIGGER trg_transfer_receipt_history_note
            BEFORE INSERT ON transfer_receipts
            FOR EACH ROW
            BEGIN
                DECLARE v_summary TEXT DEFAULT NULL;
                DECLARE v_total_qty INT DEFAULT 0;
                DECLARE v_kind_count INT DEFAULT 0;

                SELECT
                    COALESCE(SUM(ti.quantity_received - COALESCE(prev.cumulative_quantity, 0)), 0),
                    COALESCE(SUM(CASE WHEN (ti.quantity_received - COALESCE(prev.cumulative_quantity, 0)) > 0 THEN 1 ELSE 0 END), 0),
                    GROUP_CONCAT(
                        CASE
                            WHEN (ti.quantity_received - COALESCE(prev.cumulative_quantity, 0)) > 0
                            THEN CONCAT(m.nama, ' — ', (ti.quantity_received - COALESCE(prev.cumulative_quantity, 0)), ' unit')
                            ELSE NULL
                        END
                        ORDER BY m.nama SEPARATOR '; '
                    )
                INTO v_total_qty, v_kind_count, v_summary
                FROM transfer_items ti
                JOIN master_items m ON m.id = ti.master_item_id
                LEFT JOIN (
                    SELECT s.transfer_item_id, s.cumulative_quantity
                    FROM transfer_receipt_item_snapshots s
                    JOIN transfer_receipts pr ON pr.id = s.receipt_id
                    JOIN (
                        SELECT transfer_id, MAX(id) AS receipt_id
                        FROM transfer_receipts
                        WHERE transfer_id = NEW.transfer_id
                        GROUP BY transfer_id
                    ) latest ON latest.receipt_id = pr.id
                ) prev ON prev.transfer_item_id = ti.id
                WHERE ti.transfer_id = NEW.transfer_id;

                IF v_total_qty > 0 THEN
                    SET NEW.notes = CONCAT_WS(
                        '\n',
                        NULLIF(NEW.notes, ''),
                        CONCAT('[Barang diterima] ', v_kind_count, ' jenis · ', v_total_qty, ' unit — ', v_summary)
                    );
                END IF;
            END");

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
