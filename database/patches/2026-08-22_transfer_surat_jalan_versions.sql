-- Transfer shipping/receiving data + immutable Surat Jalan versions.
-- Safe to run on existing databases; columns are added only when absent.

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transfers' AND COLUMN_NAME = 'driver_name') = 0,
    'ALTER TABLE transfers ADD COLUMN driver_name VARCHAR(150) NULL AFTER sent_by',
    'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transfers' AND COLUMN_NAME = 'driver_phone') = 0,
    'ALTER TABLE transfers ADD COLUMN driver_phone VARCHAR(50) NULL AFTER driver_name',
    'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transfers' AND COLUMN_NAME = 'vehicle_number') = 0,
    'ALTER TABLE transfers ADD COLUMN vehicle_number VARCHAR(80) NULL AFTER driver_phone',
    'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transfers' AND COLUMN_NAME = 'receiver_name') = 0,
    'ALTER TABLE transfers ADD COLUMN receiver_name VARCHAR(150) NULL AFTER received_by',
    'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transfers' AND COLUMN_NAME = 'receiver_role') = 0,
    'ALTER TABLE transfers ADD COLUMN receiver_role VARCHAR(150) NULL AFTER receiver_name',
    'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transfers' AND COLUMN_NAME = 'receiver_phone') = 0,
    'ALTER TABLE transfers ADD COLUMN receiver_phone VARCHAR(50) NULL AFTER receiver_role',
    'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS transfer_surat_jalan_versions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    transfer_id BIGINT UNSIGNED NOT NULL,
    version_no INT UNSIGNED NOT NULL,
    document_no VARCHAR(80) NOT NULL,
    document_type VARCHAR(30) NOT NULL DEFAULT 'TRANSFER',
    event_label VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NULL,
    sent_at DATETIME NULL,
    received_at DATETIME NULL,
    sender_name VARCHAR(150) NULL,
    driver_name VARCHAR(150) NULL,
    driver_phone VARCHAR(50) NULL,
    vehicle_number VARCHAR(80) NULL,
    receiver_name VARCHAR(150) NULL,
    receiver_role VARCHAR(150) NULL,
    receiver_phone VARCHAR(50) NULL,
    source_warehouse VARCHAR(150) NULL,
    destination_warehouse VARCHAR(150) NULL,
    notes TEXT NULL,
    items_json LONGTEXT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_transfer_sj_version (transfer_id, version_no),
    UNIQUE KEY uq_transfer_sj_document_no (document_no),
    KEY idx_transfer_sj_versions_transfer (transfer_id, version_no),
    CONSTRAINT fk_transfer_sj_versions_transfer FOREIGN KEY (transfer_id) REFERENCES transfers(id) ON DELETE CASCADE,
    CONSTRAINT fk_transfer_sj_versions_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
