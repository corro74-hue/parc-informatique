-- =====================================================================
-- BASE DE DONNÉES : parc_informatique
-- Projet : Gestion du Parc Informatique & Réformes
-- MariaDB 10.4+ / MySQL 8+
-- Encodage : utf8mb4_unicode_ci
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+01:00";

-- =====================================================================
-- CRÉATION DE LA BASE
-- =====================================================================
DROP DATABASE IF EXISTS parc_informatique;
CREATE DATABASE parc_informatique
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE parc_informatique;

-- =====================================================================
-- 1. AUTHENTIFICATION & DROITS
-- =====================================================================

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NULL,
    avatar VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,
    failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL,
    two_factor_secret VARCHAR(255) NULL,
    two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    slug VARCHAR(80) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    module VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_module (module)
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username_date (username, attempted_at),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    email VARCHAR(180) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    payload MEDIUMTEXT NOT NULL,
    last_activity INT UNSIGNED NOT NULL,
    INDEX idx_user (user_id),
    INDEX idx_activity (last_activity),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- 2. STRUCTURE ORGANISATIONNELLE
-- =====================================================================

CREATE TABLE sites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    address VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT UNSIGNED NULL,
    code VARCHAR(20) NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    INDEX idx_site (site_id),
    INDEX idx_name (name)
) ENGINE=InnoDB;

CREATE TABLE locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id BIGINT UNSIGNED NULL,
    building VARCHAR(100) NULL,
    floor VARCHAR(20) NULL,
    room VARCHAR(50) NULL,
    full_name VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    INDEX idx_service (service_id)
) ENGINE=InnoDB;

CREATE TABLE employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id BIGINT UNSIGNED NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    matricule VARCHAR(50) NULL UNIQUE,
    email VARCHAR(180) NULL,
    phone VARCHAR(30) NULL,
    function_title VARCHAR(150) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    INDEX idx_service (service_id),
    INDEX idx_name (last_name, first_name)
) ENGINE=InnoDB;

-- =====================================================================
-- 3. PARC INFORMATIQUE
-- =====================================================================

CREATE TABLE equipment_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    icon VARCHAR(50) NULL,
    default_amortization_years INT UNSIGNED DEFAULT 5,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES equipment_categories(id) ON DELETE SET NULL,
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB;

CREATE TABLE equipment_statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL,
    color VARCHAR(20) NOT NULL DEFAULT '#6c757d',
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    is_final TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE brands (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE models (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES equipment_categories(id) ON DELETE SET NULL,
    UNIQUE KEY uq_brand_model (brand_id, name),
    INDEX idx_category (category_id)
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    contact_name VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(180) NULL,
    address VARCHAR(255) NULL,
    nif VARCHAR(50) NULL,
    rc VARCHAR(50) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE equipment (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventory_number VARCHAR(50) NOT NULL UNIQUE,
    category_id BIGINT UNSIGNED NOT NULL,
    designation VARCHAR(180) NOT NULL,
    brand_id BIGINT UNSIGNED NULL,
    model_id BIGINT UNSIGNED NULL,
    model_text VARCHAR(150) NULL,
    serial_number VARCHAR(150) NULL,
    site_id BIGINT UNSIGNED NULL,
    service_id BIGINT UNSIGNED NULL,
    location_id BIGINT UNSIGNED NULL,
    responsible_id BIGINT UNSIGNED NULL,
    supplier_id BIGINT UNSIGNED NULL,
    status_id BIGINT UNSIGNED NOT NULL,
    acquisition_date DATE NULL,
    commissioning_date DATE NULL,
    acquisition_value DECIMAL(15,2) NOT NULL DEFAULT 0,
    residual_value DECIMAL(15,2) NOT NULL DEFAULT 0,
    amortization_years INT UNSIGNED NULL,
    invoice_number VARCHAR(80) NULL,
    warranty_end_date DATE NULL,
    qr_code_path VARCHAR(255) NULL,
    barcode VARCHAR(100) NULL,
    technical_specs TEXT NULL,
    notes TEXT NULL,
    photo_path VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES equipment_categories(id),
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE SET NULL,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    FOREIGN KEY (responsible_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (status_id) REFERENCES equipment_statuses(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_inventory (inventory_number),
    INDEX idx_serial (serial_number),
    INDEX idx_category (category_id),
    INDEX idx_status (status_id),
    INDEX idx_service (service_id),
    INDEX idx_site (site_id),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB;

CREATE TABLE equipment_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    category VARCHAR(50) NULL,
    uploaded_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_equipment (equipment_id)
) ENGINE=InnoDB;

-- =====================================================================
-- 4. AFFECTATIONS
-- =====================================================================

CREATE TABLE assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,
    site_id BIGINT UNSIGNED NULL,
    service_id BIGINT UNSIGNED NULL,
    location_id BIGINT UNSIGNED NULL,
    employee_id BIGINT UNSIGNED NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    reason VARCHAR(255) NULL,
    document_path VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_equipment (equipment_id),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB;

-- =====================================================================
-- 5. MAINTENANCE
-- =====================================================================

CREATE TABLE maintenance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,
    type ENUM('preventive','corrective','curative','upgrade') NOT NULL DEFAULT 'corrective',
    status ENUM('open','in_progress','waiting_parts','completed','cancelled') NOT NULL DEFAULT 'open',
    reported_at DATETIME NOT NULL,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    reported_by BIGINT UNSIGNED NULL,
    technician VARCHAR(150) NULL,
    supplier_id BIGINT UNSIGNED NULL,
    problem_description TEXT NOT NULL,
    diagnosis TEXT NULL,
    work_done TEXT NULL,
    result ENUM('fixed','unfixed','replaced','pending') NULL,
    cost DECIMAL(15,2) NOT NULL DEFAULT 0,
    downtime_hours INT UNSIGNED NULL,
    invoice_number VARCHAR(80) NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_equipment (equipment_id),
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_reported (reported_at)
) ENGINE=InnoDB;

CREATE TABLE maintenance_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    maintenance_id BIGINT UNSIGNED NOT NULL,
    item_name VARCHAR(180) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    serial_number VARCHAR(150) NULL,
    FOREIGN KEY (maintenance_id) REFERENCES maintenance(id) ON DELETE CASCADE,
    INDEX idx_maintenance (maintenance_id)
) ENGINE=InnoDB;

CREATE TABLE maintenance_contracts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    reference VARCHAR(80) NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    amount DECIMAL(15,2) NULL,
    description TEXT NULL,
    document_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB;

-- =====================================================================
-- 6. RÉFORME
-- =====================================================================

CREATE TABLE reformations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    reason ENUM('obsolescence','breakdown','wear','end_of_life','other') NOT NULL,
    reason_details TEXT NULL,
    status ENUM(
        'draft',
        'proposed',
        'under_review',
        'approved',
        'rejected',
        'pv_generated',
        'exit_voucher_generated',
        'completed',
        'cancelled'
    ) NOT NULL DEFAULT 'draft',
    proposed_by BIGINT UNSIGNED NULL,
    proposed_at DATETIME NULL,
    submitted_at DATETIME NULL,
    decided_at DATETIME NULL,
    decided_by BIGINT UNSIGNED NULL,
    decision_notes TEXT NULL,
    commission_reference VARCHAR(80) NULL,
    meeting_date DATE NULL,
    total_value DECIMAL(15,2) NOT NULL DEFAULT 0,
    pv_path VARCHAR(255) NULL,
    pv_generated_at DATETIME NULL,
    exit_voucher_path VARCHAR(255) NULL,
    exit_voucher_generated_at DATETIME NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (proposed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (decided_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_reference (reference),
    INDEX idx_status (status),
    INDEX idx_dates (submitted_at, decided_at)
) ENGINE=InnoDB;

CREATE TABLE reformation_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reformation_id BIGINT UNSIGNED NOT NULL,
    equipment_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    estimated_value DECIMAL(15,2) NOT NULL DEFAULT 0,
    condition_notes TEXT NULL,
    photos TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reformation_id) REFERENCES reformations(id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE RESTRICT,
    UNIQUE KEY uq_reform_equip (reformation_id, equipment_id),
    INDEX idx_equipment (equipment_id)
) ENGINE=InnoDB;

CREATE TABLE reformation_workflow_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reformation_id BIGINT UNSIGNED NOT NULL,
    from_status VARCHAR(40) NULL,
    to_status VARCHAR(40) NOT NULL,
    action VARCHAR(80) NOT NULL,
    comment TEXT NULL,
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reformation_id) REFERENCES reformations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_reform (reformation_id),
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

CREATE TABLE reformation_decisions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reformation_id BIGINT UNSIGNED NOT NULL,
    decision ENUM('approved','rejected','postponed') NOT NULL,
    decision_date DATETIME NOT NULL,
    commission_members TEXT NULL,
    notes TEXT NULL,
    document_path VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reformation_id) REFERENCES reformations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_reform (reformation_id)
) ENGINE=InnoDB;

-- =====================================================================
-- 7. INVENTAIRE PHYSIQUE (CAMPAGNES)
-- =====================================================================

CREATE TABLE inventory_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    site_id BIGINT UNSIGNED NULL,
    service_id BIGINT UNSIGNED NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('planned','in_progress','completed','cancelled') NOT NULL DEFAULT 'planned',
    description TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE inventory_campaign_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    equipment_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending','found','missing','moved','damaged') NOT NULL DEFAULT 'pending',
    verified_at DATETIME NULL,
    verified_by BIGINT UNSIGNED NULL,
    location_id BIGINT UNSIGNED NULL,
    observations TEXT NULL,
    FOREIGN KEY (campaign_id) REFERENCES inventory_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    UNIQUE KEY uq_campaign_equip (campaign_id, equipment_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================================================================
-- 8. DOCUMENTS
-- =====================================================================

CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(80) NULL,
    type ENUM('pv_reform','exit_voucher','invoice','report','other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id BIGINT UNSIGNED NULL,
    current_version INT UNSIGNED NOT NULL DEFAULT 1,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NULL,
    size_bytes BIGINT UNSIGNED NULL,
    is_signed TINYINT(1) NOT NULL DEFAULT 0,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_type (type)
) ENGINE=InnoDB;

CREATE TABLE document_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    version INT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    change_notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_doc_version (document_id, version)
) ENGINE=InnoDB;

-- =====================================================================
-- 9. MOUVEMENTS & AUDIT
-- =====================================================================

CREATE TABLE inventory_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,
    movement_type ENUM('acquisition','assignment','transfer','maintenance','reform','disposal','return','loss') NOT NULL,
    from_location_id BIGINT UNSIGNED NULL,
    to_location_id BIGINT UNSIGNED NULL,
    from_service_id BIGINT UNSIGNED NULL,
    to_service_id BIGINT UNSIGNED NULL,
    from_employee_id BIGINT UNSIGNED NULL,
    to_employee_id BIGINT UNSIGNED NULL,
    reference_type VARCHAR(50) NULL,
    reference_id BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    moved_at DATETIME NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (from_location_id) REFERENCES locations(id) ON DELETE SET NULL,
    FOREIGN KEY (to_location_id) REFERENCES locations(id) ON DELETE SET NULL,
    FOREIGN KEY (from_service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (to_service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (from_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (to_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_equipment (equipment_id),
    INDEX idx_type (movement_type),
    INDEX idx_date (moved_at)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    user_name VARCHAR(180) NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(80) NULL,
    entity_id BIGINT UNSIGNED NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    url VARCHAR(500) NULL,
    method VARCHAR(10) NULL,
    severity ENUM('info','warning','error','critical') NOT NULL DEFAULT 'info',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_action (action),
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
    icon VARCHAR(50) NULL,
    link VARCHAR(500) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB;

-- =====================================================================
-- 10. PARAMÉTRAGE
-- =====================================================================

CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(120) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `group` VARCHAR(50) NOT NULL DEFAULT 'general',
    type ENUM('string','int','bool','json','text') NOT NULL DEFAULT 'string',
    description VARCHAR(255) NULL,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE amortization_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    years INT UNSIGNED NOT NULL,
    method ENUM('linear','declining') NOT NULL DEFAULT 'linear',
    rate DECIMAL(5,2) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES equipment_categories(id) ON DELETE CASCADE,
    UNIQUE KEY uq_category (category_id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- FIN DU SCHÉMA
-- =====================================================================