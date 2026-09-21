-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2026 at 08:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_gudang_tommy`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `reset_bignet_test_database` ()   BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE tbl VARCHAR(255);

    DECLARE cur CURSOR FOR
        SELECT TABLE_NAME
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = @db
          AND TABLE_TYPE = 'BASE TABLE'
          AND TABLE_NAME NOT IN (
              'users'
          );

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    reset_loop: LOOP
        FETCH cur INTO tbl;

        IF done = 1 THEN
            LEAVE reset_loop;
        END IF;

        SET @sql = CONCAT('TRUNCATE TABLE `', tbl, '`');

        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END LOOP;

    CLOSE cur;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `reference_type` varchar(50) NOT NULL,
  `reference_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity` varchar(100) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incidents`
--

CREATE TABLE `incidents` (
  `id` int(11) NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `incident_type` varchar(20) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `reported_date` datetime NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `resolution` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incident_items`
--

CREATE TABLE `incident_items` (
  `id` int(11) NOT NULL,
  `incident_id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `serial_number_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `last_holder_name` varchar(100) DEFAULT NULL,
  `evidence_path` varchar(255) DEFAULT NULL,
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incoming`
--

CREATE TABLE `incoming` (
  `id` int(11) NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `received_by` int(11) NOT NULL,
  `supplier_info` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'COMPLETED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incoming_items`
--

CREATE TABLE `incoming_items` (
  `id` int(11) NOT NULL,
  `incoming_id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `installations`
--

CREATE TABLE `installations` (
  `id` int(11) NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_site` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'INSTALLED',
  `reference_code` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `installed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `installation_items`
--

CREATE TABLE `installation_items` (
  `id` int(11) NOT NULL,
  `installation_id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `serial_number_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_categories`
--

CREATE TABLE `item_categories` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `submission_id` int(11) DEFAULT NULL,
  `outgoing_id` int(11) DEFAULT NULL,
  `loan_type` varchar(50) NOT NULL DEFAULT 'INTERNAL',
  `loan_date` date DEFAULT NULL,
  `processed_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `handed_over_by` int(11) DEFAULT NULL,
  `handed_over_at` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'PENDING_APPROVAL',
  `notes` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_items`
--

CREATE TABLE `loan_items` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `requested_quantity` int(11) NOT NULL DEFAULT 0,
  `approved_quantity` int(11) NOT NULL DEFAULT 0,
  `serial_number_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `returned_quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_item_serials`
--

CREATE TABLE `loan_item_serials` (
  `id` int(11) NOT NULL,
  `loan_item_id` int(11) DEFAULT NULL,
  `loan_manual_item_id` int(11) DEFAULT NULL,
  `serial_number_id` int(11) NOT NULL,
  `status` enum('PENDING','BORROWED','RETURNED') NOT NULL DEFAULT 'PENDING'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_manual_items`
--

CREATE TABLE `loan_manual_items` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `master_item_id` int(11) DEFAULT NULL,
  `requested_quantity` int(11) NOT NULL DEFAULT 0,
  `approved_quantity` int(11) NOT NULL DEFAULT 0,
  `specification` text DEFAULT NULL,
  `unit` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `returned_quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_items`
--

CREATE TABLE `master_items` (
  `id` int(11) NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `item_category_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `is_serialized` tinyint(1) NOT NULL DEFAULT 0,
  `stok_minimum` int(11) NOT NULL DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_item_attachments`
--

CREATE TABLE `master_item_attachments` (
  `id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'info',
  `source_route` varchar(255) DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `outgoing`
--

CREATE TABLE `outgoing` (
  `id` int(11) NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `submission_id` int(11) DEFAULT NULL,
  `purpose` varchar(30) NOT NULL DEFAULT 'ISSUE',
  `loan_id` int(11) DEFAULT NULL,
  `processed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'COMPLETED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `outgoing_items`
--

CREATE TABLE `outgoing_items` (
  `id` int(11) NOT NULL,
  `outgoing_id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `submission_item_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) NOT NULL,
  `status` enum('PENDING','COMPLETED','REJECTED') DEFAULT 'COMPLETED',
  `technician_id` int(11) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_items`
--

CREATE TABLE `return_items` (
  `id` int(11) NOT NULL,
  `return_id` int(11) NOT NULL,
  `master_item_id` int(11) DEFAULT NULL,
  `loan_item_id` int(11) DEFAULT NULL,
  `loan_manual_item_id` int(11) DEFAULT NULL,
  `serial_number_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `condition_status` varchar(50) NOT NULL DEFAULT 'GOOD'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `id` int(11) NOT NULL,
  `migration` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `serial_movements`
--

CREATE TABLE `serial_movements` (
  `id` int(11) NOT NULL,
  `serial_number_id` int(11) NOT NULL,
  `from_status` varchar(50) DEFAULT NULL,
  `to_status` varchar(50) NOT NULL,
  `from_warehouse_id` int(11) DEFAULT NULL,
  `to_warehouse_id` int(11) DEFAULT NULL,
  `from_holder_id` int(11) DEFAULT NULL,
  `to_holder_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `serial_numbers`
--

CREATE TABLE `serial_numbers` (
  `id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  `current_warehouse_id` int(11) DEFAULT NULL,
  `current_location_id` int(11) DEFAULT NULL,
  `current_holder_id` int(11) DEFAULT NULL,
  `condition_status` varchar(50) NOT NULL DEFAULT 'GOOD',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustments`
--

CREATE TABLE `stock_adjustments` (
  `id` int(11) NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `processed_by` int(11) NOT NULL,
  `reason` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustment_items`
--

CREATE TABLE `stock_adjustment_items` (
  `id` int(11) NOT NULL,
  `stock_adjustment_id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `old_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `difference` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_ledgers`
--

CREATE TABLE `stock_ledgers` (
  `id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `quantity_delta` int(11) NOT NULL,
  `balance_before` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `performed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_opnames`
--

CREATE TABLE `stock_opnames` (
  `id` int(11) NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `conducted_by` int(11) NOT NULL,
  `opname_date` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'DRAFT',
  `adjustment_transaction_code` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_opname_items`
--

CREATE TABLE `stock_opname_items` (
  `id` int(11) NOT NULL,
  `stock_opname_id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `serial_number_id` int(11) DEFAULT NULL,
  `system_quantity` int(11) NOT NULL,
  `physical_quantity` int(11) NOT NULL,
  `variance` int(11) NOT NULL,
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `submission_code` varchar(50) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `submission_type` enum('STOCK','MANUAL','MIXED') DEFAULT 'MIXED',
  `status` varchar(50) NOT NULL DEFAULT 'PENDING',
  `notes` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission_items`
--

CREATE TABLE `submission_items` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `source_type` enum('STOCK','MANUAL') DEFAULT 'STOCK',
  `warehouse_id` int(11) DEFAULT NULL,
  `master_item_id` int(11) DEFAULT NULL,
  `manual_name` varchar(255) DEFAULT NULL,
  `manual_spec` text DEFAULT NULL,
  `manual_unit` varchar(50) DEFAULT NULL,
  `requested_quantity` int(11) NOT NULL,
  `approved_quantity` int(11) NOT NULL DEFAULT 0,
  `fulfilled_quantity` int(11) NOT NULL DEFAULT 0,
  `remaining_quantity` int(11) NOT NULL DEFAULT 0,
  `status` enum('PENDING','APPROVED','PARTIAL_APPROVED','REJECTED','COMPLETED') DEFAULT 'PENDING',
  `rejection_reason` text DEFAULT NULL,
  `processing_note` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

CREATE TABLE `transfers` (
  `id` int(11) NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `source_warehouse_id` int(11) NOT NULL,
  `destination_warehouse_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'IN_TRANSIT',
  `sent_by` int(11) NOT NULL,
  `driver_name` varchar(150) DEFAULT NULL,
  `driver_phone` varchar(50) DEFAULT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `receiver_name` varchar(150) DEFAULT NULL,
  `receiver_role` varchar(150) DEFAULT NULL,
  `receiver_phone` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `received_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_items`
--

CREATE TABLE `transfer_items` (
  `id` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `quantity_sent` int(11) NOT NULL,
  `quantity_received` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_item_serials`
--

CREATE TABLE `transfer_item_serials` (
  `id` int(11) NOT NULL,
  `transfer_item_id` int(11) NOT NULL,
  `serial_number_id` int(11) NOT NULL,
  `status` enum('IN_TRANSIT','RECEIVED') NOT NULL DEFAULT 'IN_TRANSIT'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_receipts`
--

CREATE TABLE `transfer_receipts` (
  `id` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `receiver_name` varchar(150) NOT NULL,
  `receiver_role` varchar(100) DEFAULT NULL,
  `receiver_phone` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `received_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `transfer_receipts`
--
DELIMITER $$
CREATE TRIGGER `trg_transfer_receipt_history_note` BEFORE INSERT ON `transfer_receipts` FOR EACH ROW SET NEW.notes = CONCAT_WS(
    '\n',
    NULLIF(NEW.notes, ''),
    (
        SELECT CONCAT(
            '[Barang diterima] ',
            x.kind_count,
            ' jenis · ',
            x.total_qty,
            ' unit — ',
            x.summary
        )
        FROM (
            SELECT
                COUNT(*) AS kind_count,
                COALESCE(SUM(delta_qty), 0) AS total_qty,
                GROUP_CONCAT(
                    CONCAT(item_name, ' — ', delta_qty, ' unit')
                    ORDER BY item_name
                    SEPARATOR '; '
                ) AS summary
            FROM (
                SELECT
                    m.nama AS item_name,
                    ti.quantity_received -
                    COALESCE(
                        (
                            SELECT MAX(s.cumulative_quantity)
                            FROM transfer_receipt_item_snapshots s
                            WHERE s.transfer_item_id = ti.id
                        ),
                        0
                    ) AS delta_qty
                FROM transfer_items ti
                JOIN master_items m
                    ON m.id = ti.master_item_id
                WHERE ti.transfer_id = NEW.transfer_id
            ) d
            WHERE delta_qty > 0
        ) x
        WHERE x.total_qty > 0
    )
)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_transfer_receipt_snapshot_after_insert` AFTER INSERT ON `transfer_receipts` FOR EACH ROW INSERT INTO transfer_receipt_item_snapshots (
    receipt_id,
    transfer_item_id,
    master_item_id,
    cumulative_quantity,
    serial_numbers_json
)
SELECT
    NEW.id,
    ti.id,
    ti.master_item_id,
    ti.quantity_received,
    (
        SELECT CONCAT(
            '[',
            GROUP_CONCAT(
                JSON_QUOTE(sn.serial_number)
                ORDER BY sn.serial_number
                SEPARATOR ','
            ),
            ']'
        )
        FROM transfer_item_serials tis
        JOIN serial_numbers sn
            ON sn.id = tis.serial_number_id
        WHERE tis.transfer_item_id = ti.id
          AND tis.status = 'RECEIVED'
    )
FROM transfer_items ti
WHERE ti.transfer_id = NEW.transfer_id
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_receipt_item_snapshots`
--

CREATE TABLE `transfer_receipt_item_snapshots` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `transfer_item_id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
  `cumulative_quantity` int(11) NOT NULL DEFAULT 0,
  `serial_numbers_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_surat_jalan_versions`
--

CREATE TABLE `transfer_surat_jalan_versions` (
  `id` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `version_no` int(11) NOT NULL,
  `document_no` varchar(80) NOT NULL,
  `document_type` varchar(30) NOT NULL DEFAULT 'TRANSFER',
  `event_label` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `sender_name` varchar(150) DEFAULT NULL,
  `driver_name` varchar(150) DEFAULT NULL,
  `driver_phone` varchar(50) DEFAULT NULL,
  `vehicle_number` varchar(80) DEFAULT NULL,
  `receiver_name` varchar(150) DEFAULT NULL,
  `receiver_role` varchar(150) DEFAULT NULL,
  `receiver_phone` varchar(50) DEFAULT NULL,
  `source_warehouse` varchar(150) DEFAULT NULL,
  `destination_warehouse` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `items_json` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('SUPERADMIN','ADMIN','TEKNISI_NOC') NOT NULL DEFAULT 'TEKNISI_NOC',
  `account_status` enum('PENDING_APPROVAL','APPROVED','REJECTED','SUSPENDED') DEFAULT 'APPROVED',
  `phone` varchar(20) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `account_status`, `phone`, `photo_path`, `rejection_reason`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@ptbig.com', '$2y$10$SpNVJGcdsfjB1TaTbn7r.uV3Kl3FNc/9XquLsLbcGMY77q2C/vz/W', 'SUPERADMIN', 'APPROVED', 'superadmin@ptbig.com', 'profiles/profile_1_6aa7592283f32.jpg', NULL, 1, '2026-09-18 05:19:49', '2026-08-15 05:13:46', '2026-09-18 05:19:49'),
(3382, 'AISYAH', 'mariatsa035@gmail.com', '$2y$10$.FS8Zrrftb8r4wxErBaUEuKuEvumDxAgn0ZpUUx52puLY7aKw1qru', 'ADMIN', 'APPROVED', '', 'profiles/profile_3382_1787284966.jpg', NULL, 1, '2026-09-18 05:08:47', '2026-08-17 12:13:26', '2026-09-18 05:08:47'),
(3646, 'TOMMY RYAN DWIPUTRA', 'kontol@gmail.com', '$2y$10$RIYig5nUW4PnbFaRKpO1rOWvjtIBG7t7eCuChf8T6sP8tHfmYcpjG', 'TEKNISI_NOC', 'APPROVED', '', NULL, NULL, 1, '2026-08-19 19:14:15', '2026-08-17 13:16:04', '2026-08-19 19:14:15'),
(3778, 'PUKIMAY', 'pepekenak@gmail.com', '$2y$10$QaLLOxVxaMpot0ZI9Wrdce66bZmIY6QAkLmCyRig18KIjiukGR8ey', 'ADMIN', 'APPROVED', '0899345781312893', 'profiles/profile_6aa759593d557.jpg', NULL, 1, '2026-09-14 02:18:27', '2026-09-14 02:18:01', '2026-09-14 02:18:27'),
(3779, 'UAT Test User', 'uat_test_1789356785973@ptbig.com', '$2y$10$kaFATQMkDAxp5DDDogKUbeC9Zw.wFI5Gkftc6E9ROewUvtm.hSPw6', 'TEKNISI_NOC', 'APPROVED', '', NULL, NULL, 1, NULL, '2026-09-14 03:33:36', '2026-09-14 03:33:36'),
(3780, 'UAT Admin', 'admin@ptbig.com', '$2y$10$kGBs8cBQwpzwzFy9qFM2G.VsjESqmazcM3YHSKFaLJiVg7K/yEDl6', 'ADMIN', 'APPROVED', '', NULL, NULL, 1, '2026-09-17 23:57:36', '2026-09-14 03:42:08', '2026-09-17 23:57:36'),
(3781, 'UAT Teknisi', 'teknisi@ptbig.com', '$2y$10$vPyHo7Uwd9eUBZscXyAA0.yIhtSphnEpbSWc1yXOvy8Lv/ltYvnDi', 'TEKNISI_NOC', 'APPROVED', '', NULL, NULL, 1, '2026-09-17 23:57:29', '2026-09-14 03:42:42', '2026-09-17 23:57:29');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `reported_by` (`reported_by`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `incident_items`
--
ALTER TABLE `incident_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `incident_id` (`incident_id`),
  ADD KEY `master_item_id` (`master_item_id`),
  ADD KEY `serial_number_id` (`serial_number_id`);

--
-- Indexes for table `incoming`
--
ALTER TABLE `incoming`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `incoming_items`
--
ALTER TABLE `incoming_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `incoming_id` (`incoming_id`),
  ADD KEY `master_item_id` (`master_item_id`);

--
-- Indexes for table `installations`
--
ALTER TABLE `installations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `technician_id` (`technician_id`);

--
-- Indexes for table `installation_items`
--
ALTER TABLE `installation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `installation_id` (`installation_id`),
  ADD KEY `master_item_id` (`master_item_id`),
  ADD KEY `serial_number_id` (`serial_number_id`);

--
-- Indexes for table `item_categories`
--
ALTER TABLE `item_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `fk_loans_approved_by` (`approved_by`),
  ADD KEY `fk_loans_handed_over_by` (`handed_over_by`);

--
-- Indexes for table `loan_items`
--
ALTER TABLE `loan_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loan_id` (`loan_id`),
  ADD KEY `master_item_id` (`master_item_id`),
  ADD KEY `serial_number_id` (`serial_number_id`);

--
-- Indexes for table `loan_item_serials`
--
ALTER TABLE `loan_item_serials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loan_item_id` (`loan_item_id`),
  ADD KEY `serial_number_id` (`serial_number_id`);

--
-- Indexes for table `loan_manual_items`
--
ALTER TABLE `loan_manual_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loan_manual_items_loan_id` (`loan_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_location_warehouse` (`warehouse_id`,`code`),
  ADD UNIQUE KEY `uq_locations_code` (`code`);

--
-- Indexes for table `master_items`
--
ALTER TABLE `master_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`),
  ADD KEY `item_category_id` (`item_category_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `master_item_attachments`
--
ALTER TABLE `master_item_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `master_item_id` (`master_item_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_user` (`user_id`,`read_at`);

--
-- Indexes for table `outgoing`
--
ALTER TABLE `outgoing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `submission_id` (`submission_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `outgoing_items`
--
ALTER TABLE `outgoing_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `outgoing_id` (`outgoing_id`),
  ADD KEY `master_item_id` (`master_item_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `return_items`
--
ALTER TABLE `return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `master_item_id` (`master_item_id`),
  ADD KEY `serial_number_id` (`serial_number_id`),
  ADD KEY `fk_return_items_manual` (`loan_manual_item_id`);

--
-- Indexes for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `serial_movements`
--
ALTER TABLE `serial_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `serial_number_id` (`serial_number_id`),
  ADD KEY `from_warehouse_id` (`from_warehouse_id`),
  ADD KEY `to_warehouse_id` (`to_warehouse_id`),
  ADD KEY `from_holder_id` (`from_holder_id`),
  ADD KEY `to_holder_id` (`to_holder_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `serial_numbers`
--
ALTER TABLE `serial_numbers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `master_item_id` (`master_item_id`),
  ADD KEY `current_warehouse_id` (`current_warehouse_id`),
  ADD KEY `current_location_id` (`current_location_id`),
  ADD KEY `current_holder_id` (`current_holder_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_stock` (`master_item_id`,`warehouse_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_adjustment_id` (`stock_adjustment_id`),
  ADD KEY `master_item_id` (`master_item_id`);

--
-- Indexes for table `stock_ledgers`
--
ALTER TABLE `stock_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `master_item_id` (`master_item_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `stock_opnames`
--
ALTER TABLE `stock_opnames`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `conducted_by` (`conducted_by`);

--
-- Indexes for table `stock_opname_items`
--
ALTER TABLE `stock_opname_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_opname_id` (`stock_opname_id`),
  ADD KEY `master_item_id` (`master_item_id`),
  ADD KEY `serial_number_id` (`serial_number_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `submission_code` (`submission_code`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `submission_items`
--
ALTER TABLE `submission_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`),
  ADD KEY `master_item_id` (`master_item_id`),
  ADD KEY `fk_sub_items_warehouse` (`warehouse_id`),
  ADD KEY `fk_sub_items_appr_by` (`approved_by`);

--
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `source_warehouse_id` (`source_warehouse_id`),
  ADD KEY `destination_warehouse_id` (`destination_warehouse_id`),
  ADD KEY `sent_by` (`sent_by`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `transfer_items`
--
ALTER TABLE `transfer_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transfer_id` (`transfer_id`),
  ADD KEY `master_item_id` (`master_item_id`);

--
-- Indexes for table `transfer_item_serials`
--
ALTER TABLE `transfer_item_serials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transfer_item_id` (`transfer_item_id`),
  ADD KEY `serial_number_id` (`serial_number_id`);

--
-- Indexes for table `transfer_receipts`
--
ALTER TABLE `transfer_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transfer_receipts_transfer` (`transfer_id`),
  ADD KEY `idx_transfer_receipts_received_at` (`received_at`),
  ADD KEY `fk_transfer_receipts_processed_by` (`processed_by`);

--
-- Indexes for table `transfer_receipt_item_snapshots`
--
ALTER TABLE `transfer_receipt_item_snapshots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_receipt_item_snapshot` (`receipt_id`,`transfer_item_id`),
  ADD KEY `idx_receipt_snapshot_receipt` (`receipt_id`),
  ADD KEY `idx_receipt_snapshot_transfer_item` (`transfer_item_id`),
  ADD KEY `fk_receipt_snapshot_master_item` (`master_item_id`);

--
-- Indexes for table `transfer_surat_jalan_versions`
--
ALTER TABLE `transfer_surat_jalan_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_transfer_sj_version` (`transfer_id`,`version_no`),
  ADD UNIQUE KEY `uq_transfer_sj_document_no` (`document_no`),
  ADD KEY `idx_transfer_sj_versions_transfer` (`transfer_id`,`version_no`),
  ADD KEY `fk_transfer_sj_versions_user` (`created_by`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incident_items`
--
ALTER TABLE `incident_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incoming`
--
ALTER TABLE `incoming`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incoming_items`
--
ALTER TABLE `incoming_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `installations`
--
ALTER TABLE `installations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `installation_items`
--
ALTER TABLE `installation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_categories`
--
ALTER TABLE `item_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_items`
--
ALTER TABLE `loan_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_item_serials`
--
ALTER TABLE `loan_item_serials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_manual_items`
--
ALTER TABLE `loan_manual_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_items`
--
ALTER TABLE `master_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_item_attachments`
--
ALTER TABLE `master_item_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `outgoing`
--
ALTER TABLE `outgoing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `outgoing_items`
--
ALTER TABLE `outgoing_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `serial_movements`
--
ALTER TABLE `serial_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `serial_numbers`
--
ALTER TABLE `serial_numbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_ledgers`
--
ALTER TABLE `stock_ledgers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_opnames`
--
ALTER TABLE `stock_opnames`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_opname_items`
--
ALTER TABLE `stock_opname_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submission_items`
--
ALTER TABLE `submission_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_items`
--
ALTER TABLE `transfer_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_item_serials`
--
ALTER TABLE `transfer_item_serials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_receipts`
--
ALTER TABLE `transfer_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_receipt_item_snapshots`
--
ALTER TABLE `transfer_receipt_item_snapshots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_surat_jalan_versions`
--
ALTER TABLE `transfer_surat_jalan_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3784;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `incidents`
--
ALTER TABLE `incidents`
  ADD CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `incidents_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `incident_items`
--
ALTER TABLE `incident_items`
  ADD CONSTRAINT `incident_items_ibfk_1` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `incident_items_ibfk_2` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`),
  ADD CONSTRAINT `incident_items_ibfk_3` FOREIGN KEY (`serial_number_id`) REFERENCES `serial_numbers` (`id`);

--
-- Constraints for table `incoming`
--
ALTER TABLE `incoming`
  ADD CONSTRAINT `incoming_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `incoming_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `incoming_items`
--
ALTER TABLE `incoming_items`
  ADD CONSTRAINT `incoming_items_ibfk_1` FOREIGN KEY (`incoming_id`) REFERENCES `incoming` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `incoming_items_ibfk_2` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`);

--
-- Constraints for table `installations`
--
ALTER TABLE `installations`
  ADD CONSTRAINT `installations_ibfk_1` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `installation_items`
--
ALTER TABLE `installation_items`
  ADD CONSTRAINT `installation_items_ibfk_1` FOREIGN KEY (`installation_id`) REFERENCES `installations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_items_ibfk_2` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`),
  ADD CONSTRAINT `installation_items_ibfk_3` FOREIGN KEY (`serial_number_id`) REFERENCES `serial_numbers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_loans_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_loans_handed_over_by` FOREIGN KEY (`handed_over_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `loan_items`
--
ALTER TABLE `loan_items`
  ADD CONSTRAINT `loan_items_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loan_items_ibfk_2` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`),
  ADD CONSTRAINT `loan_items_ibfk_3` FOREIGN KEY (`serial_number_id`) REFERENCES `serial_numbers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `loan_item_serials`
--
ALTER TABLE `loan_item_serials`
  ADD CONSTRAINT `loan_item_serials_ibfk_1` FOREIGN KEY (`loan_item_id`) REFERENCES `loan_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loan_item_serials_ibfk_2` FOREIGN KEY (`serial_number_id`) REFERENCES `serial_numbers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_manual_items`
--
ALTER TABLE `loan_manual_items`
  ADD CONSTRAINT `fk_loan_manual_items_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `master_items`
--
ALTER TABLE `master_items`
  ADD CONSTRAINT `master_items_ibfk_1` FOREIGN KEY (`item_category_id`) REFERENCES `item_categories` (`id`),
  ADD CONSTRAINT `master_items_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `master_item_attachments`
--
ALTER TABLE `master_item_attachments`
  ADD CONSTRAINT `master_item_attachments_ibfk_1` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `master_item_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `outgoing`
--
ALTER TABLE `outgoing`
  ADD CONSTRAINT `outgoing_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `outgoing_ibfk_2` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`),
  ADD CONSTRAINT `outgoing_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `outgoing_items`
--
ALTER TABLE `outgoing_items`
  ADD CONSTRAINT `outgoing_items_ibfk_1` FOREIGN KEY (`outgoing_id`) REFERENCES `outgoing` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `outgoing_items_ibfk_2` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`);

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `return_items`
--
ALTER TABLE `return_items`
  ADD CONSTRAINT `fk_return_items_manual` FOREIGN KEY (`loan_manual_item_id`) REFERENCES `loan_manual_items` (`id`),
  ADD CONSTRAINT `return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `return_items_ibfk_2` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`),
  ADD CONSTRAINT `return_items_ibfk_3` FOREIGN KEY (`serial_number_id`) REFERENCES `serial_numbers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `serial_movements`
--
ALTER TABLE `serial_movements`
  ADD CONSTRAINT `serial_movements_ibfk_1` FOREIGN KEY (`serial_number_id`) REFERENCES `serial_numbers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `serial_movements_ibfk_2` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `serial_movements_ibfk_3` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `serial_movements_ibfk_4` FOREIGN KEY (`from_holder_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `serial_movements_ibfk_5` FOREIGN KEY (`to_holder_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `serial_movements_ibfk_6` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `serial_numbers`
--
ALTER TABLE `serial_numbers`
  ADD CONSTRAINT `serial_numbers_ibfk_1` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`),
  ADD CONSTRAINT `serial_numbers_ibfk_2` FOREIGN KEY (`current_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `serial_numbers_ibfk_3` FOREIGN KEY (`current_location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `serial_numbers_ibfk_4` FOREIGN KEY (`current_holder_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`),
  ADD CONSTRAINT `stocks_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD CONSTRAINT `stock_adjustments_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `stock_adjustments_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  ADD CONSTRAINT `stock_adjustment_items_ibfk_1` FOREIGN KEY (`stock_adjustment_id`) REFERENCES `stock_adjustments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_items_ibfk_2` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`);

--
-- Constraints for table `stock_ledgers`
--
ALTER TABLE `stock_ledgers`
  ADD CONSTRAINT `stock_ledgers_ibfk_1` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`),
  ADD CONSTRAINT `stock_ledgers_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `stock_ledgers_ibfk_3` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `stock_opnames`
--
ALTER TABLE `stock_opnames`
  ADD CONSTRAINT `stock_opnames_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `stock_opnames_ibfk_2` FOREIGN KEY (`conducted_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `stock_opname_items`
--
ALTER TABLE `stock_opname_items`
  ADD CONSTRAINT `stock_opname_items_ibfk_1` FOREIGN KEY (`stock_opname_id`) REFERENCES `stock_opnames` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_opname_items_ibfk_2` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`),
  ADD CONSTRAINT `stock_opname_items_ibfk_3` FOREIGN KEY (`serial_number_id`) REFERENCES `serial_numbers` (`id`);

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `submission_items`
--
ALTER TABLE `submission_items`
  ADD CONSTRAINT `fk_sub_items_appr_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sub_items_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `submission_items_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submission_items_ibfk_2` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`);

--
-- Constraints for table `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_ibfk_1` FOREIGN KEY (`source_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `transfers_ibfk_2` FOREIGN KEY (`destination_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `transfers_ibfk_3` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transfers_ibfk_4` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `transfer_items`
--
ALTER TABLE `transfer_items`
  ADD CONSTRAINT `transfer_items_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `transfers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfer_items_ibfk_2` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`);

--
-- Constraints for table `transfer_item_serials`
--
ALTER TABLE `transfer_item_serials`
  ADD CONSTRAINT `transfer_item_serials_ibfk_1` FOREIGN KEY (`transfer_item_id`) REFERENCES `transfer_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfer_item_serials_ibfk_2` FOREIGN KEY (`serial_number_id`) REFERENCES `serial_numbers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transfer_receipts`
--
ALTER TABLE `transfer_receipts`
  ADD CONSTRAINT `fk_transfer_receipts_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transfer_receipts_transfer` FOREIGN KEY (`transfer_id`) REFERENCES `transfers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transfer_receipt_item_snapshots`
--
ALTER TABLE `transfer_receipt_item_snapshots`
  ADD CONSTRAINT `fk_receipt_snapshot_master_item` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`),
  ADD CONSTRAINT `fk_receipt_snapshot_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `transfer_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_receipt_snapshot_transfer_item` FOREIGN KEY (`transfer_item_id`) REFERENCES `transfer_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transfer_surat_jalan_versions`
--
ALTER TABLE `transfer_surat_jalan_versions`
  ADD CONSTRAINT `fk_transfer_sj_versions_transfer` FOREIGN KEY (`transfer_id`) REFERENCES `transfers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_transfer_sj_versions_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
