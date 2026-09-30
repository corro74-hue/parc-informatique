# 🎯 CONTEXTE DU PROJET — PARC INFO

**Dernière mise à jour** : 29 septembre 2026
**État** : En développement actif

---

## 📌 RÉSUMÉ POUR REPRISE DE CONVERSATION

Copie ce paragraphe + le contenu de `structure.txt` au début d'une nouvelle conversation avec l'IA.

---

## 🖥️ STACK TECHNIQUE

- **OS** : Windows 10.0.26200
- **Serveur** : WampServer 3.4.0 (64 bit)
  - Apache 2.4.65 (port 80)
  - PHP 8.3.28
  - MySQL 8.4.7 (port 3306)
  - MariaDB 11.4.9 (port 3307)
- **Chemin projet** : `C:\wamp64\www\parc-informatique\`
- **URL locale** : `http://localhost/parc-informatique/public/`
- **Base de données** : `parc_informatique` (39 tables)
- **Gestionnaire** : Composer (vendor/ en place)

---

## 🏗️ ARCHITECTURE

**Pattern MVC custom** (sans framework) :
- `app/Controllers/` — Contrôleurs
- `app/Core/` — Cœur du mini-framework (Router, Database, Request, Response, Csrf)
- `app/Models/` — Modèles métier
- `app/Repositories/Contracts/` + `app/Repositories/MySql/` — Pattern Repository
- `app/Services/` — Logique métier
- `app/Middleware/` — Auth, Csrf, Guest, Permission, RateLimit, Security, Maintenance, PasswordPolicy
- `app/Exceptions/` — NotFoundException, ValidationException
- `app/Helpers/functions.php` — url(), e(), csrf_field(), flash(), old(), config(), can(), has_role()

**Conventions** :
- `declare(strict_types=1)` partout
- Classes `final`
- Typage strict (PHP 8.2+)
- Session flash via `flash('success', '...')` et `flash('error', '...')`
- Réponses via `Response::redirect()`, `Response::json()`, `$this->view()`

---

## ✅ MODULES FONCTIONNELS (100%)

### 1. 🔐 Authentification
- Login / logout
- 2FA (TOTP + backup codes)
- Rate limiting
- Password policy
- Verrouillage de compte après échecs

### 2. 📊 Dashboard
- Bienvenue personnalisée
- Alertes garanties (expirées + expirant bientôt)
- Cartes stats (Équipements, Maintenance, Réforme, Utilisateurs)
- Roadmap de construction

### 3. 📦 Équipements (Inventaire)
- CRUD complet
- Catégories, marques, statuts, services, sites, localisations
- Filtres simples + avancés
- Recherche globale AJAX
- Export Excel/CSV
- Export PDF (fiche individuelle + liste)
- QR Code par équipement
- Import CSV
- Corbeille (soft delete) + restauration
- Duplication
- Pièces jointes (upload/download)
- Changement de statut AJAX
- Actions groupées (bulk)
- Historique (audit)
- **Statut "Affecté" (ID 10)** ajouté récemment

### 4. 👥 Utilisateurs (Administration)
- CRUD complet
- Rôles et permissions (RBAC)
- Reset password
- Toggle actif/inactif
- Profil personnel
- Sécurité (2FA setup, backup codes)

### 5. 🎭 Rôles et permissions
- CRUD complet
- Attribution de permissions
- Rôle système "admin" (non supprimable)

### 6. 📋 Journal d'audit
- Liste des actions
- Traçabilité (user, action, entity, IP, user-agent)
- Détails avant/après

### 7. 💾 Sauvegardes BDD (Administration)
- Création manuelle (mysqldump)
- Liste des backups
- Téléchargement
- Suppression
- Nettoyage automatique (rétention 7j + 4sem + 12mois)
- **Chemin mysqldump corrigé** : `C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe`
- Table `backup_logs` : `id, filename, filepath, filesize, size_bytes, type, status, error_message, triggered_by, user_id, duration_seconds, created_by, created_at`

### 8. 🖥️ Santé système
- Version PHP, extensions
- Espace disque
- Permissions dossiers
- Mode maintenance (activable)
- Page publique `/maintenance` (503)

### 9. 🤝 Affectations
- CRUD complet
- Création depuis équipement ou employé
- **Autocomplete équipement** (`#equipment_search` + API `/api/equipment/search`)
- Retour d'affectation → équipement remis "En stock"
- Vue index, create, show, edit
- Workflow complet testé ✅
- **Conflit recherche globale/autocomplete corrigé** (garde-fou dans `global-search.js`)

### 10. 👤 Employés (NOUVEAU - 29/09/2026)
- CRUD complet
- Liste avec filtres (recherche, service, statut actif/inactif)
- Détail avec infos personnelles + contact
- **3 employés en base** : AMINE BOUKACHOURA (EMP-001), AMINE AZOUDJ (EMP-002), AMEL BOUAZIZ (EMP-003)
- Index UNIQUE sur `matricule`
- Vue index, create, show, edit
- **Utilisé par le module Affectations** (dropdown employés)

---

## ⏳ MODULES À DÉVELOPPER

### 1. 🔧 Maintenance
- Table `maintenance` existe
- Tables liées : `maintenance_items`, `maintenance_contracts`
- Dossier `resources/views/maintenance/` vide
- Dossier `app/Services/Maintenance/` vide
- **À créer entièrement**

### 2. ♻️ Réformes
- Tables : `reformations`, `reformation_items`, `reformation_decisions`, `reformation_workflow_logs`
- Dossiers vides
- **À créer entièrement**

### 3. 📄 Documents (GED)
- Tables : `documents`, `document_versions`
- Dossiers vides
- **À créer entièrement**

### 4. 📊 Rapports
- Aucune table dédiée (utilise les autres)
- Dossiers vides
- **À créer entièrement**

### 5. 📋 Campagnes d'inventaire
- Tables : `inventory_campaigns`, `inventory_campaign_items`, `inventory_movements`
- Dossiers vides
- **À créer entièrement**

### 6. ⚙️ Paramètres
- Lien dans la sidebar mais pas de routes
- **À créer**

---

## ⚠️ POINTS D'ATTENTION

### Base de données
- Le statut "Affecté" (ID=10) a été recréé manuellement après restauration
- Les catégories et services ont été corrigés (UTF-8)
- La table `backup_logs` a été complétée avec les colonnes manquantes
- Un ancien dossier `data_CORROMPU_29sept` existe dans `C:\xampp_gpa\mysql\` (peut être supprimé plus tard)

### Structure des tables importantes
```sql
-- employees
id, service_id, first_name, last_name, matricule (UNIQUE), email, phone, 
function_title, is_active, created_at, updated_at

-- assignments
id, equipment_id, site_id, service_id, location_id, employee_id, 
start_date, end_date, reason, document_path, created_by, created_at, updated_at

-- backup_logs
id, filename, filepath, filesize, size_bytes, type, status, 
error_message, triggered_by, user_id, duration_seconds, created_by, created_at