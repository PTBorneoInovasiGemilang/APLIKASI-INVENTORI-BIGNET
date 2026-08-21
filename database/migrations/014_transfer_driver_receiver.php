<?php

class TransferDriverReceiver {
    public function up(PDO $pdo) {
        $hasColumn = function (string $table, string $column) use ($pdo): bool {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
            );
            $stmt->execute([$table, $column]);
            return (bool)$stmt->fetchColumn();
        };

        if (!$hasColumn('transfers', 'driver_name')) {
            $pdo->exec("ALTER TABLE transfers ADD COLUMN driver_name VARCHAR(150) NULL AFTER sent_by");
        }

        if (!$hasColumn('transfers', 'driver_phone')) {
            $pdo->exec("ALTER TABLE transfers ADD COLUMN driver_phone VARCHAR(50) NULL AFTER driver_name");
        }

        if (!$hasColumn('transfers', 'vehicle_number')) {
            $pdo->exec("ALTER TABLE transfers ADD COLUMN vehicle_number VARCHAR(50) NULL AFTER driver_phone");
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS transfer_receipts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transfer_id INT NOT NULL,
            processed_by INT NULL,
            receiver_name VARCHAR(150) NOT NULL,
            receiver_role VARCHAR(100) NULL,
            receiver_phone VARCHAR(50) NULL,
            notes TEXT NULL,
            received_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_transfer_receipts_transfer (transfer_id),
            KEY idx_transfer_receipts_received_at (received_at),
            CONSTRAINT fk_transfer_receipts_transfer
                FOREIGN KEY (transfer_id) REFERENCES transfers(id) ON DELETE CASCADE,
            CONSTRAINT fk_transfer_receipts_processed_by
                FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}
