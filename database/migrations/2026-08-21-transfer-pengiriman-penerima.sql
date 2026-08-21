-- BIGNET - Transfer Gudang: data pembawa dan penerimaan fisik
-- Jalankan SEKALI pada database aktif.

ALTER TABLE transfers
    ADD COLUMN driver_name VARCHAR(150) NULL AFTER sent_by,
    ADD COLUMN driver_phone VARCHAR(50) NULL AFTER driver_name,
    ADD COLUMN vehicle_number VARCHAR(50) NULL AFTER driver_phone;

CREATE TABLE transfer_receipts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    transfer_id BIGINT UNSIGNED NOT NULL,
    processed_by BIGINT UNSIGNED NULL,
    receiver_name VARCHAR(150) NOT NULL,
    receiver_role VARCHAR(100) NULL,
    receiver_phone VARCHAR(50) NULL,
    notes TEXT NULL,
    received_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_transfer_receipts_transfer (transfer_id),
    KEY idx_transfer_receipts_received_at (received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
