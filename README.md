# LawApp - Application d'Assistance Juridique

## Configuration requise
- PHP 8.0 ou supérieur
- PostgreSQL 13 ou supérieur (ou MySQL 5.7+ pour développement local)
- Serveur Web Apache
- Extensions PHP PDO_PGSQL et PDO_MySQL activées

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
   - Pour le développement local, il est configuré par défaut pour fonctionner avec :
     - Host: localhost
     - Database: lawapp
     - Username: root
     - Password: (vide)
   - Pour la production, utilisez les variables d'environnement suivantes :
     - `DATABASE_URL` : URL de connexion PostgreSQL
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

5. Déployez l'application et initialisez la base de données en accédant à :
   - `https://votre-app.onrender.com/admin/init_db.php`

6. Pour plus de détails, consultez le fichier [RENDER_CONFIG.md](RENDER_CONFIG.md)

## Fonctionnalités
- Gestion des cours juridiques
- Bibliothèque de ressources juridiques
- Assistant juridique (chatbot)
- Quiz et évaluations
- Gestion des utilisateurs
- Système de paiement
