# LawApp - Application d'Assistance Juridique

## Configuration requise
- PHP 8.0 ou supérieur
- PostgreSQL 13 ou supérieur (pour production)
- MySQL 5.7+ (pour développement local)
- Serveur Web Apache
- Extensions PHP PDO_PGSQL (production) ou PDO_MySQL (développement local) activées

## Installation

1. Copiez tous les fichiers dans le dossier web de votre serveur (par exemple: htdocs pour XAMPP)

2. Base de données

   **Pour PostgreSQL (production)**
   ```sql
   CREATE DATABASE lawapp;
   CREATE USER lawapp_user WITH PASSWORD 'votre_mot_de_passe';
   GRANT ALL PRIVILEGES ON DATABASE lawapp TO lawapp_user;
   ```

   **Pour MySQL (développement local)**

   ```sql
   CREATE DATABASE lawapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Initialisation de la base de données
   - Accédez à l'URL : `http://localhost/LawApp/admin/init_db.php`
   - Ou exécutez les scripts SQL dans le dossier `database/`

4. Configuration
   - Le fichier de configuration se trouve dans `includes/config.php`
   - Pour le développement local, il est configuré par défaut pour fonctionner avec MySQL :
     - Host: localhost
     - Database: lawapp
     - Username: root
     - Password: (vide)
   - Pour la production, utilisez les variables d'environnement suivantes :
     - `DATABASE_URL` : URL de connexion PostgreSQL (format: `postgresql://user:password@host:port/database`)
     - `APP_URL` : URL de l'application
     - `ENVIRONMENT` : production

5. Permissions
   - Assurez-vous que les dossiers suivants sont accessibles en écriture :
     - uploads/
     - uploads/cours_couvertures/
     - uploads/lecons/
     - uploads/podcasts/audio/
     - uploads/podcasts/images/

6. Accès à l'application
   - Frontend : http://localhost/LawApp/
   - Administration : http://localhost/LawApp/admin/
   - Identifiants admin par défaut :
     - Email : admin@lawapp.com
     - Mot de passe : admin123

## Structure des dossiers

- admin/ : Interface d'administration
- includes/ : Fichiers de configuration et classes
- assets/ : Ressources CSS, JS, images
- uploads/ : Fichiers uploadés
- database/ : Scripts SQL

## Déploiement sur Render

1. Créez un compte sur [Render](https://render.com/)

2. Créez un service Web :
   - Connectez votre dépôt GitHub
   - Type : Web Service
   - Runtime : Docker
   - Plan : Free

3. Configurez les variables d'environnement :
   - `ENVIRONMENT` : production
   - `APP_URL` : ${RENDER_EXTERNAL_URL}
   - `DATABASE_URL` : postgresql://user:password@host:port/database

4. Créez une base de données PostgreSQL :
   - Type : PostgreSQL
   - Version : 13 ou supérieur
   - Plan : Free

5. Déployez l'application et initialisez la base de données en accédant à l'une des URLs suivantes :
   - `https://votre-app.onrender.com/init_db_redirect.php` (recommandé)
   - `https://votre-app.onrender.com/admin/init_db.php`

6. Vous pouvez vérifier l'état des tables avec les scripts de diagnostic :
   - `https://votre-app.onrender.com/test_utilisateurs.php` - Vérifie la table des utilisateurs
   - `https://votre-app.onrender.com/test_livres.php` - Vérifie la table des livres
   - `https://votre-app.onrender.com/test_categories_podcasts.php` - Vérifie la table des catégories de podcasts
   - `https://votre-app.onrender.com/test_inscriptions.php` - Vérifie la table des inscriptions

7. Pour plus de détails, consultez le fichier [RENDER_CONFIG.md](RENDER_CONFIG.md)

## Structure de la base de données

### Compatibilité des bases de données

L'application est compatible avec deux systèmes de gestion de base de données :

- **PostgreSQL** : Utilisé en production sur Render
- **MySQL** : Recommandé pour le développement local

Les scripts de création de tables sont adaptés pour fonctionner avec les deux systèmes. Les principales différences sont :

- PostgreSQL utilise `SERIAL` pour les clés auto-incrémentées, MySQL utilise `AUTO_INCREMENT`
- PostgreSQL utilise `ON CONFLICT DO NOTHING`, MySQL utilise `INSERT IGNORE`

### Tables de la base de données

La base de données contient les tables suivantes :

1. **utilisateurs** - Informations des utilisateurs
   - `id` - Identifiant unique
   - `nom`, `prenom` - Nom et prénom de l'utilisateur
   - `email` - Adresse email (unique)
   - `mot_de_passe` - Mot de passe hashé
   - `role` - Rôle de l'utilisateur (admin, utilisateur, etc.)
   - `date_inscription`, `derniere_connexion` - Dates d'inscription et de dernière connexion

2. **categories_cours** - Catégories pour les cours
   - `id` - Identifiant unique
   - `nom` - Nom de la catégorie
   - `description` - Description de la catégorie
   - `statut` - Statut (actif, inactif)

3. **cours** - Cours disponibles
   - `id` - Identifiant unique
   - `titre` - Titre du cours
   - `description` - Description du cours
   - `id_categorie` - Catégorie du cours (clé étrangère)
   - `image` - Image du cours
   - `statut` - Statut du cours

4. **categories_livres** - Catégories pour les livres
   - `id` - Identifiant unique
   - `nom` - Nom de la catégorie
   - `description` - Description de la catégorie
   - `statut` - Statut (actif, inactif)

5. **livres** - Livres juridiques
   - `id` - Identifiant unique
   - `titre` - Titre du livre
   - `auteur` - Auteur du livre
   - `description` - Description du livre
   - `annee_publication` - Année de publication
   - `editeur` - Maison d'édition
   - `isbn` - Numéro ISBN
   - `id_categorie` - Catégorie du livre (clé étrangère)
   - `image_couverture` - Image de couverture
   - `fichier_pdf` - Lien vers le fichier PDF
   - `statut` - Statut du livre

6. **categories_podcasts** - Catégories pour les podcasts
   - `id` - Identifiant unique
   - `nom` - Nom de la catégorie
   - `description` - Description de la catégorie
   - `statut` - Statut (actif, inactif)
   - `date_creation` - Date de création

7. **inscriptions** - Inscriptions des utilisateurs aux cours
   - `id` - Identifiant unique
   - `id_utilisateur` - Identifiant de l'utilisateur (clé étrangère)
   - `id_cours` - Identifiant du cours (clé étrangère)
   - `date_inscription` - Date d'inscription
   - `progres` - Progression dans le cours (en pourcentage)
   - `statut` - Statut de l'inscription (actif, inactif)

## Fonctionnalités

- Gestion des cours juridiques
- Bibliothèque de ressources juridiques
- Assistant juridique (chatbot)
- Quiz et évaluations
- Gestion des utilisateurs
- Système de paiement
- Inscription aux cours avec suivi de progression

## Notes techniques

### Résolution des problèmes de tables manquantes

Si vous rencontrez des erreurs SQL du type "relation does not exist" ou "undefined table", vous pouvez utiliser les scripts de correction suivants :

1. **Script global** : `http://localhost/LawApp/fix_all_tables.php`
   - Vérifie et corrige toutes les tables de la base de données en une seule fois

2. **Scripts de diagnostic** :
   - `test_render.php` - Affiche des informations de base sur le serveur et les variables d'environnement
   - `debug_render.php` - Vérifie l'existence et l'accessibilité des fichiers et dossiers importants
   - `check_admin_path.php` - Vérifie l'existence et l'accessibilité du dossier admin
   - `test_db_connection.php` - Teste la connexion à la base de données (MySQL ou PostgreSQL)
   - `test_pg_connection.php` - Teste spécifiquement la connexion à PostgreSQL sur Render
   - `test_utilisateurs.php` - Vérifie la table des utilisateurs
   - `test_livres.php` - Vérifie la table des livres
   - `test_categories_podcasts.php` - Vérifie la table des catégories de podcasts
   - `test_inscriptions.php` - Vérifie la table des inscriptions

3. **Scripts spécifiques** :
   - `fix_admin_table.php` - Vérifie et crée la table des administrateurs
   - `fix_livres_table.php` - Vérifie et crée la table des livres et ses dépendances
   - `fix_podcasts_table.php` - Vérifie et crée la table des podcasts et ses dépendances
   - `fix_cours_table.php` - Vérifie et crée la table des cours et ses dépendances
   - `fix_videos_table.php` - Vérifie et crée la table des vidéos et ses dépendances
   - `fix_users_tables.php` - Vérifie et crée les tables liées aux utilisateurs
   - `fix_foreign_keys.php` - Vérifie et corrige les relations entre les tables

4. **Scripts d'optimisation et de sécurité** :
   - `fix_db_compatibility.php` - Corrige les problèmes de compatibilité entre PostgreSQL et MySQL
   - `fix_db_performance.php` - Optimise les performances de la base de données
   - `fix_db_security.php` - Améliore la sécurité de la base de données
   - `fix_session_cookies.php` - Vérifie et corrige les problèmes de sessions et cookies
   - `fix_files_permissions.php` - Vérifie et corrige les problèmes de fichiers et permissions
   - `fix_redirections.php` - Vérifie et corrige les redirections HTTP en les remplaçant par des redirections JavaScript
   - `fix_inscriptions_table.php` - Vérifie et crée la table des inscriptions aux cours
   - `fix_postgres_tables.php` - Crée et corrige les tables spécifiquement pour PostgreSQL

5. **Scripts de test et diagnostic** :
   - `test_sessions.php` - Teste la création et la persistance des sessions et cookies
   - `test_redirections.php` - Teste les redirections HTTP et affiche les en-têtes de réponse
   - `admin_scripts.php` - Page centralisée pour accéder à tous les scripts de diagnostic et correction
   - `test_inscriptions.php` - Affiche les inscriptions aux cours avec les informations des utilisateurs et des cours
   - `check_render_sessions.php` - Vérifie l'état des sessions sur Render

6. **Scripts globaux** :
   - `fix_all_issues.php` - Exécute tous les scripts de correction en séquence avec une barre de progression
   - `check_app_integrity.php` - Vérifie l'intégrité globale de l'application (tables, répertoires, fichiers, variables d'environnement)

Tous ces scripts sont accessibles depuis la page d'accueil de l'application et sont compatibles avec MySQL et PostgreSQL.

### Redirections JavaScript

Pour éviter les problèmes de "headers already sent", l'application utilise des redirections JavaScript au lieu de la fonction PHP `header()`. Cette approche est implémentée dans les fichiers suivants :

```php
// Exemple de redirection JavaScript
echo "<div style='text-align: center; margin: 20px; font-family: Arial, sans-serif;'>";
echo "<h2>Redirection en cours...</h2>";
echo "<p>Vous allez être redirigé vers une autre page. Si la redirection ne fonctionne pas, <a href='page.php'>cliquez ici</a>.</p>";
echo "<div style='margin: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 4px;'>";
echo "<img src='assets/img/loading.gif' alt='Chargement...' style='width: 50px; height: 50px;'>";
echo "</div>";
echo "</div>";
echo "<script>window.location.href = 'page.php';</script>";
die();
```

### Fichiers avec redirection JavaScript

- `logout.php` - Déconnexion utilisateur
- `admin/admin_logout.php` - Déconnexion administrateur
- `update_settings_process.php` - Mise à jour des paramètres utilisateur
- `edit_profil_process.php` - Modification du profil utilisateur
- `change_password_process.php` - Changement de mot de passe
- `admin/admin_login_process.php` - Connexion administrateur
- `register.php` - Inscription utilisateur
- `view_cours.php` - Affichage des détails d'un cours
- `inscription_cours.php` - Inscription à un cours

Vous pouvez utiliser le script `fix_redirections.php` pour vérifier et corriger automatiquement les redirections HTTP dans ces fichiers.
