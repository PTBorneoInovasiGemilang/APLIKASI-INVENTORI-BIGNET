-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2026 at 04:36 PM
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

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity`, `entity_id`, `old_data`, `new_data`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3382, 'CREATE', 'item_categories', 112, NULL, NULL, NULL, NULL, '2026-08-19 08:20:23'),
(2, 3382, 'CREATE', 'units', 66, NULL, NULL, NULL, NULL, '2026-08-19 08:20:36'),
(3, 3382, 'CREATE', 'warehouses', 58, NULL, NULL, NULL, NULL, '2026-08-19 08:20:50'),
(4, 3382, 'CREATE', 'locations', 32, NULL, NULL, NULL, NULL, '2026-08-19 08:22:55'),
(5, 3382, 'CREATE', 'master_items', 189, NULL, NULL, NULL, NULL, '2026-08-19 08:23:22'),
(6, 3382, 'CREATE', 'master_items', 190, NULL, NULL, NULL, NULL, '2026-08-19 08:23:41'),
(7, 3382, 'CREATE', 'incoming', 45, NULL, NULL, NULL, NULL, '2026-08-19 08:27:27'),
(8, 3382, 'CREATE', 'incoming', 46, NULL, NULL, NULL, NULL, '2026-08-19 08:27:54'),
(9, 3646, 'CREATE', 'submissions', 35, NULL, NULL, NULL, NULL, '2026-08-19 08:30:34'),
(10, 3382, 'APPROVE', 'submissions', 35, NULL, '{\"status\":\"APPROVED\"}', NULL, NULL, '2026-08-19 10:37:28'),
(11, 3382, 'CREATE', 'outgoing', 1, NULL, NULL, NULL, NULL, '2026-08-19 11:05:38'),
(12, 3646, 'CREATE', 'installations', 2, NULL, NULL, NULL, NULL, '2026-08-19 12:04:18'),
(13, 3646, 'CREATE', 'loans', 2, NULL, NULL, NULL, NULL, '2026-08-19 12:20:52');

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

--
-- Dumping data for table `incoming`
--

INSERT INTO `incoming` (`id`, `transaction_code`, `warehouse_id`, `received_by`, `supplier_info`, `notes`, `status`, `created_at`) VALUES
(45, 'INC-20260819-0001', 58, 3382, '', '', 'COMPLETED', '2026-08-19 08:27:27'),
(46, 'INC-20260819-0002', 58, 3382, '', '', 'COMPLETED', '2026-08-19 08:27:54');

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

--
-- Dumping data for table `incoming_items`
--

INSERT INTO `incoming_items` (`id`, `incoming_id`, `master_item_id`, `quantity`) VALUES
(43, 45, 189, 5),
(44, 46, 190, 3);

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

--
-- Dumping data for table `installations`
--

INSERT INTO `installations` (`id`, `transaction_code`, `technician_id`, `customer_name`, `customer_site`, `status`, `reference_code`, `notes`, `photo_path`, `installed_at`) VALUES
(2, 'INS-20260819-0001', 3646, 'KONTOL', 'KONTOL - SDFSDF', 'INSTALLED', '', '', 'installations/inst_6a859bc26406c.jpg', '2026-08-19 12:04:18');

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

--
-- Dumping data for table `installation_items`
--

INSERT INTO `installation_items` (`id`, `installation_id`, `master_item_id`, `serial_number_id`, `quantity`) VALUES
(1, 2, 189, 1, 1);

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

--
-- Dumping data for table `item_categories`
--

INSERT INTO `item_categories` (`id`, `code`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(112, 'KAT-000001', 'MODEM', '', 1, '2026-08-19 08:20:23', '2026-08-19 08:20:23');

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

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `transaction_code`, `borrower_id`, `warehouse_id`, `submission_id`, `loan_type`, `loan_date`, `processed_by`, `approved_by`, `approved_at`, `approval_notes`, `handed_over_by`, `handed_over_at`, `status`, `notes`, `due_date`, `created_at`) VALUES
(2, 'LOAN-20260819-0001', 3646, 58, NULL, 'INTERNAL', '2026-08-19', 3646, NULL, NULL, NULL, NULL, NULL, 'BORROWED', '', '2026-08-20', '2026-08-19 12:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `loan_items`
--

CREATE TABLE `loan_items` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `master_item_id` int(11) NOT NULL,
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
  `loan_item_id` int(11) NOT NULL,
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
  `specification` text DEFAULT NULL,
  `unit` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `returned_quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_manual_items`
--

INSERT INTO `loan_manual_items` (`id`, `loan_id`, `name`, `specification`, `unit`, `quantity`, `returned_quantity`, `created_at`) VALUES
(1, 2, 'kondom', NULL, NULL, 1, 0, '2026-08-19 12:20:52');

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

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `warehouse_id`, `code`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(32, 58, 'LOK-000001', 'RUANG GUDANG', '', 1, '2026-08-19 08:22:55', '2026-08-19 08:22:55');

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

--
-- Dumping data for table `master_items`
--

INSERT INTO `master_items` (`id`, `kode_barang`, `nama`, `item_category_id`, `unit_id`, `is_serialized`, `stok_minimum`, `deskripsi`, `is_active`, `created_at`, `updated_at`) VALUES
(189, 'KAT-000001-000001', 'MODEM TRIKOM', 112, 66, 1, 100, '', 1, '2026-08-19 08:23:22', '2026-08-19 08:23:22'),
(190, 'KAT-000001-000002', 'MODEM ZTE', 112, 66, 1, 100, '', 1, '2026-08-19 08:23:41', '2026-08-19 08:23:41');

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

--
-- Dumping data for table `master_item_attachments`
--

INSERT INTO `master_item_attachments` (`id`, `master_item_id`, `file_path`, `original_name`, `mime_type`, `file_size`, `sort_order`, `uploaded_by`, `created_at`) VALUES
(5, 189, 'items/item_6a8567faa2dfe.jpg', '46144262_1751124028331092_7923117258321166336_n.jpg', 'image/jpeg', 220251, 0, 3382, '2026-08-19 08:23:22'),
(6, 190, 'items/item_6a85680d14af4.jpg', 'photo_2025-04-09_14-48-52.jpg', 'image/jpeg', 148045, 0, 3382, '2026-08-19 08:23:41');

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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `source_route`, `read_at`, `created_at`) VALUES
(338, 3382, 'Pengajuan Barang Baru', 'TOMMY RYAN DWIPUTRA mengajukan 2 MODEM TRIKOM (REQ-20260819-0001).', 'info', '/submissions/show?id=35', NULL, '2026-08-19 15:30:34'),
(339, 3646, 'Pengajuan Disetujui', 'Pengajuan REQ-20260819-0001 telah disetujui oleh Admin.', 'success', '/submissions/show?id=35', NULL, '2026-08-19 17:37:28'),
(340, 3382, 'Instalasi Baru Dicatat', 'TOMMY RYAN DWIPUTRA mencatat instalasi INS-20260819-0001 untuk KONTOL.', 'info', '/installations/show?id=2', NULL, '2026-08-19 19:04:18'),
(341, 3382, 'Peminjaman Barang Baru', 'TOMMY RYAN DWIPUTRA mengajukan peminjaman.', 'info', '/loans/show?id=2', NULL, '2026-08-19 19:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `outgoing`
--

CREATE TABLE `outgoing` (
  `id` int(11) NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `submission_id` int(11) DEFAULT NULL,
  `processed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'COMPLETED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outgoing`
--

INSERT INTO `outgoing` (`id`, `transaction_code`, `warehouse_id`, `submission_id`, `processed_by`, `notes`, `status`, `created_at`) VALUES
(1, 'OUT-20260819-0001', 58, 35, 3382, '', 'COMPLETED', '2026-08-19 11:05:38');

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

--
-- Dumping data for table `outgoing_items`
--

INSERT INTO `outgoing_items` (`id`, `outgoing_id`, `master_item_id`, `submission_item_id`, `quantity`) VALUES
(1, 1, 189, 1, 2);

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

--
-- Dumping data for table `schema_migrations`
--

INSERT INTO `schema_migrations` (`id`, `migration`, `created_at`) VALUES
(1, '001_create_users_table.php', '2026-08-14 14:56:16'),
(2, '002_create_master_tables.php', '2026-08-14 14:56:16'),
(3, '003_create_master_items_table.php', '2026-08-14 14:56:16'),
(4, '004_create_master_item_attachments_table.php', '2026-08-14 14:56:16'),
(5, '005_create_stocks_and_ledgers_tables.php', '2026-08-14 14:56:16'),
(6, '006_create_serial_numbers_and_movements_tables.php', '2026-08-14 14:56:16'),
(7, '007_create_transactions_tables.php', '2026-08-14 14:56:16'),
(8, '008_create_audit_logs_table.php', '2026-08-14 14:56:16'),
(9, '009_create_attachments_table.php', '2026-08-14 14:56:16'),
(10, '010_update_schema.php', '2026-08-15 04:50:31');

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

--
-- Dumping data for table `serial_movements`
--

INSERT INTO `serial_movements` (`id`, `serial_number_id`, `from_status`, `to_status`, `from_warehouse_id`, `to_warehouse_id`, `from_holder_id`, `to_holder_id`, `reference_type`, `reference_id`, `performed_by`, `notes`, `created_at`) VALUES
(1, 1, NULL, 'AVAILABLE', NULL, 58, NULL, NULL, 'incoming', 45, 3382, 'Barang masuk - INC-20260819-0001', '2026-08-19 08:27:27'),
(2, 2, NULL, 'AVAILABLE', NULL, 58, NULL, NULL, 'incoming', 45, 3382, 'Barang masuk - INC-20260819-0001', '2026-08-19 08:27:27'),
(3, 3, NULL, 'AVAILABLE', NULL, 58, NULL, NULL, 'incoming', 45, 3382, 'Barang masuk - INC-20260819-0001', '2026-08-19 08:27:27'),
(4, 4, NULL, 'AVAILABLE', NULL, 58, NULL, NULL, 'incoming', 45, 3382, 'Barang masuk - INC-20260819-0001', '2026-08-19 08:27:27'),
(5, 5, NULL, 'AVAILABLE', NULL, 58, NULL, NULL, 'incoming', 45, 3382, 'Barang masuk - INC-20260819-0001', '2026-08-19 08:27:27'),
(6, 6, NULL, 'AVAILABLE', NULL, 58, NULL, NULL, 'incoming', 46, 3382, 'Barang masuk - INC-20260819-0002', '2026-08-19 08:27:54'),
(7, 7, NULL, 'AVAILABLE', NULL, 58, NULL, NULL, 'incoming', 46, 3382, 'Barang masuk - INC-20260819-0002', '2026-08-19 08:27:54'),
(8, 8, NULL, 'AVAILABLE', NULL, 58, NULL, NULL, 'incoming', 46, 3382, 'Barang masuk - INC-20260819-0002', '2026-08-19 08:27:54'),
(9, 1, 'AVAILABLE', 'ISSUED', 58, NULL, NULL, 3646, 'outgoing', 1, 3382, 'Barang keluar - OUT-20260819-0001', '2026-08-19 11:05:38'),
(10, 2, 'AVAILABLE', 'ISSUED', 58, NULL, NULL, 3646, 'outgoing', 1, 3382, 'Barang keluar - OUT-20260819-0001', '2026-08-19 11:05:38'),
(11, 1, 'ISSUED', 'INSTALLED', NULL, NULL, 3646, NULL, 'installation', 2, 3646, 'Instalasi - INS-20260819-0001', '2026-08-19 12:04:18');

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

--
-- Dumping data for table `serial_numbers`
--

INSERT INTO `serial_numbers` (`id`, `master_item_id`, `serial_number`, `status`, `current_warehouse_id`, `current_location_id`, `current_holder_id`, `condition_status`, `created_at`, `updated_at`) VALUES
(1, 189, 'SN01', 'INSTALLED', NULL, NULL, NULL, 'GOOD', '2026-08-19 08:27:27', '2026-08-19 12:04:18'),
(2, 189, 'SN02', 'ISSUED', NULL, NULL, 3646, 'GOOD', '2026-08-19 08:27:27', '2026-08-19 11:05:38'),
(3, 189, 'SN03', 'AVAILABLE', 58, NULL, NULL, 'GOOD', '2026-08-19 08:27:27', '2026-08-19 08:27:27'),
(4, 189, 'SN04', 'AVAILABLE', 58, NULL, NULL, 'GOOD', '2026-08-19 08:27:27', '2026-08-19 08:27:27'),
(5, 189, 'SN05', 'AVAILABLE', 58, NULL, NULL, 'GOOD', '2026-08-19 08:27:27', '2026-08-19 08:27:27'),
(6, 190, 'PPK01', 'AVAILABLE', 58, NULL, NULL, 'GOOD', '2026-08-19 08:27:54', '2026-08-19 08:27:54'),
(7, 190, 'PPK02', 'AVAILABLE', 58, NULL, NULL, 'GOOD', '2026-08-19 08:27:54', '2026-08-19 08:27:54'),
(8, 190, 'PPK03', 'AVAILABLE', 58, NULL, NULL, 'GOOD', '2026-08-19 08:27:54', '2026-08-19 08:27:54');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`key`, `value`, `updated_at`) VALUES
('app_name', 'APLIKASI PEPEK', '2026-08-18 08:04:07'),
('app_short_name', 'BIG', '2026-08-14 18:17:54'),
('company_name', 'PT BORNEO INOVASI GEMILANG', '2026-08-18 08:04:07'),
('default_theme', 'light', '2026-08-14 18:18:26'),
('favicon_path', 'favicon_b1219910bd827bd7.png', '2026-08-16 22:33:19'),
('footer_text', '© 2026 PT. BIG. All rights reserved.', '2026-08-14 18:17:54'),
('login_subtitle', 'Sign in to your account', '2026-08-14 18:17:54'),
('login_title', 'GUDANG BIGNET', '2026-08-15 08:05:25'),
('logo_path', 'logo_aeab9d90f5a913ac.jpg', '2026-08-18 08:04:09'),
('primary_color', 'blue', '2026-08-15 07:42:37'),
('sidebar_theme', 'dark', '2026-08-15 07:58:01');

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

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`id`, `master_item_id`, `warehouse_id`, `quantity`, `updated_at`) VALUES
(57, 189, 58, 3, '2026-08-19 11:05:38'),
(58, 190, 58, 3, '2026-08-19 08:27:54');

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

--
-- Dumping data for table `stock_ledgers`
--

INSERT INTO `stock_ledgers` (`id`, `master_item_id`, `warehouse_id`, `transaction_type`, `reference_type`, `reference_id`, `quantity_delta`, `balance_before`, `balance_after`, `performed_by`, `notes`, `created_at`) VALUES
(98, 189, 58, 'INCOMING', 'incoming', 45, 5, 0, 5, 3382, 'Incoming INC-20260819-0001', '2026-08-19 08:27:27'),
(99, 190, 58, 'INCOMING', 'incoming', 46, 3, 0, 3, 3382, 'Incoming INC-20260819-0002', '2026-08-19 08:27:54'),
(100, 189, 58, 'OUTGOING', 'outgoing', 1, -2, 5, 3, 3382, 'Fulfillment outgoing OUT-20260819-0001', '2026-08-19 11:05:38');

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

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `submission_code`, `submitted_by`, `submission_type`, `status`, `notes`, `approved_by`, `approved_at`, `approval_notes`, `created_at`, `updated_at`) VALUES
(35, 'REQ-20260819-0001', 3646, 'STOCK', 'COMPLETED', '', 3382, '2026-08-19 10:37:28', '', '2026-08-19 08:30:34', '2026-08-19 11:05:38');

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

--
-- Dumping data for table `submission_items`
--

INSERT INTO `submission_items` (`id`, `submission_id`, `source_type`, `warehouse_id`, `master_item_id`, `manual_name`, `manual_spec`, `manual_unit`, `requested_quantity`, `approved_quantity`, `fulfilled_quantity`, `remaining_quantity`, `status`, `rejection_reason`, `processing_note`, `approved_by`, `approved_at`, `notes`) VALUES
(1, 35, 'STOCK', 58, 189, NULL, NULL, NULL, 2, 2, 2, 0, 'APPROVED', '', NULL, 3382, '2026-08-19 17:37:28', '');

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
  `received_by` int(11) DEFAULT NULL,
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

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `code`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(66, 'UNT-000001', 'UNIT', '', 1, '2026-08-19 08:20:36', '2026-08-19 08:20:36');

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
(1, 'Super Admin', 'superadmin@ptbig.com', '$2y$10$SpNVJGcdsfjB1TaTbn7r.uV3Kl3FNc/9XquLsLbcGMY77q2C/vz/W', 'SUPERADMIN', 'APPROVED', 'superadmin@ptbig.com', 'profiles/profile_1_1786962425.jpg', NULL, 1, '2026-08-19 08:19:37', '2026-08-15 05:13:46', '2026-08-19 08:19:37'),
(3382, 'MARIA TESA', 'mariatsa035@gmail.com', '$2y$10$.FS8Zrrftb8r4wxErBaUEuKuEvumDxAgn0ZpUUx52puLY7aKw1qru', 'ADMIN', 'APPROVED', '', 'profiles/profile_6a82fae69864e.jpg', NULL, 1, '2026-08-19 14:35:27', '2026-08-17 12:13:26', '2026-08-19 14:35:27'),
(3646, 'TOMMY RYAN DWIPUTRA', 'kontol@gmail.com', '$2y$10$RIYig5nUW4PnbFaRKpO1rOWvjtIBG7t7eCuChf8T6sP8tHfmYcpjG', 'TEKNISI_NOC', 'APPROVED', '', NULL, NULL, 1, '2026-08-19 11:36:07', '2026-08-17 13:16:04', '2026-08-19 11:36:07'),
(3743, 'Notif Register', 'notif_reg_1787040250429@ptbig.com', '$2y$10$zCqspt.M4tG6E.5yocuNs.9LubzDufTEgutO67z/ui1MAfQnwaU9a', 'TEKNISI_NOC', 'PENDING_APPROVAL', '0812', 'profiles/profile_6a8411fb39af1.png', NULL, 0, NULL, '2026-08-18 08:04:11', '2026-08-18 08:04:11');

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
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `code`, `name`, `address`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(58, 'GDG-000001', 'GUDANG 28 OKTOBER', 'PONTIANAK', '', 1, '2026-08-19 08:20:50', '2026-08-19 08:20:50');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `incident_items`
--
ALTER TABLE `incident_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `incoming`
--
ALTER TABLE `incoming`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `incoming_items`
--
ALTER TABLE `incoming_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `installations`
--
ALTER TABLE `installations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `installation_items`
--
ALTER TABLE `installation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `item_categories`
--
ALTER TABLE `item_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `master_items`
--
ALTER TABLE `master_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;

--
-- AUTO_INCREMENT for table `master_item_attachments`
--
ALTER TABLE `master_item_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=342;

--
-- AUTO_INCREMENT for table `outgoing`
--
ALTER TABLE `outgoing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `outgoing_items`
--
ALTER TABLE `outgoing_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `serial_numbers`
--
ALTER TABLE `serial_numbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `submission_items`
--
ALTER TABLE `submission_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3778;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
