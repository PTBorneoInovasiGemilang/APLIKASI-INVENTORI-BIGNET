<?php

class CreateMasterItemsTable {
    public function up(PDO $pdo) {
        $sql = "
        CREATE TABLE IF NOT EXISTS master_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode_barang VARCHAR(50) NOT NULL UNIQUE,
            nama VARCHAR(150) NOT NULL,
            item_category_id INT NOT NULL,
            unit_id INT NOT NULL,
            is_serialized BOOLEAN NOT NULL DEFAULT FALSE,
            stok_minimum INT NOT NULL DEFAULT 0,
            deskripsi TEXT NULL,
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (item_category_id) REFERENCES item_categories(id) ON DELETE RESTRICT,
            FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($sql);
    }
}
