<div align="center">

# 🖥️ Parc Info

### Gestion de Parc Informatique

*Inventaire complet des équipements, gestion des statuts en temps réel, exports PDF/Excel, QR codes, alertes garantie et bien plus.*

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-22c55e?style=for-the-badge)](LICENSE)
[![Version](https://img.shields.io/badge/Version-0.5.0-3b82f6?style=for-the-badge)](CHANGELOG.md)

</div>

---

Application web de gestion de parc informatique développée en **PHP pur** (sans framework), suivant une architecture **MVC** propre avec Repository Pattern.

---

## 📸 Aperçu

<div align="center">

![Tableau de bord](public/assets/images/dashboard.png)

*Le tableau de bord avec alertes garanties et statistiques en temps réel.*

</div>

## ✨ Fonctionnalités

### 📦 Module Équipements (Inventaire) — ✅ 100%
- ✅ CRUD complet (Créer, Lire, Modifier, Supprimer)
- ✅ Liste paginée avec tri multi-colonnes
- ✅ Filtres simples + avancés (dates, valeurs, garantie)
- ✅ Changement de statut en **AJAX** sans rechargement
- ✅ **Barre de compteurs** par statut (badges colorés cliquables)
- ✅ **Recherche globale** dans la navbar (dropdown AJAX)
- ✅ **Corbeille** avec restauration et suppression définitive
- ✅ **Duplication** d'équipement en un clic
- ✅ **QR Code** unique par équipement (étiquette imprimable)
- ✅ **Fiche PDF individuelle** (A4 portrait, avec QR code)
- ✅ **Export Excel/CSV** de la liste filtrée
- ✅ **Export PDF** de la liste complète
- ✅ **Import CSV** avec prévisualisation
- ✅ **Pièces jointes** (upload/download)
- ✅ **Actions groupées** (bulk update, bulk delete, bulk export)
- ✅ **Historique** des modifications (audit)

### 👥 Module Utilisateurs — ✅ 100%
- ✅ CRUD complet
- ✅ Rôles et permissions (RBAC)
- ✅ Reset password
- ✅ Toggle actif/inactif
- ✅ Profil personnel
- ✅ Sécurité (2FA setup, backup codes)

### 🎭 Module Rôles & Permissions — ✅ 100%
- ✅ CRUD complet
- ✅ Attribution de permissions
- ✅ Rôle système "admin" (non supprimable)

### 📋 Journal d'audit — ✅ 100%
- ✅ Liste des actions avec filtres
- ✅ Traçabilité complète (user, action, entity, IP, user-agent)
- ✅ Détails avant/après

### 💾 Sauvegardes BDD — ✅ 100%
- ✅ Création manuelle (mysqldump)
- ✅ Liste, téléchargement, suppression
- ✅ Rétention automatique (7j / 4sem / 12mois)

### 🖥️ Santé système — ✅ 100%
- ✅ Version PHP, extensions, espace disque
- ✅ Permissions dossiers
- ✅ Mode maintenance (activable)

### 🤝 Module Affectations — ✅ 100%
- ✅ CRUD complet
- ✅ Autocomplete équipement (API AJAX)
- ✅ Retour d'affectation → équipement remis "En stock"
- ✅ Workflow complet

### 👤 Module Employés — ✅ 100%
- ✅ CRUD complet
- ✅ Filtres (recherche, service, statut)
- ✅ Utilisé par les Affectations

### 🔧 Module Maintenance — ✅ 100%
- ✅ CRUD complet
- ✅ Workflow équipement automatique
- ✅ Clôture d'intervention

### 🔄 Roadmap (Modules prévus)
- 📋 **Module Campagnes d'inventaire**
- 📊 **Module Rapports** (statistiques avancées + graphiques)
- 📄 **Module Documents** (GED)
- ⚙️ **Module Paramètres** (SMTP, notifications, sécurité)

---

## 🛠️ Stack Technique

| Technologie | Usage |
|-------------|-------|
| **PHP 8.2+** | Langage principal (architecture MVC maison) |
| **MySQL 8.4** | Base de données relationnelle |
| **PDO** | Accès BDD sécurisé (requêtes préparées) |
| **Composer** | Gestionnaire de dépendances PHP |
| **Bootstrap 5.3** | Framework CSS responsive (via CDN) |
| **Bootstrap Icons** | Icônes vectorielles |
| **JavaScript (Vanilla)** | Interactions AJAX |
| **Dompdf** | Génération de PDF |
| **PhpSpreadsheet** | Génération de fichiers Excel |
| **Endroid QR Code** | Génération des QR codes |
| **Monolog** | Logging applicatif |
| **Google2FA** | Authentification 2FA |

**Architecture :**
- Pattern **MVC** (Model-View-Controller)
- **Repository Pattern** (interfaces + implémentations MySQL)
- **Service Layer** pour la logique métier
- **Middleware** (Auth, CSRF, Guest, Permission, RateLimit)
- **DTO** et **Enums** pour la robustesse des données

---

## 📁 Structure du Projet

```
Parc-Informatique/
│
├── app/ # Code applicatif (PSR-4)
│ ├── Controllers/ # Contrôleurs
│ ├── Core/ # Mini-framework (Router, Database, Csrf...)
│ ├── DTO/ # Data Transfer Objects
│ ├── Enums/ # Énumérations PHP
│ ├── Exceptions/ # Exceptions personnalisées
│ ├── Helpers/functions.php # url(), e(), csrf_field(), flash()...
│ ├── Middleware/ # Filtres de requêtes
│ ├── Models/ # Modèles métier
│ ├── Repositories/ # Contracts + MySql
│ ├── Services/ # Logique métier
│ └── Validators/ # Validateurs
│
├── config/ # app.php, database.php
├── database/ # schema.sql, seed.sql, fix_encoding.sql
├── public/ # Document root (accessible web)
│ ├── assets/css|js|images
│ ├── .htaccess
│ └── index.php # Point d'entrée unique
├── resources/views/ # Vues PHP
├── routes/ # web.php, api.php
├── scripts/ # Scripts CLI
├── storage/ # logs, backups, exports, qrcodes
├── tests/ # PHPUnit
├── .env # Variables d'environnement (ignoré Git)
├── .env.example # Modèle
├── composer.json
├── CHANGELOG.md
├── LICENSE
└── README.md


---

## 🚀 Installation

### Prérequis

- **PHP 8.2** ou supérieur
- **MySQL 8.0** ou supérieur (ou MariaDB 11+)
- **Composer** ([getcomposer.org](https://getcomposer.org/download/))
- **WampServer 3.4+** (recommandé sur Windows)
  - Apache 2.4.65 (port 80)
  - PHP 8.3.28
  - MySQL 8.4.7

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
      APP_URL=http://localhost/Parc-Informatique/public
      APP_TIMEZONE=Africa/Algiers
      APP_LOCALE=fr

      DB_CONNECTION=mysql
      DB_HOST=127.0.0.1
      DB_PORT=3306
      DB_DATABASE=parc_informatique
      DB_USERNAME=root
      DB_PASSWORD=
      DB_CHARSET=utf8mb4
      DB_COLLATION=utf8mb4_unicode_ci

      SESSION_NAME=PARC_SESSION
      SESSION_LIFETIME=1800
  ```

### Étape 4 : Créer la base de données

Ouvrez phpMyAdmin : http://localhost/phpmyadmin5.2.3/

Créez une base parc_informatique (interclassement utf8mb4_unicode_ci)

Importez database/schema.sql (onglet Importer)

Importez database/seed.sql (données initiales)

### Étape 5 : Placer le projet dans WampServer :

dans `C:\wamp64\www\Parc-Informatique\` 

(⚠️ Respectez la casse : P et I majuscules)

### Étape 6 : Lancer l'application

Ouvrez dans votre navigateur :

```
http://localhost/Parc-Informatique/public/

```
### Étape 7 : Se connecter

Champ	Valeur par défaut

```
Nom d'utilisateur : admin
Mot de passe      : admin

```
⚠️ Changez ce mot de passe après la première connexion ! (via Profil → Sécurité)

---

📊 Modèle de données

Le projet utilise 39 tables MySQL, dont :

Table	Description
equipment	Équipements du parc
equipment_categories	Catégories (PC, imprimante...)
equipment_statuses	Statuts (En service, En panne...)
brands	Marques (HP, Dell...)
services	Services de l'entreprise
sites	Sites physiques
locations	Localisations précises
employees	Employés
assignments	Affectations d'équipements
maintenance	Historique de maintenance
reformations	Équipements réformés
inventory_campaigns	Campagnes d'inventaire
audit_logs	Journal d'audit
users	Utilisateurs
roles / permissions	RBAC

🧪 Scripts utilitaires

bash
# Tester la connexion à la base

php scripts/test_charset.php

# Tester le module équipement

php scripts/test_equipment.php

# Réinitialiser l'admin

php scripts/reset_admin.php

🐛 Dépannage

L'icône WampServer reste orange
→ Conflit de port 80. Vérifiez qu'aucun autre programme (Skype, IIS) n'utilise le port 80.

Erreur 404 sur toutes les pages
→ Vérifiez que BASE_PATH dans public/index.php correspond exactement au nom du dossier (casse incluse).

Les accents s'affichent mal
→ Vérifiez que la BDD et les tables sont en utf8mb4_unicode_ci, et que charset=utf8mb4 est dans le DSN PDO.

Composer introuvable
→ Vérifiez que C:\ProgramData\ComposerSetup\bin est dans le PATH Windows.

🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

Fork le projet

Créez une branche (git checkout -b feature/nouvelle-fonctionnalite)

Commit (git commit -m 'Ajout de la fonctionnalité X')

Push (git push origin feature/nouvelle-fonctionnalite)

Ouvrez une Pull Request

📝 Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

👤 Auteur

corro74-hue

     GitHub : @corro74-hue

🙏 Remerciements

    Bootstrap & Bootstrap Icons

    Dompdf, PhpSpreadsheet, Endroid QR Code

    La communauté PHP

---

## 📅 Changelog

Voir le fichier [CHANGELOG.md](CHANGELOG.md) pour l'historique complet.

**Version actuelle : `0.5.0`**

---

<div align="center">

### ⭐ Si ce projet vous a aidé, n'hésitez pas à lui donner une étoile !

**Fait avec ❤️ en PHP**

[⬆ Retour en haut](#-parc-info--gestion-de-parc-informatique)

</div>