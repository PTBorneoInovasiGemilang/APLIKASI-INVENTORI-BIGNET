<?php

class CreateTransactionsTables {
    public function up(PDO $pdo) {
        $sql = "
        -- 1. Submissions (Pengajuan)
        CREATE TABLE IF NOT EXISTS submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            submission_code VARCHAR(50) NOT NULL UNIQUE,
            submitted_by INT NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'PENDING',
            notes TEXT NULL,
            approved_by INT NULL,
            approved_at TIMESTAMP NULL,
            approval_notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE RESTRICT,
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS submission_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            submission_id INT NOT NULL,
            master_item_id INT NOT NULL,
            requested_quantity INT NOT NULL,
            approved_quantity INT NOT NULL DEFAULT 0,
            fulfilled_quantity INT NOT NULL DEFAULT 0,
            remaining_quantity INT NOT NULL DEFAULT 0,
            notes TEXT NULL,
            FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        -- 2. Incoming
        CREATE TABLE IF NOT EXISTS incoming (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_code VARCHAR(50) NOT NULL UNIQUE,
            warehouse_id INT NOT NULL,
            received_by INT NOT NULL,
            supplier_info VARCHAR(255) NULL,
            notes TEXT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'COMPLETED',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
            FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS incoming_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            incoming_id INT NOT NULL,
            master_item_id INT NOT NULL,
            quantity INT NOT NULL,
            FOREIGN KEY (incoming_id) REFERENCES incoming(id) ON DELETE CASCADE,
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        -- 3. Outgoing
        CREATE TABLE IF NOT EXISTS outgoing (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_code VARCHAR(50) NOT NULL UNIQUE,
            warehouse_id INT NOT NULL,
            submission_id INT NULL,
            processed_by INT NOT NULL,
            notes TEXT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'COMPLETED',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
            FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE RESTRICT,
            FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS outgoing_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            outgoing_id INT NOT NULL,
            master_item_id INT NOT NULL,
            quantity INT NOT NULL,
            FOREIGN KEY (outgoing_id) REFERENCES outgoing(id) ON DELETE CASCADE,
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        -- 4. Transfers
        CREATE TABLE IF NOT EXISTS transfers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_code VARCHAR(50) NOT NULL UNIQUE,
            source_warehouse_id INT NOT NULL,
            destination_warehouse_id INT NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'IN_TRANSIT',
            sent_by INT NOT NULL,
            received_by INT NULL,
            notes TEXT NULL,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            received_at TIMESTAMP NULL,
            FOREIGN KEY (source_warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
            FOREIGN KEY (destination_warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
            FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE RESTRICT,
            FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS transfer_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transfer_id INT NOT NULL,
            master_item_id INT NOT NULL,
            quantity_sent INT NOT NULL,
            quantity_received INT NOT NULL DEFAULT 0,
            FOREIGN KEY (transfer_id) REFERENCES transfers(id) ON DELETE CASCADE,
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        -- 5. Loans
        CREATE TABLE IF NOT EXISTS loans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_code VARCHAR(50) NOT NULL UNIQUE,
            borrower_id INT NOT NULL,
            processed_by INT NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'ACTIVE',
            notes TEXT NULL,
            due_date DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (borrower_id) REFERENCES users(id) ON DELETE RESTRICT,
            FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS loan_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            loan_id INT NOT NULL,
            master_item_id INT NOT NULL,
            serial_number_id INT NULL,
            quantity INT NOT NULL,
            returned_quantity INT NOT NULL DEFAULT 0,
            FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT,
            FOREIGN KEY (serial_number_id) REFERENCES serial_numbers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        -- 6. Returns
        CREATE TABLE IF NOT EXISTS returns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_code VARCHAR(50) NOT NULL UNIQUE,
            reference_type VARCHAR(50) NULL,
            reference_id INT NULL,
            warehouse_id INT NOT NULL,
            processed_by INT NOT NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
            FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS return_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            return_id INT NOT NULL,
            master_item_id INT NOT NULL,
            serial_number_id INT NULL,
            quantity INT NOT NULL,
            condition_status VARCHAR(50) NOT NULL DEFAULT 'GOOD',
            FOREIGN KEY (return_id) REFERENCES returns(id) ON DELETE CASCADE,
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT,
            FOREIGN KEY (serial_number_id) REFERENCES serial_numbers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        -- 7. Installations
        CREATE TABLE IF NOT EXISTS installations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_code VARCHAR(50) NOT NULL UNIQUE,
            technician_id INT NOT NULL,
            customer_site VARCHAR(255) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'INSTALLED',
            notes TEXT NULL,
            installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS installation_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            installation_id INT NOT NULL,
            master_item_id INT NOT NULL,
            serial_number_id INT NULL,
            quantity INT NOT NULL,
            FOREIGN KEY (installation_id) REFERENCES installations(id) ON DELETE CASCADE,
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT,
            FOREIGN KEY (serial_number_id) REFERENCES serial_numbers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        -- 8. Stock Adjustments
        CREATE TABLE IF NOT EXISTS stock_adjustments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_code VARCHAR(50) NOT NULL UNIQUE,
            warehouse_id INT NOT NULL,
            processed_by INT NOT NULL,
            reason TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
            FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS stock_adjustment_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stock_adjustment_id INT NOT NULL,
            master_item_id INT NOT NULL,
            old_quantity INT NOT NULL,
            new_quantity INT NOT NULL,
            difference INT NOT NULL,
            FOREIGN KEY (stock_adjustment_id) REFERENCES stock_adjustments(id) ON DELETE CASCADE,
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($sql);
    }
}
