<?php
session_start();
require_once __DIR__ . '/admin_auth_check.php'; // Vérifie l'authentification et définit $pdo & BASE_URL via db_connect.php inclus dans config.php (indirectement)
require_once __DIR__ . '/../utils/redirect_helpers.php'; // Pour les messages flash // Pour les messages

// admin_auth_check.php a déjà vérifié l'authentification admin.

$pageTitle = 'Gestion des Catégories de Cours';

// Récupérer les catégories de cours depuis la base de données
try {
    $stmt = $pdo->query("SELECT id, nom, description, date_creation FROM categories_cours ORDER BY nom ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des catégories de cours: " . $e->getMessage());
    // Gérer l'erreur, peut-être rediriger avec un message d'erreur
    $_SESSION['error_message_cat_cours'] = "Erreur DB: " . htmlspecialchars($e->getMessage()); // Afficher l'erreur PDO réelle
    $categories = []; // Tableau vide pour éviter les erreurs PHP plus tard
}

// Récupérer les messages de succès/erreur de la session
$successMessage = $_SESSION['success_message_cat_cours'] ?? null;
$errorMessage = $_SESSION['error_message_cat_cours'] ?? null;
unset($_SESSION['success_message_cat_cours'], $_SESSION['error_message_cat_cours']); // Nettoyer les messages

include 'admin_header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4"><?php echo $pageTitle; ?></h1>

    <?php if ($successMessage): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <a href="categorie_cours_form.php" class="btn btn-primary mb-3">Ajouter une Catégorie</a>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom de la Catégorie</th>
                    <th>Description</th>
                    <th>Date de Création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" class="text-center">Aucune catégorie de cours trouvée.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $categorie): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($categorie['id']); ?></td>
                            <td><?php echo htmlspecialchars($categorie['nom']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($categorie['description'] ?? 'N/A')); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($categorie['date_creation'])); ?></td>
                            <td>
                                <a href="categorie_cours_form.php?id=<?php echo $categorie['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                                <a href="categorie_cours_actions.php?action=delete&id=<?php echo $categorie['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Cette action est irréversible.');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
