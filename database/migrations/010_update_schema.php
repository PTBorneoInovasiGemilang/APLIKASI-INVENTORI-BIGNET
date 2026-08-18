<?php

class UpdateSchema {
    public function up(PDO $pdo) {
        // Helper: check if column exists
        $hasColumn = function(string $table, string $column) use ($pdo): bool {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
            );
            $stmt->execute([$table, $column]);
            return $stmt->fetchColumn() > 0;
        };

        // Helper: check if table exists
        $hasTable = function(string $table) use ($pdo): bool {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?"
            );
            $stmt->execute([$table]);
            return $stmt->fetchColumn() > 0;
        };

        // Helper: check if constraint exists
        $hasConstraint = function(string $table, string $constraint) use ($pdo): bool {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?"
            );
            $stmt->execute([$table, $constraint]);
            return $stmt->fetchColumn() > 0;
        };

        // 1. Update submissions for 2 types
        if (!$hasColumn('submissions', 'submission_type')) {
            $pdo->exec("ALTER TABLE submissions ADD COLUMN submission_type ENUM('STOCK', 'MANUAL') NOT NULL DEFAULT 'STOCK' AFTER submitted_by");
        }

        if (!$hasColumn('submission_items', 'warehouse_id')) {
            $pdo->exec("ALTER TABLE submission_items ADD COLUMN warehouse_id INT NULL AFTER submission_id");
        }
        if (!$hasConstraint('submission_items', 'fk_sub_items_warehouse')) {
            $pdo->exec("ALTER TABLE submission_items ADD CONSTRAINT fk_sub_items_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL");
        }

        // 2. Loan Item Serials pivot for MULTI-SN
        if (!$hasTable('loan_item_serials')) {
            $pdo->exec("
                CREATE TABLE loan_item_serials (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    loan_item_id INT NOT NULL,
                    serial_number_id INT NOT NULL,
                    status ENUM('BORROWED', 'RETURNED') NOT NULL DEFAULT 'BORROWED',
                    FOREIGN KEY (loan_item_id) REFERENCES loan_items(id) ON DELETE CASCADE,
                    FOREIGN KEY (serial_number_id) REFERENCES serial_numbers(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // 3. Installations updates
        if (!$hasColumn('installations', 'customer_name')) {
            $pdo->exec("ALTER TABLE installations ADD COLUMN customer_name VARCHAR(255) NULL AFTER technician_id");
        }
        if (!$hasColumn('installations', 'photo_path')) {
            $pdo->exec("ALTER TABLE installations ADD COLUMN photo_path VARCHAR(255) NULL AFTER notes");
        }
    }
}
