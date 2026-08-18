<?php

class CreateMasterItemAttachmentsTable {
    public function up(PDO $pdo) {
        $sql = "
        CREATE TABLE IF NOT EXISTS master_item_attachments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            master_item_id INT NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            file_size INT NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            uploaded_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (master_item_id) REFERENCES master_items(id) ON DELETE CASCADE,
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($sql);
    }
}
