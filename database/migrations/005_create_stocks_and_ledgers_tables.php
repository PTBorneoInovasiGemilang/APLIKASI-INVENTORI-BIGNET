<?php

class CreateStocksAndLedgersTables {
    public function up(PDO $pdo) {
        $sql = "
        CREATE TABLE IF NOT EXISTS stocks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            master_item_id INT NOT NULL,
            warehouse_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_stock (master_item_id, warehouse_id),
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT,
            FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS stock_ledgers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            master_item_id INT NOT NULL,
            warehouse_id INT NOT NULL,
            transaction_type VARCHAR(50) NOT NULL,
            reference_type VARCHAR(50) NULL,
            reference_id INT NULL,
            quantity_delta INT NOT NULL,
            balance_before INT NOT NULL,
            balance_after INT NOT NULL,
            performed_by INT NOT NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT,
            FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
            FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($sql);
    }
}
