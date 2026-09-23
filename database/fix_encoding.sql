-- =====================================================================
-- CORRECTION GLOBALE DE L'ENCODAGE
-- =====================================================================
-- Ce script corrige les textes accentués mal encodés dans les tables
-- de référence (catégories, services, sites, statuts, etc.)
--
-- Il est IDEMPOTENT : peut être exécuté plusieurs fois sans risque.
-- =====================================================================

USE parc_informatique;

-- =====================================================================
-- 1. TABLE `equipment_categories`
-- =====================================================================
UPDATE equipment_categories SET name = 'Unité centrale / PC' WHERE id = 1;
UPDATE equipment_categories SET name = 'Écran'               WHERE id = 2;
UPDATE equipment_categories SET name = 'Imprimante'          WHERE id = 3;
UPDATE equipment_categories SET name = 'Scanner'             WHERE id = 4;
UPDATE equipment_categories SET name = 'Onduleur'            WHERE id = 5;
UPDATE equipment_categories SET name = 'Équipement réseau'   WHERE id = 6;
UPDATE equipment_categories SET name = 'Serveur'             WHERE id = 7;
UPDATE equipment_categories SET name = 'Téléphone IP'        WHERE id = 8;
UPDATE equipment_categories SET name = 'Vidéoprojecteur'     WHERE id = 9;
UPDATE equipment_categories SET name = 'Autre'               WHERE id = 10;

-- =====================================================================
-- 2. TABLE `equipment_statuses`
-- =====================================================================
UPDATE equipment_statuses SET name = 'En service'             WHERE id = 1;
UPDATE equipment_statuses SET name = 'En stock'               WHERE id = 2;
UPDATE equipment_statuses SET name = 'En maintenance'         WHERE id = 3;
UPDATE equipment_statuses SET name = 'Hors service'           WHERE id = 4;
UPDATE equipment_statuses SET name = 'Obsolète'               WHERE id = 5;
UPDATE equipment_statuses SET name = 'À réformer'             WHERE id = 6;
UPDATE equipment_statuses SET name = 'Réformé'                WHERE id = 7;
UPDATE equipment_statuses SET name = 'Perdu / Volé'           WHERE id = 8;
UPDATE equipment_statuses SET name = 'Sorti d''inventaire'    WHERE id = 9;

-- =====================================================================
-- 3. TABLE `services`
-- =====================================================================
UPDATE services SET name = 'Direction Générale'    WHERE id = 1;
UPDATE services SET name = 'Direction Financière'  WHERE id = 2;
UPDATE services SET name = 'Ressources Humaines'   WHERE id = 3;
UPDATE services SET name = 'Service Informatique'  WHERE id = 4;
UPDATE services SET name = 'Comptabilité'          WHERE id = 5;

-- =====================================================================
-- 4. TABLE `sites`
-- =====================================================================
UPDATE sites SET name = 'Siège principal'  WHERE id = 1;
UPDATE sites SET city = 'Alger'            WHERE id = 1;

-- =====================================================================
-- 5. TABLE `brands`
-- =====================================================================
-- (Aucune marque n'a d'accent, mais on les remet par sécurité)
UPDATE brands SET name = 'HP'     WHERE id = 1;
UPDATE brands SET name = 'Dell'   WHERE id = 2;
UPDATE brands SET name = 'Lenovo' WHERE id = 3;
UPDATE brands SET name = 'Asus'   WHERE id = 4;
UPDATE brands SET name = 'Acer'   WHERE id = 5;
UPDATE brands SET name = 'Epson'  WHERE id = 6;
UPDATE brands SET name = 'Canon'  WHERE id = 7;
UPDATE brands SET name = 'APC'    WHERE id = 8;
UPDATE brands SET name = 'Cisco'  WHERE id = 9;
UPDATE brands SET name = 'TP-Link' WHERE id = 10;

-- =====================================================================
-- 6. TABLE `roles`
-- =====================================================================
UPDATE roles SET name = 'Administrateur' WHERE id = 1;
UPDATE roles SET name = 'Gestionnaire'   WHERE id = 2;
UPDATE roles SET name = 'Commission'     WHERE id = 3;
UPDATE roles SET name = 'Consultation'   WHERE id = 4;

UPDATE roles SET description = 'Accès total à toutes les fonctionnalités'          WHERE id = 1;
UPDATE roles SET description = 'Gestion du parc, affectations, maintenance, réformes' WHERE id = 2;
UPDATE roles SET description = 'Examen et validation des propositions de réforme'  WHERE id = 3;
UPDATE roles SET description = 'Lecture seule'                                     WHERE id = 4;

-- =====================================================================
-- 7. TABLE `permissions`
-- =====================================================================
-- Mettre à jour les descriptions avec accents
UPDATE permissions SET description = 'Voir les équipements'          WHERE name = 'equipment.view';
UPDATE permissions SET description = 'Créer un équipement'           WHERE name = 'equipment.create';
UPDATE permissions SET description = 'Modifier un équipement'        WHERE name = 'equipment.edit';
UPDATE permissions SET description = 'Supprimer un équipement'       WHERE name = 'equipment.delete';
UPDATE permissions SET description = 'Exporter les équipements'      WHERE name = 'equipment.export';
UPDATE permissions SET description = 'Importer des équipements'      WHERE name = 'equipment.import';

UPDATE permissions SET description = 'Voir les affectations'         WHERE name = 'assignment.view';
UPDATE permissions SET description = 'Créer une affectation'         WHERE name = 'assignment.create';
UPDATE permissions SET description = 'Modifier une affectation'      WHERE name = 'assignment.edit';

UPDATE permissions SET description = 'Voir les maintenances'         WHERE name = 'maintenance.view';
UPDATE permissions SET description = 'Créer une maintenance'         WHERE name = 'maintenance.create';
UPDATE permissions SET description = 'Modifier une maintenance'      WHERE name = 'maintenance.edit';
UPDATE permissions SET description = 'Supprimer une maintenance'     WHERE name = 'maintenance.delete';

UPDATE permissions SET description = 'Voir les réformes'             WHERE name = 'reform.view';
UPDATE permissions SET description = 'Créer une réforme'             WHERE name = 'reform.create';
UPDATE permissions SET description = 'Modifier une réforme'          WHERE name = 'reform.edit';
UPDATE permissions SET description = 'Approuver une réforme'         WHERE name = 'reform.approve';
UPDATE permissions SET description = 'Refuser une réforme'           WHERE name = 'reform.reject';
UPDATE permissions SET description = 'Générer le PV de réforme'      WHERE name = 'reform.generate_pv';

UPDATE permissions SET description = 'Voir les documents'            WHERE name = 'document.view';
UPDATE permissions SET description = 'Télécharger les documents'     WHERE name = 'document.download';

UPDATE permissions SET description = 'Voir les rapports'             WHERE name = 'report.view';
UPDATE permissions SET description = 'Exporter les rapports'         WHERE name = 'report.export';

UPDATE permissions SET description = 'Voir les utilisateurs'         WHERE name = 'users.view';
UPDATE permissions SET description = 'Créer un utilisateur'          WHERE name = 'users.create';
UPDATE permissions SET description = 'Modifier un utilisateur'       WHERE name = 'users.edit';
UPDATE permissions SET description = 'Supprimer un utilisateur'      WHERE name = 'users.delete';

UPDATE permissions SET description = 'Voir les paramètres'           WHERE name = 'settings.view';
UPDATE permissions SET description = 'Gérer les paramètres'          WHERE name = 'settings.manage';

UPDATE permissions SET description = 'Consulter le journal d''audit' WHERE name = 'audit.view';

-- =====================================================================
-- 8. TABLE `users`
-- =====================================================================
UPDATE users SET first_name = 'Administrateur' WHERE username = 'admin';
UPDATE users SET last_name  = 'Système'        WHERE username = 'admin';

-- =====================================================================
-- 9. TABLE `settings`
-- =====================================================================
UPDATE settings SET `value` = 'Gestion du Parc Informatique' WHERE `key` = 'app.name';
UPDATE settings SET `value` = 'Votre Organisation'           WHERE `key` = 'app.organization';
UPDATE settings SET `value` = 'Alger'                        WHERE `key` = 'app.city';

UPDATE settings SET description = 'Nom de l''application'                WHERE `key` = 'app.name';
UPDATE settings SET description = 'Nom de l''organisation'               WHERE `key` = 'app.organization';
UPDATE settings SET description = 'Ville'                                WHERE `key` = 'app.city';
UPDATE settings SET description = 'Chemin du logo'                       WHERE `key` = 'app.logo';
UPDATE settings SET description = 'Préfixe des numéros d''inventaire'    WHERE `key` = 'equipment.code_prefix';
UPDATE settings SET description = 'Inclure l''année dans le code'        WHERE `key` = 'equipment.code_year';
UPDATE settings SET description = 'Préfixe des références de réforme'    WHERE `key` = 'reform.code_prefix';
UPDATE settings SET description = 'Jours avant alerte sans affectation'  WHERE `key` = 'alert.no_assignment_days';
UPDATE settings SET description = 'Jours avant alerte obsolescence'      WHERE `key` = 'alert.obsolete_days';

-- =====================================================================
-- VÉRIFICATION FINALE
-- =====================================================================
SELECT '=== VÉRIFICATION ===' AS info;

SELECT '--- Catégories ---' AS table_name;
SELECT id, code, name FROM equipment_categories ORDER BY id;

SELECT '--- Statuts ---' AS table_name;
SELECT id, code, name FROM equipment_statuses ORDER BY sort_order;

SELECT '--- Services ---' AS table_name;
SELECT id, code, name FROM services ORDER BY id;

SELECT '--- Sites ---' AS table_name;
SELECT id, code, name, city FROM sites ORDER BY id;

SELECT '--- Utilisateurs ---' AS table_name;
SELECT id, username, first_name, last_name FROM users;

SELECT '--- Rôles ---' AS table_name;
SELECT id, slug, name FROM roles ORDER BY id;

SELECT '=== FIN ===' AS info;