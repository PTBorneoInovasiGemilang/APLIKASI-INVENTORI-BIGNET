<?php

class CreateLoanManualItems {
    public function up(PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS loan_manual_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                loan_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                specification TEXT NULL,
                unit VARCHAR(100) NULL,
                quantity INT NOT NULL DEFAULT 1,
                returned_quantity INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }
}
