<?php

class CreateAuditLogsTable {
    public function up(PDO $pdo) {
        $sql = "
        CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            action VARCHAR(50) NOT NULL,
            entity VARCHAR(100) NOT NULL,
            entity_id INT NULL,
            old_data JSON NULL,
            new_data JSON NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($sql);
    }
}
