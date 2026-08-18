-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2026 at 03:39 PM
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
(484, 1, 'SUPERADMIN_CREATE_USER', 'users', 3382, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 12:13:26'),
(516, 3382, 'CREATE', 'item_categories', 52, NULL, NULL, NULL, NULL, '2026-08-17 12:24:01'),
(517, 3382, 'CREATE', 'item_categories', 53, NULL, NULL, NULL, NULL, '2026-08-17 12:24:42'),
(697, 3382, 'UPDATE', 'warehouses', 32, NULL, NULL, NULL, NULL, '2026-08-17 12:46:48'),
(1172, 1, 'SUPERADMIN_CREATE_USER', 'users', 3646, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 13:16:04'),
(1274, 3382, 'UPDATE', 'item_categories', 63, NULL, NULL, NULL, NULL, '2026-08-17 14:09:22'),
(1275, 3382, 'CREATE', 'units', 62, NULL, NULL, NULL, NULL, '2026-08-17 14:10:43'),
(1276, 3382, 'CREATE', 'locations', 29, NULL, NULL, NULL, NULL, '2026-08-17 14:11:10'),
(1277, 3382, 'DELETE', 'master_items', 128, NULL, NULL, NULL, NULL, '2026-08-17 14:11:18'),
(1278, 3382, 'DELETE', 'master_items', 152, NULL, NULL, NULL, NULL, '2026-08-17 14:11:20'),
(1279, 3382, 'DELETE', 'master_items', 158, NULL, NULL, NULL, NULL, '2026-08-17 14:11:24'),
(1280, 3382, 'DELETE', 'master_items', 165, NULL, NULL, NULL, NULL, '2026-08-17 14:11:27'),
(1281, 3382, 'CREATE', 'master_items', 177, NULL, NULL, NULL, NULL, '2026-08-17 14:12:03'),
(1282, 3382, 'CREATE', 'incoming', 37, NULL, NULL, NULL, NULL, '2026-08-17 14:12:29'),
(1283, 3382, 'UPDATE', 'master_items', 177, NULL, NULL, NULL, NULL, '2026-08-17 14:19:24'),
(1284, 3646, 'CREATE', 'submissions', 2, NULL, NULL, NULL, NULL, '2026-08-17 14:35:40'),
(1285, 3646, 'CREATE', 'submissions', 3, NULL, NULL, NULL, NULL, '2026-08-17 14:47:17'),
(1286, 3382, 'APPROVE', 'submissions', 3, NULL, '{\"status\":\"APPROVED\"}', NULL, NULL, '2026-08-17 14:53:44'),
(1287, 3382, 'APPROVE', 'submissions', 2, NULL, '{\"status\":\"APPROVED\"}', NULL, NULL, '2026-08-17 14:54:06'),
(1288, 3382, 'CREATE', 'master_items', 178, NULL, NULL, NULL, NULL, '2026-08-17 14:56:02'),
(1289, 3382, 'CREATE', 'incoming', 38, NULL, NULL, NULL, NULL, '2026-08-17 14:56:28'),
(1290, 3646, 'CREATE', 'submissions', 4, NULL, NULL, NULL, NULL, '2026-08-17 14:57:56'),
(1291, 3382, 'APPROVE', 'submissions', 4, NULL, '{\"status\":\"APPROVED\"}', NULL, NULL, '2026-08-17 14:58:45'),
(1292, 1, 'UPDATE', 'users', 3646, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 15:13:19'),
(1293, 3646, 'CREATE', 'submissions', 5, NULL, NULL, NULL, NULL, '2026-08-17 15:13:43'),
(1294, 3382, 'APPROVE', 'submissions', 5, NULL, '{\"status\":\"APPROVED\"}', NULL, NULL, '2026-08-17 15:14:23'),
(1295, 3382, 'CREATE', 'item_categories', 103, NULL, NULL, NULL, NULL, '2026-08-17 15:29:13'),
(1296, 3382, 'CREATE', 'master_items', 179, NULL, NULL, NULL, NULL, '2026-08-17 15:29:49'),
(1297, 3382, 'CREATE', 'incoming', 39, NULL, NULL, NULL, NULL, '2026-08-17 15:30:08'),
(1298, 3646, 'CREATE', 'submissions', 6, NULL, NULL, NULL, NULL, '2026-08-17 15:30:35'),
(1312, 3382, 'APPROVE', 'submissions', 6, NULL, '{\"status\":\"APPROVED\"}', NULL, NULL, '2026-08-17 16:44:33'),
(1325, 3646, 'CREATE', 'submissions', 15, NULL, NULL, NULL, NULL, '2026-08-17 17:10:28'),
(1326, 3382, 'APPROVE', 'submissions', 15, NULL, '{\"status\":\"APPROVED\"}', NULL, NULL, '2026-08-17 17:10:51'),
(1328, 3646, 'CREATE', 'submissions', 16, NULL, NULL, NULL, NULL, '2026-08-17 17:21:34'),
(1329, 3382, 'APPROVE', 'submissions', 16, NULL, '{\"status\":\"APPROVED\"}', NULL, NULL, '2026-08-17 17:21:51'),
(1332, 3646, 'CREATE', 'submissions', 17, NULL, NULL, NULL, NULL, '2026-08-17 20:41:08'),
(1333, 3382, 'APPROVE', 'submissions', 17, NULL, '{\"status\":\"APPROVED\"}', NULL, NULL, '2026-08-17 20:41:44'),
(1366, 3743, 'PUBLIC_REGISTER', 'users', 3743, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/151.0.7922.34 Safari/537.36', '2026-08-18 08:04:11'),
(1423, 3646, 'CREATE', 'submissions', 29, NULL, NULL, NULL, NULL, '2026-08-18 08:39:26'),
(1424, 3382, 'APPROVE', 'submissions', 29, NULL, '{\"status\":\"APPROVED\"}', NULL, NULL, '2026-08-18 08:39:43'),
(1431, 3646, 'CREATE', 'submissions', 33, NULL, NULL, NULL, NULL, '2026-08-18 13:37:09'),
(1432, 3382, 'APPROVE', 'submissions', 33, NULL, '{\"status\":\"APPROVED\"}', NULL, NULL, '2026-08-18 13:37:23');

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
(37, 'INC-20260817-0001', 32, 3382, '', '', 'COMPLETED', '2026-08-17 14:12:29'),
(38, 'INC-20260817-0002', 32, 3382, '', '', 'COMPLETED', '2026-08-17 14:56:28'),
(39, 'INC-20260817-0003', 32, 3382, '', '', 'COMPLETED', '2026-08-17 15:30:08');

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
(36, 37, 177, 5),
(37, 38, 177, 10),
(38, 39, 179, 20);

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
(52, 'KAT-000001', 'ODP', 'Optical Distribution Point', 1, '2026-08-17 12:24:01', '2026-08-17 12:24:01'),
(53, 'KAT-000002', 'ODC', 'Optical Distribution Cabinet', 1, '2026-08-17 12:24:42', '2026-08-17 12:24:42'),
(63, 'KAT-000003', 'SYS-BASE-KATEGORI', 'Data baseline testing', 0, '2026-08-17 12:40:42', '2026-08-17 14:09:22'),
(103, 'KAT-000004', 'MODEM', '', 1, '2026-08-17 15:29:13', '2026-08-17 15:29:13');

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
  `status` varchar(50) NOT NULL DEFAULT 'ACTIVE',
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
  `status` enum('BORROWED','RETURNED') NOT NULL DEFAULT 'BORROWED'
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
(29, 32, 'LOK-000001', 'RUANG GUDANG', '', 1, '2026-08-17 14:11:10', '2026-08-17 14:11:10');

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
(53, 'KAT-000003-000001', 'SYS-BASE-BARANG-1', 63, 38, 0, 0, 'Data baseline testing', 1, '2026-08-17 12:40:42', '2026-08-17 12:40:42'),
(54, 'KAT-000003-000002', 'SYS-BASE-BARANG-2', 63, 38, 0, 0, 'Data baseline testing', 1, '2026-08-17 12:40:42', '2026-08-17 12:40:42'),
(55, 'KAT-000003-000003', 'SYS-BASE-BARANG-SERIAL', 63, 38, 1, 0, 'Data baseline testing', 1, '2026-08-17 12:40:42', '2026-08-17 12:40:42'),
(177, 'KAT-000001-000001', 'ODP TARMOC', 52, 62, 0, 50, '', 1, '2026-08-17 14:12:03', '2026-08-17 14:19:24'),
(178, 'KAT-000001-000002', 'ODP ZIMMLINK', 52, 62, 0, 50, '', 1, '2026-08-17 14:56:02', '2026-08-17 14:56:02'),
(179, 'KAT-000004-000001', 'MODEM TRIKOM', 103, 62, 0, 50, '', 1, '2026-08-17 15:29:49', '2026-08-17 15:29:49'),
(180, 'KAT-000004-000002', 'ITM Aktif Unused 1787040241721', 103, 62, 0, 0, '', 1, '2026-08-18 08:04:02', '2026-08-18 08:04:02');

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
(1, 177, 'items/item_6a8316b3aa341.jpg', 'wallpaperflare.com_wallpaper (1).jpg', 'image/jpeg', 821366, 0, 3382, '2026-08-17 14:12:03'),
(2, 179, 'items/item_6a8328ed64b0f.jpg', 'WhatsApp Image 2025-07-17 at 22.21.39_7b20b12c.jpg', 'image/jpeg', 105983, 0, 3382, '2026-08-17 15:29:49');

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
(187, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun wfg_1786968609668@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 19:10:10'),
(190, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun wfg_1786969139878@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 19:19:00'),
(201, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun wfg_1786969976838@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 19:32:57'),
(208, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun wfg_1786970549917@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 19:42:30'),
(215, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun wfg_1786971833740@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 20:03:54'),
(222, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun wfg_1786972001983@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 20:06:42'),
(229, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun notif_reg_1786972139967@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 20:09:01'),
(230, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun wfg_1786972250950@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 20:10:52'),
(237, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun notif_reg_1786972313020@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 20:11:53'),
(238, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun wfg_1786972408940@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 20:13:29'),
(245, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun notif_reg_1786972470097@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 20:14:30'),
(246, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun wfg_1786972570190@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 20:16:10'),
(253, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun notif_reg_1786972634958@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 20:17:15'),
(254, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun wfg_1786972731999@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', '2026-08-17 22:55:58', '2026-08-17 20:18:52'),
(283, 3382, 'Pengajuan Barang Baru', 'TOMMY RYAN DWIPUTRA mengajukan 13 MODEM TRIKOM (REQ-20260818-0001).', 'info', '/submissions/show?id=15', NULL, '2026-08-18 00:10:28'),
(284, 3646, 'Pengajuan Disetujui', 'Pengajuan REQ-20260818-0001 telah disetujui oleh Admin.', 'success', '/submissions/show?id=15', NULL, '2026-08-18 00:10:51'),
(285, 3382, 'Pengajuan Barang Baru', 'TOMMY RYAN DWIPUTRA mengajukan 15 MODEM TRIKOM (REQ-20260818-0002).', 'info', '/submissions/show?id=16', NULL, '2026-08-18 00:21:34'),
(286, 3646, 'Pengajuan Disetujui', 'Pengajuan REQ-20260818-0002 telah disetujui oleh Admin.', 'success', '/submissions/show?id=16', NULL, '2026-08-18 00:21:51'),
(287, 3382, 'Pengajuan Barang Baru', 'TOMMY RYAN DWIPUTRA mengajukan 17 MODEM TRIKOM (REQ-20260818-0003).', 'info', '/submissions/show?id=17', NULL, '2026-08-18 03:41:08'),
(288, 3646, 'Pengajuan Disetujui', 'Pengajuan REQ-20260818-0003 telah disetujui oleh Admin.', 'success', '/submissions/show?id=17', NULL, '2026-08-18 03:41:45'),
(321, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun notif_reg_1787040250429@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', NULL, '2026-08-18 15:04:11'),
(322, 1, 'Pendaftaran baru menunggu persetujuan', 'Akun wfg_1787040359925@ptbig.com (TEKNISI_NOC) mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL', NULL, '2026-08-18 15:06:00'),
(325, 3382, 'Pengajuan Barang Baru', 'TOMMY RYAN DWIPUTRA mengajukan 15 MODEM TRIKOM (REQ-20260818-0004).', 'info', '/submissions/show?id=29', NULL, '2026-08-18 15:39:26'),
(326, 3646, 'Pengajuan Disetujui', 'Pengajuan REQ-20260818-0004 telah disetujui oleh Admin.', 'success', '/submissions/show?id=29', NULL, '2026-08-18 15:39:43'),
(327, 3382, 'Pengajuan Barang Baru', 'E2E_TECH mengajukan 15 MODEM TRIKOM (REQ-20260818-0005).', 'info', '/submissions/show?id=30', NULL, '2026-08-18 20:00:06'),
(330, 3382, 'Pengajuan Barang Baru', 'E2E_TECH mengajukan 3 MODEM TRIKOM (REQ-20260818-0006).', 'info', '/submissions/show?id=31', NULL, '2026-08-18 20:00:08'),
(333, 3382, 'Pengajuan Barang Baru', 'E2E_TECH mengajukan 50 MODEM TRIKOM (REQ-20260818-0007).', 'info', '/submissions/show?id=32', NULL, '2026-08-18 20:00:10'),
(335, 3382, 'Pengajuan Barang Baru', 'TOMMY RYAN DWIPUTRA mengajukan 15 MODEM TRIKOM (REQ-20260818-0005).', 'info', '/submissions/show?id=33', NULL, '2026-08-18 20:37:09'),
(336, 3646, 'Pengajuan Disetujui', 'Pengajuan REQ-20260818-0005 telah disetujui oleh Admin.', 'success', '/submissions/show?id=33', NULL, '2026-08-18 20:37:23');

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
  `master_item_id` int(11) NOT NULL,
  `loan_item_id` int(11) DEFAULT NULL,
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
(6, 55, 'SYS-BASE-SERIAL-1786970442', 'AVAILABLE', 32, NULL, NULL, 'BAIK', '2026-08-17 12:40:42', '2026-08-17 17:00:55');

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
(11, 53, 32, 45, '2026-08-17 12:40:42'),
(12, 54, 32, 10, '2026-08-17 12:40:42'),
(13, 55, 32, 4, '2026-08-17 17:00:55'),
(43, 177, 32, 15, '2026-08-18 07:57:45'),
(44, 179, 32, 20, '2026-08-18 13:00:11');

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
(57, 177, 32, 'INCOMING', 'incoming', 37, 5, 0, 5, 3382, 'Incoming INC-20260817-0001', '2026-08-17 14:12:29'),
(58, 177, 32, 'INCOMING', 'incoming', 38, 10, 5, 15, 3382, 'Incoming INC-20260817-0002', '2026-08-17 14:56:28'),
(59, 179, 32, 'INCOMING', 'incoming', 39, 20, 0, 20, 3382, 'Incoming INC-20260817-0003', '2026-08-17 15:30:08');

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
(2, 'REQ-20260817-0001', 3646, 'STOCK', 'APPROVED', 'untuk install di kobar', 3382, '2026-08-17 14:54:06', '', '2026-08-17 14:35:40', '2026-08-17 14:54:06'),
(3, 'REQ-20260817-0002', 3646, 'MANUAL', 'APPROVED', 'buat ngewe', 3382, '2026-08-17 14:53:44', '', '2026-08-17 14:47:17', '2026-08-17 14:53:44'),
(4, 'REQ-20260817-0003', 3646, 'STOCK', 'APPROVED', '', 3382, '2026-08-17 14:58:45', '', '2026-08-17 14:57:56', '2026-08-17 14:58:45'),
(5, 'REQ-20260817-0004', 3646, 'STOCK', 'APPROVED', '', 3382, '2026-08-17 15:14:23', '', '2026-08-17 15:13:43', '2026-08-17 15:14:23'),
(6, 'REQ-20260817-0005', 3646, 'STOCK', 'APPROVED', '', 3382, '2026-08-17 16:44:33', '', '2026-08-17 15:30:35', '2026-08-17 17:39:49'),
(15, 'REQ-20260818-0001', 3646, 'STOCK', 'APPROVED', '', 3382, '2026-08-17 17:10:51', '', '2026-08-17 17:10:28', '2026-08-17 17:10:51'),
(16, 'REQ-20260818-0002', 3646, 'STOCK', 'APPROVED', '', 3382, '2026-08-17 17:21:51', '', '2026-08-17 17:21:34', '2026-08-17 17:21:51'),
(17, 'REQ-20260818-0003', 3646, 'STOCK', 'APPROVED', '', 3382, '2026-08-17 20:41:44', '', '2026-08-17 20:41:08', '2026-08-17 20:41:44'),
(29, 'REQ-20260818-0004', 3646, 'STOCK', 'APPROVED', '', 3382, '2026-08-18 08:39:43', '', '2026-08-18 08:39:26', '2026-08-18 08:39:43'),
(33, 'REQ-20260818-0005', 3646, 'STOCK', 'APPROVED', '', 3382, '2026-08-18 13:37:23', '', '2026-08-18 13:37:09', '2026-08-18 13:37:23');

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
(2, 2, 'STOCK', NULL, 177, NULL, NULL, NULL, 2, 2, 0, 2, 'APPROVED', '', NULL, 3382, '2026-08-17 21:54:06', ''),
(3, 3, 'MANUAL', NULL, 177, 'kondom', '', '', 1, 1, 0, 1, 'APPROVED', '', NULL, 3382, '2026-08-17 21:53:44', ''),
(4, 4, 'STOCK', NULL, 178, NULL, NULL, NULL, 10, 10, 0, 10, 'APPROVED', '', NULL, 3382, '2026-08-17 21:58:45', ''),
(5, 5, 'STOCK', NULL, 177, NULL, NULL, NULL, 5, 5, 0, 5, 'APPROVED', '', NULL, 3382, '2026-08-17 22:14:23', ''),
(6, 6, 'STOCK', NULL, 179, NULL, NULL, NULL, 15, 15, 0, 15, 'APPROVED', '', NULL, 3382, '2026-08-17 23:44:33', ''),
(17, 15, 'STOCK', NULL, 179, NULL, NULL, NULL, 13, 13, 0, 13, 'APPROVED', '', NULL, 3382, '2026-08-18 00:10:51', ''),
(18, 16, 'STOCK', NULL, 179, NULL, NULL, NULL, 15, 15, 0, 15, 'APPROVED', '', NULL, 3382, '2026-08-18 00:21:51', ''),
(19, 17, 'STOCK', NULL, 179, NULL, NULL, NULL, 17, 17, 0, 17, 'APPROVED', '', NULL, 3382, '2026-08-18 03:41:44', ''),
(33, 29, 'STOCK', 32, 179, NULL, NULL, NULL, 15, 15, 0, 15, 'APPROVED', '', NULL, 3382, '2026-08-18 15:39:43', ''),
(37, 33, 'STOCK', 32, 179, NULL, NULL, NULL, 15, 15, 0, 15, 'APPROVED', '', NULL, 3382, '2026-08-18 20:37:23', '');

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
(38, 'UNT-000001', 'SYS-BASE-SATUAN', 'Data baseline testing', 1, '2026-08-17 12:40:42', '2026-08-17 12:40:42'),
(62, 'UNT-000002', 'BUAH', '', 1, '2026-08-17 14:10:43', '2026-08-17 14:10:43');

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
(1, 'Super Admin', 'superadmin@ptbig.com', '$2y$10$SpNVJGcdsfjB1TaTbn7r.uV3Kl3FNc/9XquLsLbcGMY77q2C/vz/W', 'SUPERADMIN', 'APPROVED', 'superadmin@ptbig.com', 'profiles/profile_1_1786962425.jpg', NULL, 1, '2026-08-18 08:38:49', '2026-08-15 05:13:46', '2026-08-18 08:38:49'),
(3382, 'MARIA TESA', 'mariatsa035@gmail.com', '$2y$10$.FS8Zrrftb8r4wxErBaUEuKuEvumDxAgn0ZpUUx52puLY7aKw1qru', 'ADMIN', 'APPROVED', '', 'profiles/profile_6a82fae69864e.jpg', NULL, 1, '2026-08-18 13:37:15', '2026-08-17 12:13:26', '2026-08-18 13:37:15'),
(3646, 'TOMMY RYAN DWIPUTRA', 'kontol@gmail.com', '$2y$10$RIYig5nUW4PnbFaRKpO1rOWvjtIBG7t7eCuChf8T6sP8tHfmYcpjG', 'TEKNISI_NOC', 'APPROVED', '', NULL, NULL, 1, '2026-08-18 13:36:44', '2026-08-17 13:16:04', '2026-08-18 13:36:44'),
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
(32, 'GDG-000001', 'GUDANG 28 OKTOBER PONTIANAK', 'PONTIANAK', '', 1, '2026-08-17 12:40:42', '2026-08-17 12:46:48');

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
  ADD KEY `processed_by` (`processed_by`);

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
  ADD KEY `serial_number_id` (`serial_number_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1433;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `incoming_items`
--
ALTER TABLE `incoming_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `installations`
--
ALTER TABLE `installations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `installation_items`
--
ALTER TABLE `installation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_categories`
--
ALTER TABLE `item_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

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
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `master_items`
--
ALTER TABLE `master_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT for table `master_item_attachments`
--
ALTER TABLE `master_item_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=337;

--
-- AUTO_INCREMENT for table `outgoing`
--
ALTER TABLE `outgoing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `outgoing_items`
--
ALTER TABLE `outgoing_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `serial_movements`
--
ALTER TABLE `serial_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `serial_numbers`
--
ALTER TABLE `serial_numbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `stock_ledgers`
--
ALTER TABLE `stock_ledgers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `stock_opnames`
--
ALTER TABLE `stock_opnames`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `stock_opname_items`
--
ALTER TABLE `stock_opname_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `submission_items`
--
ALTER TABLE `submission_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transfer_items`
--
ALTER TABLE `transfer_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transfer_item_serials`
--
ALTER TABLE `transfer_item_serials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3775;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

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
