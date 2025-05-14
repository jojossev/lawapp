<?php
// Informations de connexion à la base de données
$db_host = 'localhost';
$db_name = 'lawapp';
$db_user = 'root';
$db_pass = ''; // Laissez vide si pas de mot de passe pour root

// Options PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Gérer les erreurs comme des exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourner les résultats en tableaux associatifs
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Désactiver l'émulation des requêtes préparées pour la sécurité
];

try {
    // Création de l'instance PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $options
    );
} catch (PDOException $e) {
    // Dans un environnement de production, vous voudriez logger cette erreur et afficher un message plus générique.
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    // Afficher une erreur plus conviviale à l'utilisateur et arrêter le script si nécessaire.
    // Pour le développement, vous pouvez laisser le message d'erreur détaillé.
    die("Erreur de connexion à la base de données. Veuillez vérifier la configuration et que le serveur MySQL est bien démarré. Détail : " . $e->getMessage());
}
?>
