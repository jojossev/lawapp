<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Votre nom d'utilisateur MySQL (par défaut 'root' pour XAMPP)
define('DB_PASSWORD', '');     // Votre mot de passe MySQL (par défaut vide pour XAMPP)
define('DB_NAME', 'lawappdb');// Configuration base de données uniquement

$pdo = null; // Initialiser $pdo

/* Tenter de se connecter à la base de données MySQL */
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Définir le mode d'erreur PDO sur exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Définir le jeu de caractères en utf8mb4 pour une bonne gestion des caractères spéciaux
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch(PDOException $e){
    error_log("ERREUR: Impossible de se connecter à la base de données. " . $e->getMessage());
    die("ERREUR: Impossible de se connecter à la base de données. Veuillez vérifier la configuration et vous assurer que le serveur MySQL est en cours d'exécution. Si le problème persiste, contactez l'administrateur.");
}
?>
