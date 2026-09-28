-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3307
-- Généré le : lun. 28 sep. 2026 à 11:41
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `parc_informatique`
--

-- --------------------------------------------------------

--
-- Structure de la table `amortization_rules`
--

CREATE TABLE `amortization_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `years` int(10) UNSIGNED NOT NULL,
  `method` enum('linear','declining') NOT NULL DEFAULT 'linear',
  `rate` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `assignments`
--

CREATE TABLE `assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `site_id` bigint(20) UNSIGNED DEFAULT NULL,
  `service_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_name` varchar(180) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `entity_type` varchar(80) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `severity` enum('info','warning','error','critical') NOT NULL DEFAULT 'info',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `user_name`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `url`, `method`, `severity`, `created_at`) VALUES
(1, 2, 'Admin Nouveau', 'create', 'equipment', 1, NULL, '{\"inventory_number\":\"INF-2026-0001\",\"designation\":\"TechnoLite\",\"category_id\":2,\"brand_id\":1,\"status_id\":1,\"serial_number\":\"CZ1234569\",\"acquisition_value\":25000}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '/parc-informatique/public/equipment', 'POST', 'info', '2026-09-28 09:36:03');

-- --------------------------------------------------------

--
-- Structure de la table `brands`
--

CREATE TABLE `brands` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `brands`
--

INSERT INTO `brands` (`id`, `name`, `created_at`) VALUES
(1, 'HP', '2026-09-28 09:18:48'),
(2, 'Dell', '2026-09-28 09:18:48'),
(3, 'Lenovo', '2026-09-28 09:18:48'),
(4, 'Asus', '2026-09-28 09:18:48'),
(5, 'Acer', '2026-09-28 09:18:48'),
(6, 'Epson', '2026-09-28 09:18:48'),
(7, 'Canon', '2026-09-28 09:18:48'),
(8, 'APC', '2026-09-28 09:18:48'),
(9, 'Cisco', '2026-09-28 09:18:48'),
(10, 'TP-Link', '2026-09-28 09:18:48');

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference` varchar(80) DEFAULT NULL,
  `type` enum('pv_reform','exit_voucher','invoice','report','other') NOT NULL,
  `title` varchar(255) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `current_version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `size_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `is_signed` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `document_versions`
--

CREATE TABLE `document_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `change_notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `matricule` varchar(50) DEFAULT NULL,
  `email` varchar(180) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `function_title` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `equipment`
--

CREATE TABLE `equipment` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inventory_number` varchar(50) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `designation` varchar(180) NOT NULL,
  `brand_id` bigint(20) UNSIGNED DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `model_text` varchar(150) DEFAULT NULL,
  `serial_number` varchar(150) DEFAULT NULL,
  `site_id` bigint(20) UNSIGNED DEFAULT NULL,
  `service_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `responsible_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED NOT NULL,
  `acquisition_date` date DEFAULT NULL,
  `commissioning_date` date DEFAULT NULL,
  `acquisition_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `residual_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amortization_years` int(10) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(80) DEFAULT NULL,
  `warranty_end_date` date DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `technical_specs` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `equipment`
--

INSERT INTO `equipment` (`id`, `inventory_number`, `category_id`, `designation`, `brand_id`, `model_id`, `model_text`, `serial_number`, `site_id`, `service_id`, `location_id`, `responsible_id`, `supplier_id`, `status_id`, `acquisition_date`, `commissioning_date`, `acquisition_value`, `residual_value`, `amortization_years`, `invoice_number`, `warranty_end_date`, `qr_code_path`, `barcode`, `technical_specs`, `notes`, `photo_path`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'INF-2026-0001', 2, 'TechnoLite', 1, NULL, '201 HP', 'CZ1234569', 1, 4, NULL, NULL, NULL, 1, '2025-09-28', NULL, 25000.00, 25000.00, 1, 'FAC-2024-0047', '2026-09-25', NULL, NULL, NULL, NULL, NULL, 2, NULL, '2026-09-28 09:36:03', '2026-09-28 09:36:03', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `equipment_attachments`
--

CREATE TABLE `equipment_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `equipment_categories`
--

CREATE TABLE `equipment_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `default_amortization_years` int(10) UNSIGNED DEFAULT 5,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `equipment_categories`
--

INSERT INTO `equipment_categories` (`id`, `parent_id`, `code`, `name`, `description`, `icon`, `default_amortization_years`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 'PC', 'Unité centrale / PC', 'Ordinateurs de bureau et portables', NULL, 5, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(2, NULL, 'SCREEN', 'Écran', 'Moniteurs et écrans', NULL, 5, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(3, NULL, 'PRINT', 'Imprimante', 'Imprimantes et multifonctions', NULL, 5, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(4, NULL, 'SCAN', 'Scanner', 'Scanners', NULL, 5, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(5, NULL, 'UPS', 'Onduleur', 'Onduleurs et batteries', NULL, 5, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(6, NULL, 'NETWORK', 'Équipement réseau', 'Switch, routeur, firewall', NULL, 8, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(7, NULL, 'SERVER', 'Serveur', 'Serveurs physiques', NULL, 5, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(8, NULL, 'PHONE', 'Téléphone IP', 'Téléphones IP', NULL, 5, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(9, NULL, 'VIDEO', 'Vidéoprojecteur', 'Vidéoprojecteurs', NULL, 5, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(10, NULL, 'OTHER', 'Autre', 'Autres équipements', NULL, 5, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48');

-- --------------------------------------------------------

--
-- Structure de la table `equipment_statuses`
--

CREATE TABLE `equipment_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(80) NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#6c757d',
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_final` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `equipment_statuses`
--

INSERT INTO `equipment_statuses` (`id`, `code`, `name`, `color`, `is_available`, `is_final`, `sort_order`) VALUES
(1, 'in_service', 'En service', '#198754', 1, 0, 1),
(2, 'in_stock', 'En stock', '#0dcaf0', 1, 0, 2),
(3, 'maintenance', 'En maintenance', '#ffc107', 1, 0, 3),
(4, 'out_of_service', 'Hors service', '#dc3545', 0, 0, 4),
(5, 'obsolete', 'Obsolète', '#6c757d', 0, 0, 5),
(6, 'to_reform', 'À réformer', '#fd7e14', 0, 0, 6),
(7, 'reformed', 'Réformé', '#495057', 0, 1, 7),
(8, 'lost', 'Perdu / Volé', '#842029', 0, 1, 8),
(9, 'disposed', 'Sorti d\'inventaire', '#212529', 0, 1, 9);

-- --------------------------------------------------------

--
-- Structure de la table `inventory_campaigns`
--

CREATE TABLE `inventory_campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference` varchar(50) NOT NULL,
  `name` varchar(180) NOT NULL,
  `site_id` bigint(20) UNSIGNED DEFAULT NULL,
  `service_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('planned','in_progress','completed','cancelled') NOT NULL DEFAULT 'planned',
  `description` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inventory_campaign_items`
--

CREATE TABLE `inventory_campaign_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','found','missing','moved','damaged') NOT NULL DEFAULT 'pending',
  `verified_at` datetime DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `observations` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `movement_type` enum('acquisition','assignment','transfer','maintenance','reform','disposal','return','loss') NOT NULL,
  `from_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `from_service_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_service_id` bigint(20) UNSIGNED DEFAULT NULL,
  `from_employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `moved_at` datetime NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `locations`
--

CREATE TABLE `locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED DEFAULT NULL,
  `building` varchar(100) DEFAULT NULL,
  `floor` varchar(20) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `attempted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `ip_address`, `user_agent`, `success`, `attempted_at`) VALUES
(1, 'GERANTE', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 0, '2026-09-28 09:19:50'),
(2, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 0, '2026-09-28 09:19:52'),
(3, 'admin@sadid.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 0, '2026-09-28 09:19:57'),
(4, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 0, '2026-09-28 09:20:08'),
(5, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 0, '2026-09-28 09:20:34'),
(6, 'admin2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 1, '2026-09-28 09:29:10'),
(7, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 1, '2026-09-28 09:30:45'),
(8, 'admin2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 1, '2026-09-28 09:30:58');

-- --------------------------------------------------------

--
-- Structure de la table `maintenance`
--

CREATE TABLE `maintenance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('preventive','corrective','curative','upgrade') NOT NULL DEFAULT 'corrective',
  `status` enum('open','in_progress','waiting_parts','completed','cancelled') NOT NULL DEFAULT 'open',
  `reported_at` datetime NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `reported_by` bigint(20) UNSIGNED DEFAULT NULL,
  `technician` varchar(150) DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `problem_description` text NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `work_done` text DEFAULT NULL,
  `result` enum('fixed','unfixed','replaced','pending') DEFAULT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `downtime_hours` int(10) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(80) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `maintenance_contracts`
--

CREATE TABLE `maintenance_contracts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL,
  `reference` varchar(80) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `maintenance_items`
--

CREATE TABLE `maintenance_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `maintenance_id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(180) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `serial_number` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `models`
--

CREATE TABLE `models` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brand_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `icon` varchar(50) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `password_history`
--

CREATE TABLE `password_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(180) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `module`, `description`, `created_at`) VALUES
(1, 'equipment.view', 'equipment', 'Voir les équipements', '2026-09-28 09:18:48'),
(2, 'equipment.create', 'equipment', 'Créer un équipement', '2026-09-28 09:18:48'),
(3, 'equipment.edit', 'equipment', 'Modifier un équipement', '2026-09-28 09:18:48'),
(4, 'equipment.delete', 'equipment', 'Supprimer un équipement', '2026-09-28 09:18:48'),
(5, 'equipment.export', 'equipment', 'Exporter les équipements', '2026-09-28 09:18:48'),
(6, 'equipment.import', 'equipment', 'Importer des équipements', '2026-09-28 09:18:48'),
(7, 'assignment.view', 'assignment', 'Voir les affectations', '2026-09-28 09:18:48'),
(8, 'assignment.create', 'assignment', 'Créer une affectation', '2026-09-28 09:18:48'),
(9, 'assignment.edit', 'assignment', 'Modifier une affectation', '2026-09-28 09:18:48'),
(10, 'maintenance.view', 'maintenance', 'Voir les maintenances', '2026-09-28 09:18:48'),
(11, 'maintenance.create', 'maintenance', 'Créer une maintenance', '2026-09-28 09:18:48'),
(12, 'maintenance.edit', 'maintenance', 'Modifier une maintenance', '2026-09-28 09:18:48'),
(13, 'maintenance.delete', 'maintenance', 'Supprimer une maintenance', '2026-09-28 09:18:48'),
(14, 'reform.view', 'reform', 'Voir les réformes', '2026-09-28 09:18:48'),
(15, 'reform.create', 'reform', 'Créer une réforme', '2026-09-28 09:18:48'),
(16, 'reform.edit', 'reform', 'Modifier une réforme', '2026-09-28 09:18:48'),
(17, 'reform.approve', 'reform', 'Approuver une réforme', '2026-09-28 09:18:48'),
(18, 'reform.reject', 'reform', 'Refuser une réforme', '2026-09-28 09:18:48'),
(19, 'reform.generate_pv', 'reform', 'Générer le PV de réforme', '2026-09-28 09:18:48'),
(20, 'document.view', 'document', 'Voir les documents', '2026-09-28 09:18:48'),
(21, 'document.download', 'document', 'Télécharger les documents', '2026-09-28 09:18:48'),
(22, 'report.view', 'report', 'Voir les rapports', '2026-09-28 09:18:48'),
(23, 'report.export', 'report', 'Exporter les rapports', '2026-09-28 09:18:48'),
(24, 'users.view', 'users', 'Voir les utilisateurs', '2026-09-28 09:18:48'),
(25, 'users.create', 'users', 'Créer un utilisateur', '2026-09-28 09:18:48'),
(26, 'users.edit', 'users', 'Modifier un utilisateur', '2026-09-28 09:18:48'),
(27, 'users.delete', 'users', 'Supprimer un utilisateur', '2026-09-28 09:18:48'),
(28, 'settings.view', 'settings', 'Voir les paramètres', '2026-09-28 09:18:48'),
(29, 'settings.manage', 'settings', 'Gérer les paramètres', '2026-09-28 09:18:48'),
(30, 'audit.view', 'audit', 'Consulter le journal d\'audit', '2026-09-28 09:18:48');

-- --------------------------------------------------------

--
-- Structure de la table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(10) UNSIGNED NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `hits` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `first_hit_at` int(10) UNSIGNED NOT NULL,
  `last_hit_at` int(10) UNSIGNED NOT NULL,
  `blocked_until` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `identifier`, `route`, `hits`, `first_hit_at`, `last_hit_at`, `blocked_until`) VALUES
(3, 'ip:127.0.0.1', '/login', 2, 1790587844, 1790587858, NULL),
(4, 'user:2|ip:127.0.0.1', '/users', 1, 1790588012, 1790588012, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `reformations`
--

CREATE TABLE `reformations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `reason` enum('obsolescence','breakdown','wear','end_of_life','other') NOT NULL,
  `reason_details` text DEFAULT NULL,
  `status` enum('draft','proposed','under_review','approved','rejected','pv_generated','exit_voucher_generated','completed','cancelled') NOT NULL DEFAULT 'draft',
  `proposed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `proposed_at` datetime DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `decided_by` bigint(20) UNSIGNED DEFAULT NULL,
  `decision_notes` text DEFAULT NULL,
  `commission_reference` varchar(80) DEFAULT NULL,
  `meeting_date` date DEFAULT NULL,
  `total_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `pv_path` varchar(255) DEFAULT NULL,
  `pv_generated_at` datetime DEFAULT NULL,
  `exit_voucher_path` varchar(255) DEFAULT NULL,
  `exit_voucher_generated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reformation_decisions`
--

CREATE TABLE `reformation_decisions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reformation_id` bigint(20) UNSIGNED NOT NULL,
  `decision` enum('approved','rejected','postponed') NOT NULL,
  `decision_date` datetime NOT NULL,
  `commission_members` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reformation_items`
--

CREATE TABLE `reformation_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reformation_id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `estimated_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `condition_notes` text DEFAULT NULL,
  `photos` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reformation_workflow_logs`
--

CREATE TABLE `reformation_workflow_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reformation_id` bigint(20) UNSIGNED NOT NULL,
  `from_status` varchar(40) DEFAULT NULL,
  `to_status` varchar(40) NOT NULL,
  `action` varchar(80) NOT NULL,
  `comment` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'Administrateur', 'admin', 'Accès total à toutes les fonctionnalités', 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(2, 'Gestionnaire', 'manager', 'Gestion du parc, affectations, maintenance, réformes', 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(3, 'Commission', 'commission', 'Examen et validation des propositions de réforme', 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(4, 'Consultation', 'viewer', 'Lecture seule', 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48');

-- --------------------------------------------------------

--
-- Structure de la table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 19),
(2, 20),
(2, 21),
(2, 22),
(2, 23),
(3, 1),
(3, 14),
(3, 17),
(3, 18),
(3, 20),
(3, 21),
(3, 22),
(4, 1),
(4, 7),
(4, 10),
(4, 14),
(4, 20),
(4, 22);

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `site_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `services`
--

INSERT INTO `services` (`id`, `site_id`, `code`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'DIR', 'Direction Générale', NULL, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(2, 1, 'DF', 'Direction Financière', NULL, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(3, 1, 'RH', 'Ressources Humaines', NULL, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(4, 1, 'INFO', 'Service Informatique', NULL, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48'),
(5, 1, 'COMP', 'Comptabilité', NULL, 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48');

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `payload` mediumtext NOT NULL,
  `last_activity` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('cmtu68vk3bfl6tjvar0iattcvm', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 'user_id|i:2;username|s:6:\"admin2\";full_name|s:13:\"Admin Nouveau\";roles|a:1:{i:0;s:5:\"admin\";}permissions|a:30:{i:0;s:14:\"equipment.view\";i:1;s:16:\"equipment.create\";i:2;s:14:\"equipment.edit\";i:3;s:16:\"equipment.delete\";i:4;s:16:\"equipment.export\";i:5;s:16:\"equipment.import\";i:6;s:15:\"assignment.view\";i:7;s:17:\"assignment.create\";i:8;s:15:\"assignment.edit\";i:9;s:16:\"maintenance.view\";i:10;s:18:\"maintenance.create\";i:11;s:16:\"maintenance.edit\";i:12;s:18:\"maintenance.delete\";i:13;s:11:\"reform.view\";i:14;s:13:\"reform.create\";i:15;s:11:\"reform.edit\";i:16;s:14:\"reform.approve\";i:17;s:13:\"reform.reject\";i:18;s:18:\"reform.generate_pv\";i:19;s:13:\"document.view\";i:20;s:17:\"document.download\";i:21;s:11:\"report.view\";i:22;s:13:\"report.export\";i:23;s:10:\"users.view\";i:24;s:12:\"users.create\";i:25;s:10:\"users.edit\";i:26;s:12:\"users.delete\";i:27;s:13:\"settings.view\";i:28;s:15:\"settings.manage\";i:29;s:10:\"audit.view\";}logged_in_at|i:1790587750;last_activity|i:1790587750;must_change_password|b:0;_flash|a:0:{}_csrf_token|s:64:\"1cfaca9e4dbe7788bd99e0cc2235e17b0e65edaffc364896717a8b111cc8fa52\";', 1790587750),
('lc1ub5agneureq4fqpner09or5', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '_csrf_token|s:64:\"5970675d1d47618d0bc535f236aa6cbf17316dca2ef1e3838bf40c4bd9e27ea3\";', 1790587618),
('t1lhmul2ceo1ajk9oh62g6ajhd', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '_csrf_token|s:64:\"350322d66594ce23ea03524c3a11be28bc3d74641b49021f9c8beae2d41294b7\";_old|a:1:{s:8:\"username\";s:5:\"admin\";}_flash|a:0:{}', 1790587234),
('to4tvggjm766n7le9d3nc07saj', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '_flash|a:1:{s:7:\"success\";s:24:\"Vous êtes déconnecté.\";}', 1790587853),
('u9ni3vuo5q4tp4j6slad1ld5nq', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 'user_id|i:2;username|s:6:\"admin2\";full_name|s:13:\"Admin Nouveau\";roles|a:1:{i:0;s:5:\"admin\";}permissions|a:30:{i:0;s:14:\"equipment.view\";i:1;s:16:\"equipment.create\";i:2;s:14:\"equipment.edit\";i:3;s:16:\"equipment.delete\";i:4;s:16:\"equipment.export\";i:5;s:16:\"equipment.import\";i:6;s:15:\"assignment.view\";i:7;s:17:\"assignment.create\";i:8;s:15:\"assignment.edit\";i:9;s:16:\"maintenance.view\";i:10;s:18:\"maintenance.create\";i:11;s:16:\"maintenance.edit\";i:12;s:18:\"maintenance.delete\";i:13;s:11:\"reform.view\";i:14;s:13:\"reform.create\";i:15;s:11:\"reform.edit\";i:16;s:14:\"reform.approve\";i:17;s:13:\"reform.reject\";i:18;s:18:\"reform.generate_pv\";i:19;s:13:\"document.view\";i:20;s:17:\"document.download\";i:21;s:11:\"report.view\";i:22;s:13:\"report.export\";i:23;s:10:\"users.view\";i:24;s:12:\"users.create\";i:25;s:10:\"users.edit\";i:26;s:12:\"users.delete\";i:27;s:13:\"settings.view\";i:28;s:15:\"settings.manage\";i:29;s:10:\"audit.view\";}logged_in_at|i:1790587858;last_activity|i:1790588238;must_change_password|b:0;_flash|a:0:{}_csrf_token|s:64:\"c131fde821c3e8440f57d41ba404cce2d5290f7fffe9a90c5d544916e2bed5af\";', 1790588238);

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(120) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `type` enum('string','int','bool','json','text') NOT NULL DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `group`, `type`, `description`, `updated_at`) VALUES
(1, 'app.name', 'Gestion du Parc Informatique', 'general', 'string', 'Nom de l\'application', '2026-09-28 09:18:48'),
(2, 'app.organization', 'Votre Organisation', 'general', 'string', 'Nom de l\'organisation', '2026-09-28 09:18:48'),
(3, 'app.city', 'Alger', 'general', 'string', 'Ville', '2026-09-28 09:18:48'),
(4, 'app.logo', '', 'general', 'string', 'Chemin du logo', '2026-09-28 09:18:48'),
(5, 'equipment.code_prefix', 'INF', 'equipment', 'string', 'Préfixe des numéros d\'inventaire', '2026-09-28 09:18:48'),
(6, 'equipment.code_year', '1', 'equipment', 'bool', 'Inclure l\'année dans le code', '2026-09-28 09:18:48'),
(7, 'reform.code_prefix', 'REF', 'reform', 'string', 'Préfixe des références de réforme', '2026-09-28 09:18:48'),
(8, 'alert.no_assignment_days', '30', 'alerts', 'int', 'Jours avant alerte sans affectation', '2026-09-28 09:18:48'),
(9, 'alert.obsolete_days', '60', 'alerts', 'int', 'Jours avant alerte obsolescence', '2026-09-28 09:18:48');

-- --------------------------------------------------------

--
-- Structure de la table `sites`
--

CREATE TABLE `sites` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sites`
--

INSERT INTO `sites` (`id`, `code`, `name`, `address`, `city`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SIEGE', 'Siège principal', NULL, 'Alger', 1, '2026-09-28 09:18:48', '2026-09-28 09:18:48');

-- --------------------------------------------------------

--
-- Structure de la table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(30) DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `contact_name` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(180) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `nif` varchar(50) DEFAULT NULL,
  `rc` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `password_expires_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `failed_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_backup_codes` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `avatar`, `is_active`, `must_change_password`, `password_changed_at`, `password_expires_at`, `last_login_at`, `last_login_ip`, `failed_attempts`, `locked_until`, `two_factor_secret`, `two_factor_enabled`, `two_factor_backup_codes`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', 'admin@parc.local', '$argon2id$v=19$m=65536,t=4,p=2$MGV6OU1YVWhzMXprM25TZw$gYoS/NKRHw21tweuIXRdto7NGL2ae5GhIjFck0NRnH4', 'Administrateur', 'Système', NULL, NULL, 1, 0, '2026-09-28 09:30:45', '2026-12-27 09:30:45', NULL, NULL, 0, NULL, NULL, 0, NULL, NULL, '2026-09-28 09:18:48', '2026-09-28 09:30:45', NULL),
(2, 'admin2', 'admin2@parc.local', '$argon2id$v=19$m=65536,t=4,p=2$RVVHOFd6bGw2bmplYU1ERQ$nUZaUmTwUpLd0dVk0/uzSmCv89Fo8uJ8Wftk6GCvkAk', 'Admin', 'Nouveau', NULL, NULL, 1, 0, '2026-09-28 09:29:10', '2026-12-27 09:29:10', NULL, NULL, 0, NULL, NULL, 0, NULL, NULL, '2026-09-28 09:26:33', '2026-09-28 09:29:10', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_at`) VALUES
(1, 1, '2026-09-28 09:18:48'),
(2, 1, '2026-09-28 09:26:33');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `amortization_rules`
--
ALTER TABLE `amortization_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_category` (`category_id`);

--
-- Index pour la table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_equipment` (`equipment_id`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Index pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_date` (`created_at`);

--
-- Index pour la table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_type` (`type`);

--
-- Index pour la table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_doc_version` (`document_id`,`version`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricule` (`matricule`),
  ADD KEY `idx_service` (`service_id`),
  ADD KEY `idx_name` (`last_name`,`first_name`);

--
-- Index pour la table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventory_number` (`inventory_number`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `responsible_id` (`responsible_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_inventory` (`inventory_number`),
  ADD KEY `idx_serial` (`serial_number`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_status` (`status_id`),
  ADD KEY `idx_service` (`service_id`),
  ADD KEY `idx_site` (`site_id`),
  ADD KEY `idx_deleted` (`deleted_at`);

--
-- Index pour la table `equipment_attachments`
--
ALTER TABLE `equipment_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_equipment` (`equipment_id`);

--
-- Index pour la table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_parent` (`parent_id`);

--
-- Index pour la table `equipment_statuses`
--
ALTER TABLE `equipment_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `inventory_campaigns`
--
ALTER TABLE `inventory_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `site_id` (`site_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `inventory_campaign_items`
--
ALTER TABLE `inventory_campaign_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_campaign_equip` (`campaign_id`,`equipment_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `idx_status` (`status`);

--
-- Index pour la table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_location_id` (`from_location_id`),
  ADD KEY `to_location_id` (`to_location_id`),
  ADD KEY `from_service_id` (`from_service_id`),
  ADD KEY `to_service_id` (`to_service_id`),
  ADD KEY `from_employee_id` (`from_employee_id`),
  ADD KEY `to_employee_id` (`to_employee_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_equipment` (`equipment_id`),
  ADD KEY `idx_type` (`movement_type`),
  ADD KEY `idx_date` (`moved_at`);

--
-- Index pour la table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service` (`service_id`);

--
-- Index pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username_date` (`username`,`attempted_at`),
  ADD KEY `idx_ip` (`ip_address`);

--
-- Index pour la table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reported_by` (`reported_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_equipment` (`equipment_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_reported` (`reported_at`);

--
-- Index pour la table `maintenance_contracts`
--
ALTER TABLE `maintenance_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Index pour la table `maintenance_items`
--
ALTER TABLE `maintenance_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_maintenance` (`maintenance_id`);

--
-- Index pour la table `models`
--
ALTER TABLE `models`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_brand_model` (`brand_id`,`name`),
  ADD KEY `idx_category` (`category_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`);

--
-- Index pour la table `password_history`
--
ALTER TABLE `password_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `idx_email` (`email`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_module` (`module`);

--
-- Index pour la table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier_route` (`identifier`,`route`),
  ADD KEY `idx_last_hit` (`last_hit_at`),
  ADD KEY `idx_blocked` (`blocked_until`);

--
-- Index pour la table `reformations`
--
ALTER TABLE `reformations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `proposed_by` (`proposed_by`),
  ADD KEY `decided_by` (`decided_by`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_reference` (`reference`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dates` (`submitted_at`,`decided_at`);

--
-- Index pour la table `reformation_decisions`
--
ALTER TABLE `reformation_decisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_reform` (`reformation_id`);

--
-- Index pour la table `reformation_items`
--
ALTER TABLE `reformation_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_reform_equip` (`reformation_id`,`equipment_id`),
  ADD KEY `idx_equipment` (`equipment_id`);

--
-- Index pour la table `reformation_workflow_logs`
--
ALTER TABLE `reformation_workflow_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_reform` (`reformation_id`),
  ADD KEY `idx_date` (`created_at`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_site` (`site_id`),
  ADD KEY `idx_name` (`name`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_activity` (`last_activity`);

--
-- Index pour la table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Index pour la table `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_active` (`is_active`);

--
-- Index pour la table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `amortization_rules`
--
ALTER TABLE `amortization_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `equipment_attachments`
--
ALTER TABLE `equipment_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `equipment_statuses`
--
ALTER TABLE `equipment_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `inventory_campaigns`
--
ALTER TABLE `inventory_campaigns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inventory_campaign_items`
--
ALTER TABLE `inventory_campaign_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `maintenance_contracts`
--
ALTER TABLE `maintenance_contracts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `maintenance_items`
--
ALTER TABLE `maintenance_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `models`
--
ALTER TABLE `models`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `password_history`
--
ALTER TABLE `password_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pour la table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `reformations`
--
ALTER TABLE `reformations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reformation_decisions`
--
ALTER TABLE `reformation_decisions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reformation_items`
--
ALTER TABLE `reformation_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reformation_workflow_logs`
--
ALTER TABLE `reformation_workflow_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `sites`
--
ALTER TABLE `sites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `amortization_rules`
--
ALTER TABLE `amortization_rules`
  ADD CONSTRAINT `amortization_rules_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `equipment_categories` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assignments_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assignments_ibfk_5` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assignments_ibfk_6` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `document_versions`
--
ALTER TABLE `document_versions`
  ADD CONSTRAINT `document_versions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_versions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `equipment_categories` (`id`),
  ADD CONSTRAINT `equipment_ibfk_10` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_11` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_3` FOREIGN KEY (`model_id`) REFERENCES `models` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_4` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_5` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_6` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_7` FOREIGN KEY (`responsible_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_8` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_ibfk_9` FOREIGN KEY (`status_id`) REFERENCES `equipment_statuses` (`id`);

--
-- Contraintes pour la table `equipment_attachments`
--
ALTER TABLE `equipment_attachments`
  ADD CONSTRAINT `equipment_attachments_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  ADD CONSTRAINT `equipment_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `equipment_categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `inventory_campaigns`
--
ALTER TABLE `inventory_campaigns`
  ADD CONSTRAINT `inventory_campaigns_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_campaigns_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_campaigns_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `inventory_campaign_items`
--
ALTER TABLE `inventory_campaign_items`
  ADD CONSTRAINT `inventory_campaign_items_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `inventory_campaigns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_campaign_items_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_campaign_items_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_campaign_items_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_movements_ibfk_2` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_movements_ibfk_3` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_movements_ibfk_4` FOREIGN KEY (`from_service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_movements_ibfk_5` FOREIGN KEY (`to_service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_movements_ibfk_6` FOREIGN KEY (`from_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_movements_ibfk_7` FOREIGN KEY (`to_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_movements_ibfk_8` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `maintenance_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maintenance_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maintenance_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `maintenance_contracts`
--
ALTER TABLE `maintenance_contracts`
  ADD CONSTRAINT `maintenance_contracts_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `maintenance_items`
--
ALTER TABLE `maintenance_items`
  ADD CONSTRAINT `maintenance_items_ibfk_1` FOREIGN KEY (`maintenance_id`) REFERENCES `maintenance` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `models`
--
ALTER TABLE `models`
  ADD CONSTRAINT `models_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `models_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `equipment_categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reformations`
--
ALTER TABLE `reformations`
  ADD CONSTRAINT `reformations_ibfk_1` FOREIGN KEY (`proposed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reformations_ibfk_2` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reformations_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `reformation_decisions`
--
ALTER TABLE `reformation_decisions`
  ADD CONSTRAINT `reformation_decisions_ibfk_1` FOREIGN KEY (`reformation_id`) REFERENCES `reformations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reformation_decisions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `reformation_items`
--
ALTER TABLE `reformation_items`
  ADD CONSTRAINT `reformation_items_ibfk_1` FOREIGN KEY (`reformation_id`) REFERENCES `reformations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reformation_items_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`);

--
-- Contraintes pour la table `reformation_workflow_logs`
--
ALTER TABLE `reformation_workflow_logs`
  ADD CONSTRAINT `reformation_workflow_logs_ibfk_1` FOREIGN KEY (`reformation_id`) REFERENCES `reformations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reformation_workflow_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
