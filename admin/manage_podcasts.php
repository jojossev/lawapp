<?php
session_start();
require_once '../db_connect.php'; // Ajustez le chemin si nécessaire
require_once 'admin_auth_check.php'; // Vérification de la connexion admin
require_once 'admin_includes/admin_functions.php'; // Fonctions utilitaires (si besoin)

// Récupérer les podcasts depuis la base de données
$podcasts = [];
$error_message = '';

try {
    // Sélectionner les colonnes nécessaires et joindre avec categories_podcasts
    // Utiliser les noms de colonnes de VOTRE table
    $sql = "SELECT 
                p.id, 
                p.titre, 
                p.description,
                p.url_audio, 
                p.duree,
                p.statut, 
                p.date_creation, 
                c.nom AS nom_categorie,
                CONCAT(u.prenom, ' ', u.nom) AS nom_createur
            FROM 
                podcasts p
            LEFT JOIN 
                categories_podcasts c ON p.id_categorie = c.id
            LEFT JOIN
                utilisateurs u ON p.id_createur = u.id
            ORDER BY 
                p.date_creation DESC"; // Trier par date de publication récente

    $stmt = $pdo->query($sql);
    $podcasts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des podcasts: " . $e->getMessage());
    $error_message = "Impossible de charger les podcasts."; // Message d'erreur générique
    // Conserver le message d'erreur de session s'il existe déjà (par ex. après une redirection)
    if (isset($_SESSION['error_message_podcast'])) {
         $error_message = $_SESSION['error_message_podcast'];
         unset($_SESSION['error_message_podcast']);
    }
}

// Inclure l'en-tête de l'admin
$pageTitle = "Gérer les Podcasts";
include 'admin_header.php'; 
?>

<div class="container mt-4">
    <h2>Gestion des Podcasts</h2>

    <?php if (!empty($_SESSION['success_message_podcast'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success_message_podcast']); unset($_SESSION['success_message_podcast']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <a href="podcast_form.php?action=add" class="btn btn-primary mb-3">Ajouter un Podcast</a>

    <?php if (empty($podcasts) && empty($error_message)): ?>
        <p>Aucun podcast trouvé.</p>
    <?php elseif (!empty($podcasts)): ?>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Créateur</th>
                    <th>Catégorie</th>
                    <th>Publié</th>
                    <th>Date Création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($podcasts as $podcast): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($podcast['id']); ?></td>
                        <td><?php echo htmlspecialchars($podcast['titre']); ?></td>
                        <td><?php echo htmlspecialchars($podcast['description']); ?></td>
                        <td><?php echo htmlspecialchars($podcast['nom_createur'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($podcast['nom_categorie'] ?? 'Aucune'); ?></td>
                        <td><?php echo $podcast['statut'] === 'publie' ? 'Oui' : 'Non'; ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($podcast['date_creation']))); ?></td>
                        <td>
                            <a href="podcast_form.php?action=edit&id=<?php echo $podcast['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                            <a href="podcast_actions.php?action=delete&id=<?php echo $podcast['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce podcast ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<?php include 'admin_footer.php'; ?>
