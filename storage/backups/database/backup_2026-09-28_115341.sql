-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: parc_informatique
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `amortization_rules`
--

DROP TABLE IF EXISTS `amortization_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amortization_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `years` int(10) unsigned NOT NULL,
  `method` enum('linear','declining') NOT NULL DEFAULT 'linear',
  `rate` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_category` (`category_id`),
  CONSTRAINT `amortization_rules_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `equipment_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amortization_rules`
--

LOCK TABLES `amortization_rules` WRITE;
/*!40000 ALTER TABLE `amortization_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `amortization_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `equipment_id` bigint(20) unsigned NOT NULL,
  `site_id` bigint(20) unsigned DEFAULT NULL,
  `service_id` bigint(20) unsigned DEFAULT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `employee_id` bigint(20) unsigned DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`),
  KEY `service_id` (`service_id`),
  KEY `location_id` (`location_id`),
  KEY `employee_id` (`employee_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_equipment` (`equipment_id`),
  KEY `idx_dates` (`start_date`,`end_date`),
  CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assignments_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assignments_ibfk_5` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assignments_ibfk_6` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignments`
--

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_name` varchar(180) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `entity_type` varchar(80) DEFAULT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `severity` enum('info','warning','error','critical') NOT NULL DEFAULT 'info',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_action` (`action`),
  KEY `idx_date` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,2,'Admin Nouveau','create','equipment',1,NULL,'{\"inventory_number\":\"INF-2026-0001\",\"designation\":\"TechnoLite\",\"category_id\":2,\"brand_id\":1,\"status_id\":1,\"serial_number\":\"CZ1234569\",\"acquisition_value\":25000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','/parc-informatique/public/equipment','POST','info','2026-09-28 09:36:03');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_logs`
--

DROP TABLE IF EXISTS `backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('database','files','full') NOT NULL DEFAULT 'database',
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `size_bytes` bigint(20) unsigned NOT NULL DEFAULT 0,
  `status` enum('success','failed','partial') NOT NULL DEFAULT 'success',
  `error_message` text DEFAULT NULL,
  `triggered_by` enum('manual','cron','system') NOT NULL DEFAULT 'manual',
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `duration_seconds` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_backup_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_logs`
--

LOCK TABLES `backup_logs` WRITE;
/*!40000 ALTER TABLE `backup_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (1,'HP','2026-09-28 09:18:48'),(2,'Dell','2026-09-28 09:18:48'),(3,'Lenovo','2026-09-28 09:18:48'),(4,'Asus','2026-09-28 09:18:48'),(5,'Acer','2026-09-28 09:18:48'),(6,'Epson','2026-09-28 09:18:48'),(7,'Canon','2026-09-28 09:18:48'),(8,'APC','2026-09-28 09:18:48'),(9,'Cisco','2026-09-28 09:18:48'),(10,'TP-Link','2026-09-28 09:18:48');
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_versions`
--

DROP TABLE IF EXISTS `document_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` bigint(20) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `change_notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_doc_version` (`document_id`,`version`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `document_versions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_versions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_versions`
--

LOCK TABLES `document_versions` WRITE;
/*!40000 ALTER TABLE `document_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(80) DEFAULT NULL,
  `type` enum('pv_reform','exit_voucher','invoice','report','other') NOT NULL,
  `title` varchar(255) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `current_version` int(10) unsigned NOT NULL DEFAULT 1,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `size_bytes` bigint(20) unsigned DEFAULT NULL,
  `is_signed` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_type` (`type`),
  CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) unsigned DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `matricule` varchar(50) DEFAULT NULL,
  `email` varchar(180) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `function_title` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`),
  KEY `idx_service` (`service_id`),
  KEY `idx_name` (`last_name`,`first_name`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_number` varchar(50) NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `designation` varchar(180) NOT NULL,
  `brand_id` bigint(20) unsigned DEFAULT NULL,
  `model_id` bigint(20) unsigned DEFAULT NULL,
  `model_text` varchar(150) DEFAULT NULL,
  `serial_number` varchar(150) DEFAULT NULL,
  `site_id` bigint(20) unsigned DEFAULT NULL,
  `service_id` bigint(20) unsigned DEFAULT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `responsible_id` bigint(20) unsigned DEFAULT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  `acquisition_date` date DEFAULT NULL,
  `commissioning_date` date DEFAULT NULL,
  `acquisition_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `residual_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amortization_years` int(10) unsigned DEFAULT NULL,
  `invoice_number` varchar(80) DEFAULT NULL,
  `warranty_end_date` date DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `technical_specs` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_number` (`inventory_number`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`),
  KEY `location_id` (`location_id`),
  KEY `responsible_id` (`responsible_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_inventory` (`inventory_number`),
  KEY `idx_serial` (`serial_number`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status` (`status_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_site` (`site_id`),
  KEY `idx_deleted` (`deleted_at`),
  CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `equipment_categories` (`id`),
  CONSTRAINT `equipment_ibfk_10` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_ibfk_11` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_ibfk_3` FOREIGN KEY (`model_id`) REFERENCES `models` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_ibfk_4` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_ibfk_5` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_ibfk_6` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_ibfk_7` FOREIGN KEY (`responsible_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_ibfk_8` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_ibfk_9` FOREIGN KEY (`status_id`) REFERENCES `equipment_statuses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment`
--

LOCK TABLES `equipment` WRITE;
/*!40000 ALTER TABLE `equipment` DISABLE KEYS */;
INSERT INTO `equipment` VALUES (1,'INF-2026-0001',2,'TechnoLite',1,NULL,'201 HP','CZ1234569',1,4,NULL,NULL,NULL,1,'2025-09-28',NULL,25000.00,25000.00,1,'FAC-2024-0047','2026-09-25',NULL,NULL,NULL,NULL,NULL,2,NULL,'2026-09-28 09:36:03','2026-09-28 09:36:03',NULL);
/*!40000 ALTER TABLE `equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment_attachments`
--

DROP TABLE IF EXISTS `equipment_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `equipment_id` bigint(20) unsigned NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` bigint(20) unsigned NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_equipment` (`equipment_id`),
  CONSTRAINT `equipment_attachments_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipment_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_attachments`
--

LOCK TABLES `equipment_attachments` WRITE;
/*!40000 ALTER TABLE `equipment_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `equipment_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment_categories`
--

DROP TABLE IF EXISTS `equipment_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `default_amortization_years` int(10) unsigned DEFAULT 5,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_parent` (`parent_id`),
  CONSTRAINT `equipment_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `equipment_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_categories`
--

LOCK TABLES `equipment_categories` WRITE;
/*!40000 ALTER TABLE `equipment_categories` DISABLE KEYS */;
INSERT INTO `equipment_categories` VALUES (1,NULL,'PC','Unité centrale / PC','Ordinateurs de bureau et portables',NULL,5,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(2,NULL,'SCREEN','Écran','Moniteurs et écrans',NULL,5,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(3,NULL,'PRINT','Imprimante','Imprimantes et multifonctions',NULL,5,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(4,NULL,'SCAN','Scanner','Scanners',NULL,5,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(5,NULL,'UPS','Onduleur','Onduleurs et batteries',NULL,5,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(6,NULL,'NETWORK','Équipement réseau','Switch, routeur, firewall',NULL,8,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(7,NULL,'SERVER','Serveur','Serveurs physiques',NULL,5,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(8,NULL,'PHONE','Téléphone IP','Téléphones IP',NULL,5,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(9,NULL,'VIDEO','Vidéoprojecteur','Vidéoprojecteurs',NULL,5,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(10,NULL,'OTHER','Autre','Autres équipements',NULL,5,1,'2026-09-28 09:18:48','2026-09-28 09:18:48');
/*!40000 ALTER TABLE `equipment_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment_statuses`
--

DROP TABLE IF EXISTS `equipment_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment_statuses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(80) NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#6c757d',
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_final` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_statuses`
--

LOCK TABLES `equipment_statuses` WRITE;
/*!40000 ALTER TABLE `equipment_statuses` DISABLE KEYS */;
INSERT INTO `equipment_statuses` VALUES (1,'in_service','En service','#198754',1,0,1),(2,'in_stock','En stock','#0dcaf0',1,0,2),(3,'maintenance','En maintenance','#ffc107',1,0,3),(4,'out_of_service','Hors service','#dc3545',0,0,4),(5,'obsolete','Obsolète','#6c757d',0,0,5),(6,'to_reform','À réformer','#fd7e14',0,0,6),(7,'reformed','Réformé','#495057',0,1,7),(8,'lost','Perdu / Volé','#842029',0,1,8),(9,'disposed','Sorti d\'inventaire','#212529',0,1,9);
/*!40000 ALTER TABLE `equipment_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_campaign_items`
--

DROP TABLE IF EXISTS `inventory_campaign_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_campaign_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `equipment_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending','found','missing','moved','damaged') NOT NULL DEFAULT 'pending',
  `verified_at` datetime DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `observations` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_campaign_equip` (`campaign_id`,`equipment_id`),
  KEY `equipment_id` (`equipment_id`),
  KEY `verified_by` (`verified_by`),
  KEY `location_id` (`location_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `inventory_campaign_items_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `inventory_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_campaign_items_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_campaign_items_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_campaign_items_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_campaign_items`
--

LOCK TABLES `inventory_campaign_items` WRITE;
/*!40000 ALTER TABLE `inventory_campaign_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_campaign_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_campaigns`
--

DROP TABLE IF EXISTS `inventory_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_campaigns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `name` varchar(180) NOT NULL,
  `site_id` bigint(20) unsigned DEFAULT NULL,
  `service_id` bigint(20) unsigned DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('planned','in_progress','completed','cancelled') NOT NULL DEFAULT 'planned',
  `description` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `site_id` (`site_id`),
  KEY `service_id` (`service_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `inventory_campaigns_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_campaigns_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_campaigns_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_campaigns`
--

LOCK TABLES `inventory_campaigns` WRITE;
/*!40000 ALTER TABLE `inventory_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_movements`
--

DROP TABLE IF EXISTS `inventory_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `equipment_id` bigint(20) unsigned NOT NULL,
  `movement_type` enum('acquisition','assignment','transfer','maintenance','reform','disposal','return','loss') NOT NULL,
  `from_location_id` bigint(20) unsigned DEFAULT NULL,
  `to_location_id` bigint(20) unsigned DEFAULT NULL,
  `from_service_id` bigint(20) unsigned DEFAULT NULL,
  `to_service_id` bigint(20) unsigned DEFAULT NULL,
  `from_employee_id` bigint(20) unsigned DEFAULT NULL,
  `to_employee_id` bigint(20) unsigned DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `moved_at` datetime NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `from_location_id` (`from_location_id`),
  KEY `to_location_id` (`to_location_id`),
  KEY `from_service_id` (`from_service_id`),
  KEY `to_service_id` (`to_service_id`),
  KEY `from_employee_id` (`from_employee_id`),
  KEY `to_employee_id` (`to_employee_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_equipment` (`equipment_id`),
  KEY `idx_type` (`movement_type`),
  KEY `idx_date` (`moved_at`),
  CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_movements_ibfk_2` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_movements_ibfk_3` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_movements_ibfk_4` FOREIGN KEY (`from_service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_movements_ibfk_5` FOREIGN KEY (`to_service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_movements_ibfk_6` FOREIGN KEY (`from_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_movements_ibfk_7` FOREIGN KEY (`to_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_movements_ibfk_8` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_movements`
--

LOCK TABLES `inventory_movements` WRITE;
/*!40000 ALTER TABLE `inventory_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) unsigned DEFAULT NULL,
  `building` varchar(100) DEFAULT NULL,
  `floor` varchar(20) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_service` (`service_id`),
  CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `attempted_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_username_date` (`username`,`attempted_at`),
  KEY `idx_ip` (`ip_address`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES (1,'GERANTE','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0',0,'2026-09-28 09:19:50'),(2,'admin','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0',0,'2026-09-28 09:19:52'),(3,'admin@sadid.com','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0',0,'2026-09-28 09:19:57'),(4,'admin','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0',0,'2026-09-28 09:20:08'),(5,'admin','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0',0,'2026-09-28 09:20:34'),(6,'admin2','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0',1,'2026-09-28 09:29:10'),(7,'admin','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0',1,'2026-09-28 09:30:45'),(8,'admin2','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0',1,'2026-09-28 09:30:58'),(9,'admin2','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0',1,'2026-09-28 10:20:03');
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance`
--

DROP TABLE IF EXISTS `maintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `equipment_id` bigint(20) unsigned NOT NULL,
  `type` enum('preventive','corrective','curative','upgrade') NOT NULL DEFAULT 'corrective',
  `status` enum('open','in_progress','waiting_parts','completed','cancelled') NOT NULL DEFAULT 'open',
  `reported_at` datetime NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `reported_by` bigint(20) unsigned DEFAULT NULL,
  `technician` varchar(150) DEFAULT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `problem_description` text NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `work_done` text DEFAULT NULL,
  `result` enum('fixed','unfixed','replaced','pending') DEFAULT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `downtime_hours` int(10) unsigned DEFAULT NULL,
  `invoice_number` varchar(80) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reported_by` (`reported_by`),
  KEY `supplier_id` (`supplier_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_equipment` (`equipment_id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_reported` (`reported_at`),
  CONSTRAINT `maintenance_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `maintenance_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `maintenance_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `maintenance_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance`
--

LOCK TABLES `maintenance` WRITE;
/*!40000 ALTER TABLE `maintenance` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_contracts`
--

DROP TABLE IF EXISTS `maintenance_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_contracts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `name` varchar(180) NOT NULL,
  `reference` varchar(80) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `idx_dates` (`start_date`,`end_date`),
  CONSTRAINT `maintenance_contracts_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_contracts`
--

LOCK TABLES `maintenance_contracts` WRITE;
/*!40000 ALTER TABLE `maintenance_contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenance_contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_items`
--

DROP TABLE IF EXISTS `maintenance_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `maintenance_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(180) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `serial_number` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_maintenance` (`maintenance_id`),
  CONSTRAINT `maintenance_items_ibfk_1` FOREIGN KEY (`maintenance_id`) REFERENCES `maintenance` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_items`
--

LOCK TABLES `maintenance_items` WRITE;
/*!40000 ALTER TABLE `maintenance_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenance_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `models`
--

DROP TABLE IF EXISTS `models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `models` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `brand_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_brand_model` (`brand_id`,`name`),
  KEY `idx_category` (`category_id`),
  CONSTRAINT `models_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  CONSTRAINT `models_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `equipment_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `models`
--

LOCK TABLES `models` WRITE;
/*!40000 ALTER TABLE `models` DISABLE KEYS */;
/*!40000 ALTER TABLE `models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `icon` varchar(50) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`,`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_history`
--

DROP TABLE IF EXISTS `password_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_created` (`user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_history`
--

LOCK TABLES `password_history` WRITE;
/*!40000 ALTER TABLE `password_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(180) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_module` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'equipment.view','equipment','Voir les équipements','2026-09-28 09:18:48'),(2,'equipment.create','equipment','Créer un équipement','2026-09-28 09:18:48'),(3,'equipment.edit','equipment','Modifier un équipement','2026-09-28 09:18:48'),(4,'equipment.delete','equipment','Supprimer un équipement','2026-09-28 09:18:48'),(5,'equipment.export','equipment','Exporter les équipements','2026-09-28 09:18:48'),(6,'equipment.import','equipment','Importer des équipements','2026-09-28 09:18:48'),(7,'assignment.view','assignment','Voir les affectations','2026-09-28 09:18:48'),(8,'assignment.create','assignment','Créer une affectation','2026-09-28 09:18:48'),(9,'assignment.edit','assignment','Modifier une affectation','2026-09-28 09:18:48'),(10,'maintenance.view','maintenance','Voir les maintenances','2026-09-28 09:18:48'),(11,'maintenance.create','maintenance','Créer une maintenance','2026-09-28 09:18:48'),(12,'maintenance.edit','maintenance','Modifier une maintenance','2026-09-28 09:18:48'),(13,'maintenance.delete','maintenance','Supprimer une maintenance','2026-09-28 09:18:48'),(14,'reform.view','reform','Voir les réformes','2026-09-28 09:18:48'),(15,'reform.create','reform','Créer une réforme','2026-09-28 09:18:48'),(16,'reform.edit','reform','Modifier une réforme','2026-09-28 09:18:48'),(17,'reform.approve','reform','Approuver une réforme','2026-09-28 09:18:48'),(18,'reform.reject','reform','Refuser une réforme','2026-09-28 09:18:48'),(19,'reform.generate_pv','reform','Générer le PV de réforme','2026-09-28 09:18:48'),(20,'document.view','document','Voir les documents','2026-09-28 09:18:48'),(21,'document.download','document','Télécharger les documents','2026-09-28 09:18:48'),(22,'report.view','report','Voir les rapports','2026-09-28 09:18:48'),(23,'report.export','report','Exporter les rapports','2026-09-28 09:18:48'),(24,'users.view','users','Voir les utilisateurs','2026-09-28 09:18:48'),(25,'users.create','users','Créer un utilisateur','2026-09-28 09:18:48'),(26,'users.edit','users','Modifier un utilisateur','2026-09-28 09:18:48'),(27,'users.delete','users','Supprimer un utilisateur','2026-09-28 09:18:48'),(28,'settings.view','settings','Voir les paramètres','2026-09-28 09:18:48'),(29,'settings.manage','settings','Gérer les paramètres','2026-09-28 09:18:48'),(30,'audit.view','audit','Consulter le journal d\'audit','2026-09-28 09:18:48');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_limits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT 0,
  `first_hit_at` int(10) unsigned NOT NULL,
  `last_hit_at` int(10) unsigned NOT NULL,
  `blocked_until` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_identifier_route` (`identifier`,`route`),
  KEY `idx_last_hit` (`last_hit_at`),
  KEY `idx_blocked` (`blocked_until`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_limits`
--

LOCK TABLES `rate_limits` WRITE;
/*!40000 ALTER TABLE `rate_limits` DISABLE KEYS */;
INSERT INTO `rate_limits` VALUES (3,'ip:127.0.0.1','/login',1,1790590803,1790590803,NULL),(4,'user:2|ip:127.0.0.1','/users',1,1790588012,1790588012,NULL);
/*!40000 ALTER TABLE `rate_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reformation_decisions`
--

DROP TABLE IF EXISTS `reformation_decisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reformation_decisions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reformation_id` bigint(20) unsigned NOT NULL,
  `decision` enum('approved','rejected','postponed') NOT NULL,
  `decision_date` datetime NOT NULL,
  `commission_members` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_reform` (`reformation_id`),
  CONSTRAINT `reformation_decisions_ibfk_1` FOREIGN KEY (`reformation_id`) REFERENCES `reformations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reformation_decisions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reformation_decisions`
--

LOCK TABLES `reformation_decisions` WRITE;
/*!40000 ALTER TABLE `reformation_decisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `reformation_decisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reformation_items`
--

DROP TABLE IF EXISTS `reformation_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reformation_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reformation_id` bigint(20) unsigned NOT NULL,
  `equipment_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `estimated_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `condition_notes` text DEFAULT NULL,
  `photos` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reform_equip` (`reformation_id`,`equipment_id`),
  KEY `idx_equipment` (`equipment_id`),
  CONSTRAINT `reformation_items_ibfk_1` FOREIGN KEY (`reformation_id`) REFERENCES `reformations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reformation_items_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reformation_items`
--

LOCK TABLES `reformation_items` WRITE;
/*!40000 ALTER TABLE `reformation_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `reformation_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reformation_workflow_logs`
--

DROP TABLE IF EXISTS `reformation_workflow_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reformation_workflow_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reformation_id` bigint(20) unsigned NOT NULL,
  `from_status` varchar(40) DEFAULT NULL,
  `to_status` varchar(40) NOT NULL,
  `action` varchar(80) NOT NULL,
  `comment` text DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_reform` (`reformation_id`),
  KEY `idx_date` (`created_at`),
  CONSTRAINT `reformation_workflow_logs_ibfk_1` FOREIGN KEY (`reformation_id`) REFERENCES `reformations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reformation_workflow_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reformation_workflow_logs`
--

LOCK TABLES `reformation_workflow_logs` WRITE;
/*!40000 ALTER TABLE `reformation_workflow_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `reformation_workflow_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reformations`
--

DROP TABLE IF EXISTS `reformations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reformations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `reason` enum('obsolescence','breakdown','wear','end_of_life','other') NOT NULL,
  `reason_details` text DEFAULT NULL,
  `status` enum('draft','proposed','under_review','approved','rejected','pv_generated','exit_voucher_generated','completed','cancelled') NOT NULL DEFAULT 'draft',
  `proposed_by` bigint(20) unsigned DEFAULT NULL,
  `proposed_at` datetime DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `decided_by` bigint(20) unsigned DEFAULT NULL,
  `decision_notes` text DEFAULT NULL,
  `commission_reference` varchar(80) DEFAULT NULL,
  `meeting_date` date DEFAULT NULL,
  `total_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `pv_path` varchar(255) DEFAULT NULL,
  `pv_generated_at` datetime DEFAULT NULL,
  `exit_voucher_path` varchar(255) DEFAULT NULL,
  `exit_voucher_generated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `proposed_by` (`proposed_by`),
  KEY `decided_by` (`decided_by`),
  KEY `created_by` (`created_by`),
  KEY `idx_reference` (`reference`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`submitted_at`,`decided_at`),
  CONSTRAINT `reformations_ibfk_1` FOREIGN KEY (`proposed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reformations_ibfk_2` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reformations_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reformations`
--

LOCK TABLES `reformations` WRITE;
/*!40000 ALTER TABLE `reformations` DISABLE KEYS */;
/*!40000 ALTER TABLE `reformations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,19),(1,20),(1,21),(1,22),(1,23),(1,24),(1,25),(1,26),(1,27),(1,28),(1,29),(1,30),(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(2,8),(2,9),(2,10),(2,11),(2,12),(2,13),(2,14),(2,15),(2,16),(2,19),(2,20),(2,21),(2,22),(2,23),(3,1),(3,14),(3,17),(3,18),(3,20),(3,21),(3,22),(4,1),(4,7),(4,10),(4,14),(4,20),(4,22);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrateur','admin','Accès total à toutes les fonctionnalités',1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(2,'Gestionnaire','manager','Gestion du parc, affectations, maintenance, réformes',1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(3,'Commission','commission','Examen et validation des propositions de réforme',1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(4,'Consultation','viewer','Lecture seule',1,'2026-09-28 09:18:48','2026-09-28 09:18:48');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_site` (`site_id`),
  KEY `idx_name` (`name`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,1,'DIR','Direction Générale',NULL,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(2,1,'DF','Direction Financière',NULL,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(3,1,'RH','Ressources Humaines',NULL,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(4,1,'INFO','Service Informatique',NULL,1,'2026-09-28 09:18:48','2026-09-28 09:18:48'),(5,1,'COMP','Comptabilité',NULL,1,'2026-09-28 09:18:48','2026-09-28 09:18:48');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `payload` mediumtext NOT NULL,
  `last_activity` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_activity` (`last_activity`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('cmtu68vk3bfl6tjvar0iattcvm',2,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','user_id|i:2;username|s:6:\"admin2\";full_name|s:13:\"Admin Nouveau\";roles|a:1:{i:0;s:5:\"admin\";}permissions|a:30:{i:0;s:14:\"equipment.view\";i:1;s:16:\"equipment.create\";i:2;s:14:\"equipment.edit\";i:3;s:16:\"equipment.delete\";i:4;s:16:\"equipment.export\";i:5;s:16:\"equipment.import\";i:6;s:15:\"assignment.view\";i:7;s:17:\"assignment.create\";i:8;s:15:\"assignment.edit\";i:9;s:16:\"maintenance.view\";i:10;s:18:\"maintenance.create\";i:11;s:16:\"maintenance.edit\";i:12;s:18:\"maintenance.delete\";i:13;s:11:\"reform.view\";i:14;s:13:\"reform.create\";i:15;s:11:\"reform.edit\";i:16;s:14:\"reform.approve\";i:17;s:13:\"reform.reject\";i:18;s:18:\"reform.generate_pv\";i:19;s:13:\"document.view\";i:20;s:17:\"document.download\";i:21;s:11:\"report.view\";i:22;s:13:\"report.export\";i:23;s:10:\"users.view\";i:24;s:12:\"users.create\";i:25;s:10:\"users.edit\";i:26;s:12:\"users.delete\";i:27;s:13:\"settings.view\";i:28;s:15:\"settings.manage\";i:29;s:10:\"audit.view\";}logged_in_at|i:1790587750;last_activity|i:1790587750;must_change_password|b:0;_flash|a:0:{}_csrf_token|s:64:\"1cfaca9e4dbe7788bd99e0cc2235e17b0e65edaffc364896717a8b111cc8fa52\";',1790587750),('lc1ub5agneureq4fqpner09or5',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','_csrf_token|s:64:\"5970675d1d47618d0bc535f236aa6cbf17316dca2ef1e3838bf40c4bd9e27ea3\";',1790587618),('qqp0hgv5hh81ht8kenj1o24s20',2,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','user_id|i:2;username|s:6:\"admin2\";full_name|s:13:\"Admin Nouveau\";roles|a:1:{i:0;s:5:\"admin\";}permissions|a:30:{i:0;s:14:\"equipment.view\";i:1;s:16:\"equipment.create\";i:2;s:14:\"equipment.edit\";i:3;s:16:\"equipment.delete\";i:4;s:16:\"equipment.export\";i:5;s:16:\"equipment.import\";i:6;s:15:\"assignment.view\";i:7;s:17:\"assignment.create\";i:8;s:15:\"assignment.edit\";i:9;s:16:\"maintenance.view\";i:10;s:18:\"maintenance.create\";i:11;s:16:\"maintenance.edit\";i:12;s:18:\"maintenance.delete\";i:13;s:11:\"reform.view\";i:14;s:13:\"reform.create\";i:15;s:11:\"reform.edit\";i:16;s:14:\"reform.approve\";i:17;s:13:\"reform.reject\";i:18;s:18:\"reform.generate_pv\";i:19;s:13:\"document.view\";i:20;s:17:\"document.download\";i:21;s:11:\"report.view\";i:22;s:13:\"report.export\";i:23;s:10:\"users.view\";i:24;s:12:\"users.create\";i:25;s:10:\"users.edit\";i:26;s:12:\"users.delete\";i:27;s:13:\"settings.view\";i:28;s:15:\"settings.manage\";i:29;s:10:\"audit.view\";}logged_in_at|i:1790590803;last_activity|i:1790592818;must_change_password|b:0;_flash|a:0:{}_csrf_token|s:64:\"f2af05d4c58604eb60e69b3bf88b7dba835405f8f7de60692d39fb153cec3834\";',1790592818),('t1lhmul2ceo1ajk9oh62g6ajhd',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','_csrf_token|s:64:\"350322d66594ce23ea03524c3a11be28bc3d74641b49021f9c8beae2d41294b7\";_old|a:1:{s:8:\"username\";s:5:\"admin\";}_flash|a:0:{}',1790587234),('to4tvggjm766n7le9d3nc07saj',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','_flash|a:1:{s:7:\"success\";s:24:\"Vous êtes déconnecté.\";}',1790587853);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(120) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `type` enum('string','int','bool','json','text') NOT NULL DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'app.name','Gestion du Parc Informatique','general','string','Nom de l\'application','2026-09-28 09:18:48'),(2,'app.organization','Votre Organisation','general','string','Nom de l\'organisation','2026-09-28 09:18:48'),(3,'app.city','Alger','general','string','Ville','2026-09-28 09:18:48'),(4,'app.logo','','general','string','Chemin du logo','2026-09-28 09:18:48'),(5,'equipment.code_prefix','INF','equipment','string','Préfixe des numéros d\'inventaire','2026-09-28 09:18:48'),(6,'equipment.code_year','1','equipment','bool','Inclure l\'année dans le code','2026-09-28 09:18:48'),(7,'reform.code_prefix','REF','reform','string','Préfixe des références de réforme','2026-09-28 09:18:48'),(8,'alert.no_assignment_days','30','alerts','int','Jours avant alerte sans affectation','2026-09-28 09:18:48'),(9,'alert.obsolete_days','60','alerts','int','Jours avant alerte obsolescence','2026-09-28 09:18:48');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sites`
--

DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sites`
--

LOCK TABLES `sites` WRITE;
/*!40000 ALTER TABLE `sites` DISABLE KEYS */;
INSERT INTO `sites` VALUES (1,'SIEGE','Siège principal',NULL,'Alger',1,'2026-09-28 09:18:48','2026-09-28 09:18:48');
/*!40000 ALTER TABLE `sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_roles` (
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,1,'2026-09-28 09:18:48'),(2,1,'2026-09-28 09:26:33');
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `failed_attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_backup_codes` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@parc.local','$argon2id$v=19$m=65536,t=4,p=2$MGV6OU1YVWhzMXprM25TZw$gYoS/NKRHw21tweuIXRdto7NGL2ae5GhIjFck0NRnH4','Administrateur','Système',NULL,NULL,1,0,'2026-09-28 09:30:45','2026-12-27 09:30:45',NULL,NULL,0,NULL,NULL,0,NULL,NULL,'2026-09-28 09:18:48','2026-09-28 09:30:45',NULL),(2,'admin2','admin2@parc.local','$argon2id$v=19$m=65536,t=4,p=2$RVVHOFd6bGw2bmplYU1ERQ$nUZaUmTwUpLd0dVk0/uzSmCv89Fo8uJ8Wftk6GCvkAk','Admin','Nouveau',NULL,NULL,1,0,'2026-09-28 09:29:10','2026-12-27 09:29:10',NULL,NULL,0,NULL,NULL,0,NULL,NULL,'2026-09-28 09:26:33','2026-09-28 09:29:10',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'parc_informatique'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-28 11:53:41
