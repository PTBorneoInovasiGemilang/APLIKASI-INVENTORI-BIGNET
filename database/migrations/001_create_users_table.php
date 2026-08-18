<?php

class CreateUsersTable {
    public function up(PDO $pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('SUPERADMIN', 'ADMIN', 'TEKNISI_NOC') NOT NULL DEFAULT 'TEKNISI_NOC',
            phone VARCHAR(20) NULL,
            photo_path VARCHAR(255) NULL,
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            last_login_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $pdo->exec($sql);
    }
}
