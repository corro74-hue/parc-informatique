# 🖥️ Parc Info — Gestion de Parc Informatique

Application web de gestion de parc informatique développée en **PHP pur** (sans framework), suivant une architecture **MVC** propre avec Repository Pattern.

> Inventaire complet des équipements, gestion des statuts en temps réel, exports PDF/Excel, QR codes, alertes garantie et bien plus.

---

## 📸 Aperçu

*Ajoutez ici une capture d'écran de la liste des équipements ou du tableau de bord.*

---

## ✨ Fonctionnalités

### 📦 Module Équipements (Inventaire)
- ✅ CRUD complet (Créer, Lire, Modifier, Supprimer)
- ✅ Liste paginée avec tri multi-colonnes
- ✅ Filtres simples (recherche, catégorie, statut, service, site, marque)
- ✅ **Filtres avancés** (plage de dates, plage de valeurs, sous garantie, sans N° de série)
- ✅ Changement de statut en **AJAX** sans rechargement
- ✅ **Barre de compteurs** par statut (badges colorés cliquables)
- ✅ **Recherche globale** dans la navbar (avec dropdown AJAX)
- ✅ **Corbeille** avec restauration et suppression définitive
- ✅ **Duplication** d'équipement en un clic
- ✅ **QR Code** unique par équipement (étiquette imprimable)
- ✅ **Fiche PDF individuelle** (A4 portrait, avec QR code)
- ✅ **Export Excel/CSV** de la liste filtrée
- ✅ **Export PDF** de la liste complète

### 📊 Tableau de bord
- ✅ Bandeau de bienvenue personnalisé
- ✅ **Alertes garantie** : équipements dont la garantie expire bientôt
- ✅ **Alertes garantie** : garanties récemment expirées
- ✅ Cartes statistiques (équipements, maintenance, réformes, utilisateurs)

### 🔐 Authentification & Sécurité
- ✅ Connexion sécurisée avec sessions
- ✅ **Protection CSRF** sur tous les formulaires
- ✅ Middleware d'authentification
- ✅ Rôles utilisateurs (admin, technicien)

### 🔄 Roadmap (Modules prévus)
- 🔄 **Actions groupées** (sélection multiple + traitement en masse)
- 🔄 **Import CSV/Excel** d'équipements
- 🔄 **Historique des modifications** (traçabilité complète)
- 🔄 **Pièces jointes** (photos, factures, garanties)
- 🔄 **Mode sombre**
- 🔄 **Module Maintenance** complet
- 🔄 **Module Réformes** complet
- 🔄 **Module Affectations** (équipement ↔ employé)
- 🔄 **Module Documents** (GED)
- 🔄 **Module Rapports** (statistiques avancées)
- 🔄 **Module Utilisateurs** (CRUD + permissions)
- 🔄 **Module Campagnes d'inventaire**

---

## 🛠️ Stack Technique

| Technologie | Usage |
|-------------|-------|
| **PHP 8.1+** | Langage principal (architecture MVC maison) |
| **MySQL 5.7+** | Base de données relationnelle |
| **PDO** | Accès base de données sécurisé (requêtes préparées) |
| **Composer** | Gestionnaire de dépendances PHP |
| **Bootstrap 5.3** | Framework CSS responsive (via CDN) |
| **Bootstrap Icons** | Icônes vectorielles |
| **JavaScript (Vanilla)** | Interactions AJAX (statuts, recherche) |
| **Dompdf** | Génération de PDF |
| **PhpSpreadsheet** | Génération de fichiers Excel |
| **Endroid QR Code** | Génération des QR codes |
| **Monolog** | Logging applicatif |

**Architecture :**
- Pattern **MVC** (Model-View-Controller)
- **Repository Pattern** pour l'accès aux données (interfaces + implémentations MySQL)
- **Service Layer** pour la logique métier
- **Middleware** pour l'authentification et le CSRF
- **DTO** et **Enums** pour la robustesse des données

---

## 📁 Structure du Projet

```
parc-informatique/
│
├── app/                              # Code applicatif (PSR-4)
│   ├── Controllers/                  # Contrôleurs (logique des requêtes)
│   │   ├── Api/                      # (à venir) Contrôleurs API
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── EquipmentController.php
│   │
│   ├── Core/                         # Cœur du mini-framework
│   │   ├── Controller.php
│   │   ├── Csrf.php                  # Protection CSRF
│   │   ├── Database.php              # Connexion PDO Singleton
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── Router.php
│   │
│   ├── DTO/                          # Data Transfer Objects (à venir)
│   ├── Enums/                        # Énumérations PHP 8.1+ (à venir)
│   │
│   ├── Exceptions/                   # Exceptions personnalisées
│   │   ├── NotFoundException.php
│   │   └── ValidationException.php
│   │
│   ├── Helpers/                      # Fonctions helper globales
│   │   └── functions.php             # url(), e(), csrf_field(), flash()...
│   │
│   ├── Middleware/                   # Filtres de requêtes
│   │   ├── AuthMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   └── GuestMiddleware.php
│   │
│   ├── Models/                       # Modèles métier
│   │   ├── Equipment.php
│   │   └── User.php
│   │
│   ├── Policies/                     # Autorisations (à venir)
│   │
│   ├── Repositories/                 # Couche d'accès aux données
│   │   ├── Contracts/                # Interfaces
│   │   │   ├── EquipmentRepositoryInterface.php
│   │   │   └── UserRepositoryInterface.php
│   │   └── MySql/                    # Implémentations MySQL
│   │       ├── EquipmentRepository.php
│   │       └── UserRepository.php
│   │
│   ├── Services/                     # Logique métier
│   │   ├── Audit/                    # (à venir) Journal d'audit
│   │   ├── Auth/AuthService.php
│   │   ├── Document/                 # (à venir) GED
│   │   ├── Equipment/EquipmentService.php
│   │   ├── Export/                   # (à venir) Exports Excel/PDF
│   │   ├── Import/                   # (à venir) Imports CSV
│   │   ├── Maintenance/              # (à venir) Maintenance
│   │   ├── Notification/             # (à venir) Notifications
│   │   ├── QrCode/QrCodeService.php
│   │   └── Reform/                   # (à venir) Réformes
│   │
│   └── Validators/                   # Validateurs (à venir)
│
├── config/                           # Configuration
│   ├── app.php
│   └── database.php
│
├── database/                         # Scripts SQL
│   ├── backups/                      # Sauvegardes SQL (à venir)
│   ├── migrations/                   # Migrations (à venir)
│   ├── seeds/                        # Seeders (à venir)
│   ├── fix_encoding.sql
│   ├── reset_reference_data.sql
│   ├── schema.sql                    # Schéma complet
│   └── seed.sql                      # Données initiales
│
├── public/                           # Document root (accessible web)
│   ├── assets/
│   │   ├── css/                      # (à venir) Styles personnalisés
│   │   ├── images/                   # (à venir) Images
│   │   └── js/
│   │       ├── equipment-status.js   # AJAX changement de statut
│   │       └── global-search.js      # AJAX recherche globale
│   ├── documents/                    # (à venir) PDFs générés
│   ├── uploads/                      # (à venir) Uploads utilisateurs
│   ├── .htaccess                     # Réécriture URL
│   └── index.php                     # Point d'entrée unique
│
├── resources/                        # Ressources non-web
│   ├── templates/                    # Templates d'export
│   │   ├── excel/                    # (à venir) Templates XLSX
│   │   └── pdf/                      # (à venir) Templates PDF
│   └── views/                        # Vues PHP
│       ├── assignment/               # (à venir)
│       ├── auth/
│       │   └── login.php
│       ├── campaigns/                # (à venir)
│       ├── dashboard/
│       │   └── index.php
│       ├── documents/                # (à venir)
│       ├── equipment/
│       │   ├── partials/
│       │   │   ├── _filters.php
│       │   │   └── _form.php
│       │   ├── create.php
│       │   ├── edit.php
│       │   ├── index.php
│       │   ├── qrcode.php
│       │   ├── show.php
│       │   └── trash.php
│       ├── errors/
│       │   ├── 404.php
│       │   └── 500.php
│       ├── layouts/
│       │   ├── app.php               # Layout principal
│       │   └── auth.php              # Layout page de login
│       ├── maintenance/              # (à venir)
│       ├── partials/                 # (à venir) Partials globaux
│       ├── reform/                   # (à venir)
│       ├── reports/                  # (à venir)
│       └── users/                    # (à venir)
│
├── routes/                           # Définition des routes
│   ├── api.php
│   └── web.php
│
├── scripts/                          # Scripts CLI utilitaires
│   ├── reset_admin.php
│   ├── test_charset.php
│   └── test_equipment.php
│
├── storage/                          # Données générées
│   ├── backups/                      # Sauvegardes
│   ├── cache/                        # Cache applicatif
│   ├── documents/                    # Documents générés
│   ├── exports/                      # Exports Excel/CSV
│   ├── imports/                      # Fichiers à importer
│   ├── logs/                         # Logs Monolog
│   │   ├── app-2026-09-21.log
│   │   ├── app-2026-09-23.log
│   │   └── php-errors.log
│   ├── qrcodes/                      # QR codes générés
│   └── sessions/                     # Sessions PHP
│
├── tests/                            # Tests (PHPUnit)
│   ├── Feature/                      # (à venir) Tests fonctionnels
│   └── Unit/                         # (à venir) Tests unitaires
│
├── .env                              # Variables d'environnement (ignoré par Git)
├── .gitignore                        # Fichiers exclus de Git
├── composer.json                     # Dépendances PHP
├── composer.lock                     # Versions verrouillées
└── README.md                         # Ce fichier
```

---

## 🚀 Installation

### Prérequis

- **PHP 8.1** ou supérieur ([php.net](https://www.php.net/downloads))
- **MySQL 5.7** ou supérieur ([mysql.com](https://dev.mysql.com/downloads/))
- **Composer** ([getcomposer.org](https://getcomposer.org/download/))
- **XAMPP** (recommandé sur Windows) ou **WAMP** / **MAMP**

### Étape 1 : Cloner le dépôt

```bash
git clone https://github.com/corro74-hue/parc-informatique.git
cd parc-informatique
```

### Étape 2 : Installer les dépendances

```bash
composer install
```

### Étape 3 : Configurer l'environnement

1. Copie le fichier `.env.example` en `.env` :
   ```bash
   cp .env.example .env
   ```
   *(Sur Windows : `copy .env.example .env`)*

2. Ouvre `.env` et configure tes paramètres :
   ```env
   APP_NAME="Gestion Parc Informatique"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost/parc-informatique/public
   APP_TIMEZONE=Africa/Algiers

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3307
   DB_DATABASE=parc_informatique
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### Étape 4 : Créer la base de données

1. Ouvre **phpMyAdmin** : [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Crée une base de données nommée **`parc_informatique`**
3. Importe le schéma :
   - Onglet **Importer**
   - Sélectionne `database/schema.sql`
   - Clique sur **Exécuter**
4. Importe les données initiales :
   - Importe `database/seed.sql` (statuts, catégories, marques)

### Étape 5 : Lancer l'application

**Option 1 : Avec XAMPP/WAMP**

Place le projet dans `htdocs/parc-informatique` puis ouvre :
```
http://localhost/parc-informatique/public/
```

**Option 2 : Avec le serveur PHP intégré**

```bash
php -S localhost:8000 -t public
```

Puis ouvre : `http://localhost:8000`

### Étape 6 : Se connecter

Utilise les identifiants créés par le script `scripts/reset_admin.php` :

```
Nom d'utilisateur : admin
Mot de passe      : admin
```

⚠️ **Change ce mot de passe après la première connexion !**

---

## 📊 Modèle de données

Le projet utilise **15+ tables MySQL** :

| Table | Description |
|-------|-------------|
| `equipment` | Équipements du parc |
| `equipment_categories` | Catégories (PC, imprimante, etc.) |
| `equipment_statuses` | Statuts (En service, En panne, etc.) |
| `brands` | Marques (HP, Dell, etc.) |
| `services` | Services de l'entreprise |
| `sites` | Sites physiques |
| `locations` | Localisations précises |
| `employees` | Employés responsables |
| `assignments` | Affectations d'équipements |
| `maintenance` | Historique de maintenance |
| `reformations` | Équipements réformés |
| `inventory_campaigns` | Campagnes d'inventaire |
| `audit_logs` | Journal d'audit |
| `users` | Utilisateurs de l'application |
| `roles` / `permissions` | Gestion des rôles |

---

## 🧪 Tests

Le projet inclut quelques scripts de test dans `scripts/` :

```bash
# Tester la connexion à la base
php scripts/test_charset.php

# Tester le module équipement
php scripts/test_equipment.php

# Réinitialiser l'admin
php scripts/reset_admin.php
```

---

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Fork le projet
2. Crée une branche (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit tes changements (`git commit -m 'Ajout de la fonctionnalité X'`)
4. Push sur la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Ouvre une Pull Request

---

## 📝 Licence

Ce projet est sous licence **MIT**. Voir le fichier `LICENSE` pour plus de détails.

---

## 👤 Auteur

**corro74-hue**
- GitHub : [@corro74-hue](https://github.com/corro74-hue)

---

## 🙏 Remerciements

- Bootstrap pour le framework CSS
- Bootstrap Icons pour les icônes
- Dompdf, PhpSpreadsheet, Endroid QR Code pour les libs
- La communauté PHP pour l'inspiration

---

## 📅 Changelog

### Version 0.3.0 — En cours
- ✅ Module Équipements complet (CRUD, filtres, exports)
- ✅ QR Codes et étiquettes imprimables
- ✅ Changement de statut AJAX
- ✅ Barre de compteurs par statut
- ✅ Filtres avancés (dates, valeurs, garantie)
- ✅ Alertes garantie sur le dashboard
- ✅ Recherche globale dans la navbar
- 🔄 En cours : Actions groupées, import CSV, historique

### Version 0.2.0
- ✅ Tableau de bord avec statistiques
- ✅ Corbeille avec restauration
- ✅ Duplication d'équipements
- ✅ Export PDF/Excel

### Version 0.1.0
- ✅ Authentification
- ✅ Layout principal (sidebar, topbar)
- ✅ Structure MVC de base

---

<div align="center">

**Fait avec ❤️ en PHP**

⭐ Si ce projet t'a aidé, n'hésite pas à lui donner une étoile !

</div>