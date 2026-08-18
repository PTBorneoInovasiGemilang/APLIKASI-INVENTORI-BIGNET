<?php

class CreateSerialNumbersAndMovementsTables {
    public function up(PDO $pdo) {
        $sql = "
        CREATE TABLE IF NOT EXISTS serial_numbers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            master_item_id INT NOT NULL,
            serial_number VARCHAR(100) NOT NULL UNIQUE,
            status VARCHAR(50) NOT NULL,
            current_warehouse_id INT NULL,
            current_location_id INT NULL,
            current_holder_id INT NULL,
            condition_status VARCHAR(50) NOT NULL DEFAULT 'GOOD',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT,
            FOREIGN KEY (current_warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
            FOREIGN KEY (current_location_id) REFERENCES locations(id) ON DELETE SET NULL,
            FOREIGN KEY (current_holder_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS serial_movements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            serial_number_id INT NOT NULL,
            from_status VARCHAR(50) NULL,
            to_status VARCHAR(50) NOT NULL,
            from_warehouse_id INT NULL,
            to_warehouse_id INT NULL,
            from_holder_id INT NULL,
            to_holder_id INT NULL,
            reference_type VARCHAR(50) NULL,
            reference_id INT NULL,
            performed_by INT NOT NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (serial_number_id) REFERENCES serial_numbers(id) ON DELETE CASCADE,
            FOREIGN KEY (from_warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
            FOREIGN KEY (to_warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
            FOREIGN KEY (from_holder_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (to_holder_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($sql);
    }
}
