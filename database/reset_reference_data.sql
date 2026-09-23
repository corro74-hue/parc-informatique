-- =====================================================================
-- RÉINITIALISATION COMPLÈTE DES DONNÉES DE RÉFÉRENCE
-- =====================================================================
-- Ce script ÉCRASE toutes les données de référence avec des valeurs
-- correctement encodées en UTF-8.
--
-- ⚠️ IMPORTANT : à importer avec l'option --default-character-set=utf8mb4
-- =====================================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET character_set_client = utf8mb4;
SET character_set_connection = utf8mb4;
SET character_set_results = utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;

USE parc_informatique;

-- Désactiver temporairement les contraintes
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- 1. CATÉGORIES D'ÉQUIPEMENT
-- =====================================================================
DELETE FROM equipment_categories;
INSERT INTO equipment_categories (id, code, name, description, default_amortization_years) VALUES
(1,  'PC',      'Unité centrale / PC',  'Ordinateurs de bureau et portables', 5),
(2,  'SCREEN',  'Écran',                'Moniteurs et écrans',                5),
(3,  'PRINT',   'Imprimante',           'Imprimantes et multifonctions',      5),
(4,  'SCAN',    'Scanner',              'Scanners',                           5),
(5,  'UPS',     'Onduleur',             'Onduleurs et batteries',             5),
(6,  'NETWORK', 'Équipement réseau',    'Switch, routeur, firewall',          8),
(7,  'SERVER',  'Serveur',              'Serveurs physiques',                 5),
(8,  'PHONE',   'Téléphone IP',         'Téléphones IP',                      5),
(9,  'VIDEO',   'Vidéoprojecteur',      'Vidéoprojecteurs',                   5),
(10, 'OTHER',   'Autre',                'Autres équipements',                 5);

-- =====================================================================
-- 2. STATUTS D'ÉQUIPEMENT
-- =====================================================================
DELETE FROM equipment_statuses;
INSERT INTO equipment_statuses (id, code, name, color, is_available, is_final, sort_order) VALUES
(1, 'in_service',    'En service',         '#198754', 1, 0, 1),
(2, 'in_stock',      'En stock',           '#0dcaf0', 1, 0, 2),
(3, 'maintenance',   'En maintenance',     '#ffc107', 1, 0, 3),
(4, 'out_of_service','Hors service',       '#dc3545', 0, 0, 4),
(5, 'obsolete',      'Obsolète',           '#6c757d', 0, 0, 5),
(6, 'to_reform',     'À réformer',         '#fd7e14', 0, 0, 6),
(7, 'reformed',      'Réformé',            '#495057', 0, 1, 7),
(8, 'lost',          'Perdu / Volé',       '#842029', 0, 1, 8),
(9, 'disposed',      'Sorti d''inventaire','#212529', 0, 1, 9);

-- =====================================================================
-- 3. MARQUES
-- =====================================================================
DELETE FROM brands;
INSERT INTO brands (id, name) VALUES
(1, 'HP'), (2, 'Dell'), (3, 'Lenovo'), (4, 'Asus'), (5, 'Acer'),
(6, 'Epson'), (7, 'Canon'), (8, 'APC'), (9, 'Cisco'), (10, 'TP-Link');

-- =====================================================================
-- 4. SERVICES
-- =====================================================================
DELETE FROM services;
INSERT INTO services (id, site_id, code, name) VALUES
(1, 1, 'DIR',  'Direction Générale'),
(2, 1, 'DF',   'Direction Financière'),
(3, 1, 'RH',   'Ressources Humaines'),
(4, 1, 'INFO', 'Service Informatique'),
(5, 1, 'COMP', 'Comptabilité');

-- =====================================================================
-- 5. SITES
-- =====================================================================
DELETE FROM sites;
INSERT INTO sites (id, code, name, city) VALUES
(1, 'SIEGE', 'Siège principal', 'Alger');

-- =====================================================================
-- 6. RÔLES
-- =====================================================================
DELETE FROM roles;
INSERT INTO roles (id, name, slug, description, is_system) VALUES
(1, 'Administrateur', 'admin',      'Accès total à toutes les fonctionnalités', 1),
(2, 'Gestionnaire',   'manager',    'Gestion du parc, affectations, maintenance, réformes', 1),
(3, 'Commission',     'commission', 'Examen et validation des propositions de réforme', 1),
(4, 'Consultation',   'viewer',     'Lecture seule', 1);

-- =====================================================================
-- 7. PERMISSIONS
-- =====================================================================
DELETE FROM permissions;
INSERT INTO permissions (name, module, description) VALUES
('equipment.view',     'equipment',   'Voir les équipements'),
('equipment.create',   'equipment',   'Créer un équipement'),
('equipment.edit',     'equipment',   'Modifier un équipement'),
('equipment.delete',   'equipment',   'Supprimer un équipement'),
('equipment.export',   'equipment',   'Exporter les équipements'),
('equipment.import',   'equipment',   'Importer des équipements'),
('assignment.view',    'assignment',  'Voir les affectations'),
('assignment.create',  'assignment',  'Créer une affectation'),
('assignment.edit',    'assignment',  'Modifier une affectation'),
('maintenance.view',   'maintenance', 'Voir les maintenances'),
('maintenance.create', 'maintenance', 'Créer une maintenance'),
('maintenance.edit',   'maintenance', 'Modifier une maintenance'),
('maintenance.delete', 'maintenance', 'Supprimer une maintenance'),
('reform.view',        'reform',      'Voir les réformes'),
('reform.create',      'reform',      'Créer une réforme'),
('reform.edit',        'reform',      'Modifier une réforme'),
('reform.approve',     'reform',      'Approuver une réforme'),
('reform.reject',      'reform',      'Refuser une réforme'),
('reform.generate_pv', 'reform',      'Générer le PV de réforme'),
('document.view',      'document',    'Voir les documents'),
('document.download',  'document',    'Télécharger les documents'),
('report.view',        'report',      'Voir les rapports'),
('report.export',      'report',      'Exporter les rapports'),
('users.view',         'users',       'Voir les utilisateurs'),
('users.create',       'users',       'Créer un utilisateur'),
('users.edit',         'users',       'Modifier un utilisateur'),
('users.delete',       'users',       'Supprimer un utilisateur'),
('settings.view',      'settings',    'Voir les paramètres'),
('settings.manage',    'settings',    'Gérer les paramètres'),
('audit.view',         'audit',       'Consulter le journal d''audit');

-- =====================================================================
-- 8. LIAISONS RÔLES ↔ PERMISSIONS
-- =====================================================================
DELETE FROM role_permissions;

-- Admin : toutes les permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Gestionnaire : tout sauf admin users/settings/audit + approbation
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions
WHERE name NOT IN ('users.view','users.create','users.edit','users.delete',
                   'settings.view','settings.manage','audit.view',
                   'reform.approve','reform.reject');

-- Commission : consultation + approbation/refus
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions
WHERE name IN ('equipment.view','reform.view','reform.approve','reform.reject',
               'document.view','document.download','report.view');

-- Consultation : lecture seule
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions
WHERE name IN ('equipment.view','assignment.view','maintenance.view','reform.view',
               'document.view','report.view');

-- =====================================================================
-- 9. UTILISATEUR ADMIN
-- =====================================================================
-- ⚠️ On ne supprime PAS l'admin, on le met à jour
UPDATE users SET
    first_name = 'Administrateur',
    last_name  = 'Système',
    email      = 'admin@parc.local'
WHERE username = 'admin';

-- S'assurer que le lien user_roles existe
INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (1, 1);

-- =====================================================================
-- 10. PARAMÈTRES
-- =====================================================================
DELETE FROM settings;
INSERT INTO settings (`key`, `value`, `group`, type, description) VALUES
('app.name',                'Gestion du Parc Informatique', 'general', 'string', 'Nom de l''application'),
('app.organization',        'Votre Organisation',           'general', 'string', 'Nom de l''organisation'),
('app.city',                'Alger',                        'general', 'string', 'Ville'),
('app.logo',                '',                             'general', 'string', 'Chemin du logo'),
('equipment.code_prefix',   'INF',                          'equipment', 'string', 'Préfixe des numéros d''inventaire'),
('equipment.code_year',     '1',                            'equipment', 'bool',   'Inclure l''année dans le code'),
('reform.code_prefix',      'REF',                          'reform',  'string', 'Préfixe des références de réforme'),
('alert.no_assignment_days','30',                           'alerts',  'int',    'Jours avant alerte sans affectation'),
('alert.obsolete_days',     '60',                           'alerts',  'int',    'Jours avant alerte obsolescence');

-- =====================================================================
-- Réactiver les contraintes
-- =====================================================================
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- VÉRIFICATION
-- =====================================================================
SELECT '=== VÉRIFICATION FINALE ===' AS info;

SELECT 'Catégories' AS t, COUNT(*) AS nb FROM equipment_categories
UNION ALL SELECT 'Statuts', COUNT(*) FROM equipment_statuses
UNION ALL SELECT 'Marques', COUNT(*) FROM brands
UNION ALL SELECT 'Services', COUNT(*) FROM services
UNION ALL SELECT 'Sites', COUNT(*) FROM sites
UNION ALL SELECT 'Rôles', COUNT(*) FROM roles
UNION ALL SELECT 'Permissions', COUNT(*) FROM permissions
UNION ALL SELECT 'Utilisateurs', COUNT(*) FROM users
UNION ALL SELECT 'Paramètres', COUNT(*) FROM settings;

-- Afficher les valeurs pour vérification
SELECT '--- CATÉGORIES ---' AS info;
SELECT id, code, name FROM equipment_categories ORDER BY id;

SELECT '--- STATUTS ---' AS info;
SELECT id, code, name FROM equipment_statuses ORDER BY sort_order;

SELECT '--- SERVICES ---' AS info;
SELECT id, code, name FROM services ORDER BY id;

SELECT '--- SITES ---' AS info;
SELECT id, code, name FROM sites ORDER BY id;