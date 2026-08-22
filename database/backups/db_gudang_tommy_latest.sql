-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2026 at 06:26 PM
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

--
-- Dumping data for table `attachments`
--

INSERT INTO `attachments` (`id`, `reference_type`, `reference_id`, `file_path`, `original_name`, `mime_type`, `file_size`, `uploaded_by`, `created_at`) VALUES
(2, 'transfer_dispatch', 2, 'transfers/2/transfer_dispatch_1787218221_02a69619.jpg', 'WhatsApp Image 2025-07-17 at 22.21.39_a058701c.jpg', 'image/jpeg', 63605, 3382, '2026-08-20 09:30:21'),
(3, 'transfer_receive', 2, 'transfers/2/transfer_receive_1787219292_810e3a1c.jpg', 'WhatsApp Image 2025-05-18 at 20.32.40_e236389c.jpg', 'image/jpeg', 1259967, 3382, '2026-08-20 09:48:12'),
(4, 'transfer_dispatch', 3, 'transfers/3/transfer_dispatch_1787321954_b94ab847.jpg', 'WhatsApp Image 2026-08-21 at 19.37.48.jpeg', 'image/jpeg', 140331, 3382, '2026-08-21 14:19:14'),
(5, 'transfer_receive', 3, 'transfers/3/transfer_receive_1787322018_a7e9bc9e.png', 'BigNet-Color Logo (1).png', 'image/png', 457476, 3382, '2026-08-21 14:20:18'),
(6, 'transfer_dispatch', 4, 'transfers/4/transfer_dispatch_1787325795_5ae5994b.png', 'BigNet-Color Logo (1).png', 'image/png', 457476, 3382, '2026-08-21 15:23:15'),
(7, 'transfer_receive', 4, 'transfers/4/transfer_receive_1787326051_24c29217.jpg', 'WhatsApp Image 2026-08-21 at 19.37.48.jpeg', 'image/jpeg', 140331, 3382, '2026-08-21 15:27:31'),
(8, 'transfer_receive', 3, 'transfers/3/transfer_receive_1787415080_3407f2a0.png', 'BigNet-Color Logo (1).png', 'image/png', 457476, 3382, '2026-08-22 16:11:20');

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
(27, 3382, 'CREATE', 'item_categories', 113, NULL, NULL, NULL, NULL, '2026-08-20 09:22:01'),
(28, 3382, 'CREATE', 'item_categories', 114, NULL, NULL, NULL, NULL, '2026-08-20 09:22:07'),
(29, 3382, 'CREATE', 'item_categories', 115, NULL, NULL, NULL, NULL, '2026-08-20 09:22:22'),
(30, 3382, 'CREATE', 'warehouses', 60, NULL, NULL, NULL, NULL, '2026-08-20 09:22:39'),
(31, 3382, 'CREATE', 'warehouses', 61, NULL, NULL, NULL, NULL, '2026-08-20 09:22:46'),
(32, 3382, 'CREATE', 'units', 67, NULL, NULL, NULL, NULL, '2026-08-20 09:22:54'),
(33, 3382, 'CREATE', 'locations', 33, NULL, NULL, NULL, NULL, '2026-08-20 09:23:04'),
(34, 3382, 'CREATE', 'locations', 34, NULL, NULL, NULL, NULL, '2026-08-20 09:23:12'),
(35, 3382, 'CREATE', 'master_items', 191, NULL, NULL, NULL, NULL, '2026-08-20 09:23:54'),
(36, 3382, 'CREATE', 'master_items', 192, NULL, NULL, NULL, NULL, '2026-08-20 09:24:22'),
(37, 3382, 'CREATE', 'master_items', 193, NULL, NULL, NULL, NULL, '2026-08-20 09:24:57'),
(38, 3382, 'CREATE', 'incoming', 47, NULL, NULL, NULL, NULL, '2026-08-20 09:26:16'),
(39, 3382, 'CREATE', 'incoming', 48, NULL, NULL, NULL, NULL, '2026-08-20 09:26:33'),
(40, 3382, 'CREATE', 'incoming', 49, NULL, NULL, NULL, NULL, '2026-08-20 09:26:45'),
(41, 3382, 'CREATE', 'transfers', 2, NULL, '{\"transaction_code\":\"TRF-20260820-0001\"}', NULL, NULL, '2026-08-20 09:30:21'),
(42, 3382, 'RECEIVE_TRANSFER', 'transfers', 2, NULL, '{\"photo\":true,\"status_after\":\"COMPLETED\"}', NULL, NULL, '2026-08-20 09:48:12'),
(43, 3382, 'CREATE', 'transfers', 3, NULL, '{\"transaction_code\":\"TRF-20260821-0001\"}', NULL, NULL, '2026-08-21 14:19:14'),
(44, 3382, 'RECEIVE_TRANSFER', 'transfers', 3, NULL, '{\"photo\":true,\"status_after\":\"PARTIAL_RECEIVED\"}', NULL, NULL, '2026-08-21 14:20:18'),
(45, 3382, 'CREATE', 'transfers', 4, NULL, '{\"transaction_code\":\"TRF-20260821-0002\",\"driver_name\":\"fghfghf\",\"driver_phone\":\"085156984933\",\"vehicle_number\":\"KB 5400\"}', NULL, NULL, '2026-08-21 15:23:15'),
(46, 3382, 'RECEIVE_TRANSFER', 'transfers', 4, NULL, '{\"receiver_name\":\"fghfghgfh\",\"receiver_role\":\"sgsgdsf\",\"receiver_phone\":\"0898343434346\",\"status_after\":\"COMPLETED\",\"photo\":true}', NULL, NULL, '2026-08-21 15:27:31'),
(47, 3382, 'RECEIVE_TRANSFER', 'transfers', 3, NULL, '{\"receiver_name\":\"RONI\",\"receiver_role\":\"ASDSAD\",\"receiver_phone\":\"0898343434346\",\"status_after\":\"COMPLETED\",\"photo\":true}', NULL, NULL, '2026-08-22 16:11:20');

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
(47, 'INC-20260820-0001', 60, 3382, '', '', 'COMPLETED', '2026-08-20 09:26:16'),
(48, 'INC-20260820-0002', 60, 3382, '', '', 'COMPLETED', '2026-08-20 09:26:33'),
(49, 'INC-20260820-0003', 60, 3382, '', '', 'COMPLETED', '2026-08-20 09:26:45');

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
(45, 47, 191, 50),
(46, 48, 192, 20),
(47, 49, 193, 20);

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

--
-- Dumping data for table `item_categories`
--

INSERT INTO `item_categories` (`id`, `code`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(113, 'KAT-000001', 'ODP', '', 1, '2026-08-20 09:22:01', '2026-08-20 09:22:01'),
(114, 'KAT-000002', 'ODC', '', 1, '2026-08-20 09:22:07', '2026-08-20 09:22:07'),
(115, 'KAT-000003', 'MODEM', '', 1, '2026-08-20 09:22:22', '2026-08-20 09:22:22');

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

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `warehouse_id`, `code`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(33, 60, 'LOK-000001', 'RUANG GUDANG', '', 1, '2026-08-20 09:23:04', '2026-08-20 09:23:04'),
(34, 61, 'LOK-000002', 'RUANG GUDANG', '', 1, '2026-08-20 09:23:12', '2026-08-20 09:23:12');

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
(191, 'KAT-000003-000001', 'MODEM TRIKOM', 115, 67, 1, 100, '', 1, '2026-08-20 09:23:54', '2026-08-20 09:23:54'),
(192, 'KAT-000001-000001', 'ODP TARMOC', 113, 67, 0, 0, '', 1, '2026-08-20 09:24:22', '2026-08-20 09:24:22'),
(193, 'KAT-000002-000001', 'ODC TARMOC', 114, 67, 0, 100, '', 1, '2026-08-20 09:24:57', '2026-08-20 09:24:57');

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
(7, 191, 'items/item_6a86c7aa6248a.jpg', '46144262_1751124028331092_7923117258321166336_n.jpg', 'image/jpeg', 220251, 0, 3382, '2026-08-20 09:23:54'),
(8, 192, 'items/item_6a86c7c67c780.jpg', 'wallpaperflare.com_wallpaper (1).jpg', 'image/jpeg', 821366, 0, 3382, '2026-08-20 09:24:22'),
(9, 193, 'items/item_6a86c7e908b8c.jpg', 'anime-girl-white-hair-hd-wallpaper-uhdpaper.com-220@5@p.jpg', 'image/jpeg', 329131, 0, 3382, '2026-08-20 09:24:57');

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

--
-- Dumping data for table `serial_movements`
--

INSERT INTO `serial_movements` (`id`, `serial_number_id`, `from_status`, `to_status`, `from_warehouse_id`, `to_warehouse_id`, `from_holder_id`, `to_holder_id`, `reference_type`, `reference_id`, `performed_by`, `notes`, `created_at`) VALUES
(16, 9, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(17, 10, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(18, 11, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(19, 12, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(20, 13, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(21, 14, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(22, 15, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(23, 16, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(24, 17, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(25, 18, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(26, 19, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(27, 20, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(28, 21, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(29, 22, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(30, 23, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(31, 24, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(32, 25, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(33, 26, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(34, 27, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(35, 28, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(36, 29, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(37, 30, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(38, 31, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(39, 32, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(40, 33, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(41, 34, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(42, 35, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(43, 36, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(44, 37, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(45, 38, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(46, 39, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(47, 40, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(48, 41, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(49, 42, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(50, 43, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(51, 44, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(52, 45, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(53, 46, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(54, 47, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(55, 48, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(56, 49, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(57, 50, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(58, 51, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(59, 52, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(60, 53, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(61, 54, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(62, 55, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(63, 56, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(64, 57, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(65, 58, NULL, 'AVAILABLE', NULL, 60, NULL, NULL, 'incoming', 47, 3382, 'Barang masuk - INC-20260820-0001', '2026-08-20 09:26:16'),
(66, 9, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 2, 3382, 'Transfer Keluar - TRF-20260820-0001', '2026-08-20 09:30:21'),
(67, 10, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 2, 3382, 'Transfer Keluar - TRF-20260820-0001', '2026-08-20 09:30:21'),
(68, 11, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 2, 3382, 'Transfer Keluar - TRF-20260820-0001', '2026-08-20 09:30:21'),
(69, 12, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 2, 3382, 'Transfer Keluar - TRF-20260820-0001', '2026-08-20 09:30:21'),
(70, 13, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 2, 3382, 'Transfer Keluar - TRF-20260820-0001', '2026-08-20 09:30:21'),
(71, 14, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 2, 3382, 'Transfer Keluar - TRF-20260820-0001', '2026-08-20 09:30:21'),
(72, 15, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 2, 3382, 'Transfer Keluar - TRF-20260820-0001', '2026-08-20 09:30:21'),
(73, 16, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 2, 3382, 'Transfer Keluar - TRF-20260820-0001', '2026-08-20 09:30:21'),
(74, 17, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 2, 3382, 'Transfer Keluar - TRF-20260820-0001', '2026-08-20 09:30:21'),
(75, 18, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 2, 3382, 'Transfer Keluar - TRF-20260820-0001', '2026-08-20 09:30:21'),
(76, 9, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 2, 3382, 'Transfer Diterima - TRF-20260820-0001', '2026-08-20 09:48:12'),
(77, 10, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 2, 3382, 'Transfer Diterima - TRF-20260820-0001', '2026-08-20 09:48:12'),
(78, 11, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 2, 3382, 'Transfer Diterima - TRF-20260820-0001', '2026-08-20 09:48:12'),
(79, 12, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 2, 3382, 'Transfer Diterima - TRF-20260820-0001', '2026-08-20 09:48:12'),
(80, 13, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 2, 3382, 'Transfer Diterima - TRF-20260820-0001', '2026-08-20 09:48:12'),
(81, 14, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 2, 3382, 'Transfer Diterima - TRF-20260820-0001', '2026-08-20 09:48:12'),
(82, 15, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 2, 3382, 'Transfer Diterima - TRF-20260820-0001', '2026-08-20 09:48:12'),
(83, 16, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 2, 3382, 'Transfer Diterima - TRF-20260820-0001', '2026-08-20 09:48:12'),
(84, 17, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 2, 3382, 'Transfer Diterima - TRF-20260820-0001', '2026-08-20 09:48:12'),
(85, 18, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 2, 3382, 'Transfer Diterima - TRF-20260820-0001', '2026-08-20 09:48:12'),
(86, 19, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 3, 3382, 'Transfer Keluar - TRF-20260821-0001', '2026-08-21 14:19:14'),
(87, 20, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 3, 3382, 'Transfer Keluar - TRF-20260821-0001', '2026-08-21 14:19:14'),
(88, 21, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 3, 3382, 'Transfer Keluar - TRF-20260821-0001', '2026-08-21 14:19:14'),
(89, 22, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 3, 3382, 'Transfer Keluar - TRF-20260821-0001', '2026-08-21 14:19:14'),
(90, 23, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 3, 3382, 'Transfer Keluar - TRF-20260821-0001', '2026-08-21 14:19:14'),
(91, 24, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 3, 3382, 'Transfer Keluar - TRF-20260821-0001', '2026-08-21 14:19:14'),
(92, 25, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 3, 3382, 'Transfer Keluar - TRF-20260821-0001', '2026-08-21 14:19:14'),
(93, 26, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 3, 3382, 'Transfer Keluar - TRF-20260821-0001', '2026-08-21 14:19:14'),
(94, 27, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 3, 3382, 'Transfer Keluar - TRF-20260821-0001', '2026-08-21 14:19:14'),
(95, 28, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 3, 3382, 'Transfer Keluar - TRF-20260821-0001', '2026-08-21 14:19:14'),
(96, 19, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 3, 3382, 'Transfer Diterima - TRF-20260821-0001', '2026-08-21 14:20:18'),
(97, 20, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 3, 3382, 'Transfer Diterima - TRF-20260821-0001', '2026-08-21 14:20:18'),
(98, 21, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 3, 3382, 'Transfer Diterima - TRF-20260821-0001', '2026-08-21 14:20:18'),
(99, 22, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 3, 3382, 'Transfer Diterima - TRF-20260821-0001', '2026-08-21 14:20:18'),
(100, 23, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 3, 3382, 'Transfer Diterima - TRF-20260821-0001', '2026-08-21 14:20:18'),
(101, 24, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 3, 3382, 'Transfer Diterima - TRF-20260821-0001', '2026-08-21 14:20:18'),
(102, 25, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 3, 3382, 'Transfer Diterima - TRF-20260821-0001', '2026-08-21 14:20:18'),
(103, 29, 'AVAILABLE', 'IN_TRANSIT', 60, NULL, NULL, NULL, 'transfer', 4, 3382, 'Transfer Keluar - TRF-20260821-0002', '2026-08-21 15:23:15'),
(104, 29, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 4, 3382, 'Transfer Diterima - TRF-20260821-0002', '2026-08-21 15:27:31'),
(105, 26, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 3, 3382, 'Transfer Diterima - TRF-20260821-0001', '2026-08-22 16:11:20'),
(106, 27, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 3, 3382, 'Transfer Diterima - TRF-20260821-0001', '2026-08-22 16:11:20'),
(107, 28, 'IN_TRANSIT', 'AVAILABLE', NULL, 61, NULL, NULL, 'transfer', 3, 3382, 'Transfer Diterima - TRF-20260821-0001', '2026-08-22 16:11:20');

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
(9, 191, 'MDM-TEST-0001', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:48:12'),
(10, 191, 'MDM-TEST-0002', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:48:12'),
(11, 191, 'MDM-TEST-0003', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:48:12'),
(12, 191, 'MDM-TEST-0004', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:48:12'),
(13, 191, 'MDM-TEST-0005', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:48:12'),
(14, 191, 'MDM-TEST-0006', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:48:12'),
(15, 191, 'MDM-TEST-0007', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:48:12'),
(16, 191, 'MDM-TEST-0008', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:48:12'),
(17, 191, 'MDM-TEST-0009', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:48:12'),
(18, 191, 'MDM-TEST-0010', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:48:12'),
(19, 191, 'MDM-TEST-0011', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-21 14:20:18'),
(20, 191, 'MDM-TEST-0012', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-21 14:20:18'),
(21, 191, 'MDM-TEST-0013', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-21 14:20:18'),
(22, 191, 'MDM-TEST-0014', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-21 14:20:18'),
(23, 191, 'MDM-TEST-0015', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-21 14:20:18'),
(24, 191, 'MDM-TEST-0016', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-21 14:20:18'),
(25, 191, 'MDM-TEST-0017', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-21 14:20:18'),
(26, 191, 'MDM-TEST-0018', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-22 16:11:20'),
(27, 191, 'MDM-TEST-0019', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-22 16:11:20'),
(28, 191, 'MDM-TEST-0020', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-22 16:11:20'),
(29, 191, 'MDM-TEST-0021', 'AVAILABLE', 61, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-21 15:27:31'),
(30, 191, 'MDM-TEST-0022', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(31, 191, 'MDM-TEST-0023', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(32, 191, 'MDM-TEST-0024', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(33, 191, 'MDM-TEST-0025', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(34, 191, 'MDM-TEST-0026', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(35, 191, 'MDM-TEST-0027', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(36, 191, 'MDM-TEST-0028', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(37, 191, 'MDM-TEST-0029', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(38, 191, 'MDM-TEST-0030', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(39, 191, 'MDM-TEST-0031', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(40, 191, 'MDM-TEST-0032', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(41, 191, 'MDM-TEST-0033', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(42, 191, 'MDM-TEST-0034', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(43, 191, 'MDM-TEST-0035', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(44, 191, 'MDM-TEST-0036', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(45, 191, 'MDM-TEST-0037', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(46, 191, 'MDM-TEST-0038', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(47, 191, 'MDM-TEST-0039', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(48, 191, 'MDM-TEST-0040', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(49, 191, 'MDM-TEST-0041', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(50, 191, 'MDM-TEST-0042', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(51, 191, 'MDM-TEST-0043', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(52, 191, 'MDM-TEST-0044', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(53, 191, 'MDM-TEST-0045', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(54, 191, 'MDM-TEST-0046', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(55, 191, 'MDM-TEST-0047', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(56, 191, 'MDM-TEST-0048', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(57, 191, 'MDM-TEST-0049', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16'),
(58, 191, 'MDM-TEST-0050', 'AVAILABLE', 60, NULL, NULL, 'GOOD', '2026-08-20 09:26:16', '2026-08-20 09:26:16');

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
('default_theme', 'light', '2026-08-20 09:38:54'),
('favicon_path', 'favicon_d0b68732d18e5534.jpg', '2026-08-20 09:38:54'),
('logo_path', 'logo_5874de4903debfd7.png', '2026-08-21 04:05:17'),
('primary_color', 'blue', '2026-08-20 09:38:54'),
('sidebar_theme', 'dark', '2026-08-20 09:38:54');

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
(59, 191, 60, 29, '2026-08-21 15:23:15'),
(60, 192, 60, 14, '2026-08-21 14:19:14'),
(61, 193, 60, 10, '2026-08-21 14:19:14'),
(62, 191, 61, 21, '2026-08-22 16:11:20'),
(63, 192, 61, 6, '2026-08-21 14:20:18'),
(64, 193, 61, 10, '2026-08-21 14:20:18');

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
(102, 191, 60, 'INCOMING', 'incoming', 47, 50, 0, 50, 3382, 'Incoming INC-20260820-0001', '2026-08-20 09:26:16'),
(103, 192, 60, 'INCOMING', 'incoming', 48, 20, 0, 20, 3382, 'Incoming INC-20260820-0002', '2026-08-20 09:26:33'),
(104, 193, 60, 'INCOMING', 'incoming', 49, 20, 0, 20, 3382, 'Incoming INC-20260820-0003', '2026-08-20 09:26:45'),
(105, 191, 60, 'TRANSFER_OUT', 'transfers', 2, -10, 50, 40, 3382, 'Transfer Keluar TRF-20260820-0001', '2026-08-20 09:30:21'),
(106, 193, 60, 'TRANSFER_OUT', 'transfers', 2, -5, 20, 15, 3382, 'Transfer Keluar TRF-20260820-0001', '2026-08-20 09:30:21'),
(107, 192, 60, 'TRANSFER_OUT', 'transfers', 2, -3, 20, 17, 3382, 'Transfer Keluar TRF-20260820-0001', '2026-08-20 09:30:21'),
(108, 191, 61, 'TRANSFER_IN', 'transfers', 2, 10, 0, 10, 3382, 'Transfer In TRF-20260820-0001', '2026-08-20 09:48:12'),
(109, 192, 61, 'TRANSFER_IN', 'transfers', 2, 3, 0, 3, 3382, 'Transfer In TRF-20260820-0001', '2026-08-20 09:48:12'),
(110, 193, 61, 'TRANSFER_IN', 'transfers', 2, 5, 0, 5, 3382, 'Transfer In TRF-20260820-0001', '2026-08-20 09:48:12'),
(111, 191, 60, 'TRANSFER_OUT', 'transfers', 3, -10, 40, 30, 3382, 'Transfer Keluar TRF-20260821-0001', '2026-08-21 14:19:14'),
(112, 193, 60, 'TRANSFER_OUT', 'transfers', 3, -5, 15, 10, 3382, 'Transfer Keluar TRF-20260821-0001', '2026-08-21 14:19:14'),
(113, 192, 60, 'TRANSFER_OUT', 'transfers', 3, -3, 17, 14, 3382, 'Transfer Keluar TRF-20260821-0001', '2026-08-21 14:19:14'),
(114, 191, 61, 'TRANSFER_IN', 'transfers', 3, 7, 10, 17, 3382, 'Transfer In TRF-20260821-0001', '2026-08-21 14:20:18'),
(115, 193, 61, 'TRANSFER_IN', 'transfers', 3, 5, 5, 10, 3382, 'Transfer In TRF-20260821-0001', '2026-08-21 14:20:18'),
(116, 192, 61, 'TRANSFER_IN', 'transfers', 3, 3, 3, 6, 3382, 'Transfer In TRF-20260821-0001', '2026-08-21 14:20:18'),
(117, 191, 60, 'TRANSFER_OUT', 'transfers', 4, -1, 30, 29, 3382, 'Transfer Keluar TRF-20260821-0002', '2026-08-21 15:23:15'),
(118, 191, 61, 'TRANSFER_IN', 'transfers', 4, 1, 17, 18, 3382, 'Transfer In TRF-20260821-0002', '2026-08-21 15:27:31'),
(119, 191, 61, 'TRANSFER_IN', 'transfers', 3, 3, 18, 21, 3382, 'Transfer In TRF-20260821-0001', '2026-08-22 16:11:20');

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

--
-- Dumping data for table `transfers`
--

INSERT INTO `transfers` (`id`, `transaction_code`, `source_warehouse_id`, `destination_warehouse_id`, `status`, `sent_by`, `driver_name`, `driver_phone`, `vehicle_number`, `received_by`, `receiver_name`, `receiver_role`, `receiver_phone`, `notes`, `sent_at`, `received_at`) VALUES
(2, 'TRF-20260820-0001', 60, 61, 'COMPLETED', 3382, NULL, NULL, NULL, 3382, NULL, NULL, NULL, '', '2026-08-20 09:30:21', '2026-08-20 09:48:12'),
(3, 'TRF-20260821-0001', 60, 61, 'COMPLETED', 3382, NULL, NULL, NULL, 3382, NULL, NULL, NULL, '', '2026-08-21 14:19:14', '2026-08-22 16:11:20'),
(4, 'TRF-20260821-0002', 60, 61, 'COMPLETED', 3382, 'fghfghf', '085156984933', 'KB 5400', 3382, NULL, NULL, NULL, '', '2026-08-21 15:23:15', '2026-08-21 15:27:31');

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

--
-- Dumping data for table `transfer_items`
--

INSERT INTO `transfer_items` (`id`, `transfer_id`, `master_item_id`, `quantity_sent`, `quantity_received`) VALUES
(2, 2, 191, 10, 10),
(3, 2, 193, 5, 5),
(4, 2, 192, 3, 3),
(5, 3, 191, 10, 10),
(6, 3, 193, 5, 5),
(7, 3, 192, 3, 3),
(8, 4, 191, 1, 1);

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

--
-- Dumping data for table `transfer_item_serials`
--

INSERT INTO `transfer_item_serials` (`id`, `transfer_item_id`, `serial_number_id`, `status`) VALUES
(2, 2, 9, 'RECEIVED'),
(3, 2, 10, 'RECEIVED'),
(4, 2, 11, 'RECEIVED'),
(5, 2, 12, 'RECEIVED'),
(6, 2, 13, 'RECEIVED'),
(7, 2, 14, 'RECEIVED'),
(8, 2, 15, 'RECEIVED'),
(9, 2, 16, 'RECEIVED'),
(10, 2, 17, 'RECEIVED'),
(11, 2, 18, 'RECEIVED'),
(12, 5, 19, 'RECEIVED'),
(13, 5, 20, 'RECEIVED'),
(14, 5, 21, 'RECEIVED'),
(15, 5, 22, 'RECEIVED'),
(16, 5, 23, 'RECEIVED'),
(17, 5, 24, 'RECEIVED'),
(18, 5, 25, 'RECEIVED'),
(19, 5, 26, 'RECEIVED'),
(20, 5, 27, 'RECEIVED'),
(21, 5, 28, 'RECEIVED'),
(22, 8, 29, 'RECEIVED');

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
-- Dumping data for table `transfer_receipts`
--

INSERT INTO `transfer_receipts` (`id`, `transfer_id`, `processed_by`, `receiver_name`, `receiver_role`, `receiver_phone`, `notes`, `received_at`) VALUES
(1, 4, 3382, 'fghfghgfh', 'sgsgdsf', '0898343434346', NULL, '2026-08-21 22:27:31'),
(2, 3, 3382, 'RONI', 'ASDSAD', '0898343434346', NULL, '2026-08-22 23:11:20');

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

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `code`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(67, 'UNT-000001', 'UNIT', '', 1, '2026-08-20 09:22:54', '2026-08-20 09:22:54');

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
(1, 'Super Admin', 'superadmin@ptbig.com', '$2y$10$SpNVJGcdsfjB1TaTbn7r.uV3Kl3FNc/9XquLsLbcGMY77q2C/vz/W', 'SUPERADMIN', 'APPROVED', 'superadmin@ptbig.com', 'profiles/profile_1_1786962425.jpg', NULL, 1, '2026-08-21 04:03:17', '2026-08-15 05:13:46', '2026-08-21 04:03:17'),
(3382, 'AISYAH', 'mariatsa035@gmail.com', '$2y$10$.FS8Zrrftb8r4wxErBaUEuKuEvumDxAgn0ZpUUx52puLY7aKw1qru', 'ADMIN', 'APPROVED', '', 'profiles/profile_3382_1787284966.jpg', NULL, 1, '2026-08-22 16:06:31', '2026-08-17 12:13:26', '2026-08-22 16:06:31'),
(3646, 'TOMMY RYAN DWIPUTRA', 'kontol@gmail.com', '$2y$10$RIYig5nUW4PnbFaRKpO1rOWvjtIBG7t7eCuChf8T6sP8tHfmYcpjG', 'TEKNISI_NOC', 'APPROVED', '', NULL, NULL, 1, '2026-08-19 19:14:15', '2026-08-17 13:16:04', '2026-08-19 19:14:15'),
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
(60, 'GDG-000001', 'GUDANG PUSAT', '', '', 1, '2026-08-20 09:22:39', '2026-08-20 09:22:39'),
(61, 'GDG-000002', 'GUDANG CABANG', '', '', 1, '2026-08-20 09:22:46', '2026-08-20 09:22:46');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `incoming_items`
--
ALTER TABLE `incoming_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `loan_items`
--
ALTER TABLE `loan_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_item_serials`
--
ALTER TABLE `loan_item_serials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loan_manual_items`
--
ALTER TABLE `loan_manual_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `master_items`
--
ALTER TABLE `master_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT for table `master_item_attachments`
--
ALTER TABLE `master_item_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=350;

--
-- AUTO_INCREMENT for table `outgoing`
--
ALTER TABLE `outgoing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `outgoing_items`
--
ALTER TABLE `outgoing_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `serial_movements`
--
ALTER TABLE `serial_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `serial_numbers`
--
ALTER TABLE `serial_numbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transfer_items`
--
ALTER TABLE `transfer_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transfer_item_serials`
--
ALTER TABLE `transfer_item_serials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `transfer_receipts`
--
ALTER TABLE `transfer_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transfer_surat_jalan_versions`
--
ALTER TABLE `transfer_surat_jalan_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3778;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

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
-- Constraints for table `transfer_surat_jalan_versions`
--
ALTER TABLE `transfer_surat_jalan_versions`
  ADD CONSTRAINT `fk_transfer_sj_versions_transfer` FOREIGN KEY (`transfer_id`) REFERENCES `transfers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_transfer_sj_versions_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
