<?php

$config = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
$pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Migration for Incidents (Damage/Lost)
$sql = "
CREATE TABLE IF NOT EXISTS incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_code VARCHAR(50) NOT NULL UNIQUE,
    incident_type VARCHAR(20) NOT NULL,
    reported_by INT NOT NULL,
    reported_date DATETIME NOT NULL,
    warehouse_id INT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    resolution TEXT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(reported_by) REFERENCES users(id),
    FOREIGN KEY(warehouse_id) REFERENCES warehouses(id)
);

CREATE TABLE IF NOT EXISTS incident_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incident_id INT NOT NULL,
    master_item_id INT NOT NULL,
    serial_number_id INT NULL,
    quantity INT NOT NULL,
    last_holder_name VARCHAR(100) NULL,
    evidence_path VARCHAR(255) NULL,
    reason TEXT NOT NULL,
    FOREIGN KEY(incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
    FOREIGN KEY(master_item_id) REFERENCES master_items(id),
    FOREIGN KEY(serial_number_id) REFERENCES serial_numbers(id)
);

CREATE TABLE IF NOT EXISTS stock_opnames (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_code VARCHAR(50) NOT NULL UNIQUE,
    warehouse_id INT NOT NULL,
    conducted_by INT NOT NULL,
    opname_date DATETIME NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'DRAFT',
    adjustment_transaction_code VARCHAR(50) NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY(conducted_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS stock_opname_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_opname_id INT NOT NULL,
    master_item_id INT NOT NULL,
    serial_number_id INT NULL,
    system_quantity INT NOT NULL,
    physical_quantity INT NOT NULL,
    variance INT NOT NULL,
    reason TEXT NULL,
    FOREIGN KEY(stock_opname_id) REFERENCES stock_opnames(id) ON DELETE CASCADE,
    FOREIGN KEY(master_item_id) REFERENCES master_items(id),
    FOREIGN KEY(serial_number_id) REFERENCES serial_numbers(id)
);
";

$pdo->exec($sql);
echo "Migrations for Incidents and Stock Opnames created successfully.\n";
