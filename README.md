# LawApp - Application d'Assistance Juridique

## Configuration requise
- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur Web Apache
- Extension PHP PDO_MySQL activée

## Installation

1. Copiez tous les fichiers dans le dossier web de votre serveur (par exemple: htdocs pour XAMPP)

2. Créez une base de données MySQL nommée 'lawapp'
   ```sql
   CREATE DATABASE lawapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Importez le fichier de base de données
   - Ouvrez phpMyAdmin
   - Sélectionnez la base de données 'lawapp'
   - Importez le fichier 'database/database_dump.sql'

4. Configuration
   - Le fichier de configuration se trouve dans 'includes/config.php'
   - Par défaut, il est configuré pour fonctionner avec :
     - Host: localhost
     - Database: lawapp
     - Username: root
     - Password: (vide)
   - Modifiez ces valeurs si nécessaire selon votre configuration

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

## Fonctionnalités
- Gestion des cours juridiques
- Bibliothèque de ressources juridiques
- Assistant juridique (chatbot)
- Quiz et évaluations
- Gestion des utilisateurs
- Système de paiement
