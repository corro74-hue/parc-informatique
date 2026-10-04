# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

---

## [0.5.0] - 2026-10-04

### Added
- Module **Maintenance** complet (CRUD + workflow équipement automatique + clôture)
- Module **Employés** complet (CRUD + filtres + utilisé par Affectations)
- Module **Sauvegardes BDD** (création manuelle, liste, téléchargement, suppression, rétention auto)
- Module **Santé système** (version PHP, extensions, espace disque, permissions)
- **Pièces jointes** sur les équipements (upload/download)
- **Actions groupées** (bulk update, bulk delete, bulk export)
- **Import CSV** avec prévisualisation
- **Historique** des modifications (audit trail)
- **2FA** (TOTP + backup codes)
- **Rate limiting** sur les routes sensibles
- **Password policy** configurable
- **Verrouillage de compte** après échecs répétés
- Statut **"Affecté"** (ID 10) pour les équipements

### Changed
- Migration vers **WampServer** (port 80, MySQL 8.4.7, PHP 8.3.28)
- Chemin `mysqldump` corrigé : `C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe`
- Table `backup_logs` complétée avec les colonnes manquantes
- Catégories et services corrigés (UTF-8)

### Fixed
- Bug de casse dans `BASE_PATH` (`parc-informatique` vs `Parc-Informatique`)
- Bug de casse dans `Request.php` (extraction URI)
- Conflit recherche globale / autocomplete équipement
- Encodage UTF-8 des données de référence

---

## [0.4.0] - 2026-09-29

### Added
- Module **Affectations** complet (CRUD + retour + vue show/edit)
- Module **Employés** (CRUD + vues)
- Autocomplete équipement (`#equipment_search` + API `/api/equipment/search`)
- Workflow retour d'affectation → équipement remis "En stock"

### Fixed
- Conflit recherche globale/autocomplete (garde-fou dans `global-search.js`)
- Restauration complète après crash MySQL

---

## [0.3.0] - 2026-09-23

### Added
- Module **Équipements** complet (CRUD, filtres simples + avancés)
- **QR Codes** et étiquettes imprimables
- Changement de statut en **AJAX**
- **Barre de compteurs** par statut
- Filtres avancés (dates, valeurs, garantie)
- **Alertes garantie** sur le dashboard
- **Recherche globale** dans la navbar
- **Export PDF/Excel**
- **Import CSV**

---

## [0.2.0] - 2026-09-21

### Added
- **Tableau de bord** avec statistiques
- **Corbeille** avec restauration
- **Duplication** d'équipements

---

## [0.1.0] - 2026-09-20

### Added
- **Authentification** (login/logout)
- **Layout principal** (sidebar, topbar)
- **Structure MVC** de base (Router, Database, Request, Response)

---

## [Unreleased]

### En développement
- 📋 Module **Campagnes d'inventaire**
- 📊 Module **Rapports** (statistiques avancées + graphiques Chart.js)
- 📄 Module **Documents** (GED)
- ⚙️ Module **Paramètres** (SMTP, notifications, sécurité)

---

[0.5.0]: https://github.com/corro74-hue/parc-informatique/compare/v0.4.0...v0.5.0
[0.4.0]: https://github.com/corro74-hue/parc-informatique/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/corro74-hue/parc-informatique/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/corro74-hue/parc-informatique/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/corro74-hue/parc-informatique/releases/tag/v0.1.0