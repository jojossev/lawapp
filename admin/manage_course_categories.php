<?php
// admin/manage_course_categories.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php'; // Vérification de l'authentification de l'admin
require_once __DIR__ . '/admin_header.php'; // En-tête de l'interface d'administration

$page_title = 'Gérer les Catégories de Cours';

// Récupérer les messages de succès ou d'erreur de la session
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Récupérer toutes les catégories de cours
try {
    $stmt = $pdo->query("SELECT id, nom_categorie, description_categorie AS description FROM categories_cours ORDER BY nom_categorie ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des catégories de cours : " . $e->getMessage());
    $error_message = "Erreur lors de la récupération des catégories de cours. Veuillez réessayer.";
    $categories = [];
}
?>

<div class="container-fluid">
    <h1 class="mt-4"><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-tags me-1"></i>
            Liste des Catégories de Cours
            <a href="course_category_form.php" class="btn btn-primary btn-sm float-end">
                <i class="fas fa-plus"></i> Ajouter une catégorie
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($categories) && !$error_message): ?>
                <div class="alert alert-info">Aucune catégorie de cours n'a été créée pour le moment.</div>
            <?php elseif (!empty($categories)):
 ?>
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom de la Catégorie</th>
                            <th>Description</th>
                            <th style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $categorie): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($categorie['id']); ?></td>
                                <td><?php echo htmlspecialchars($categorie['nom_categorie']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($categorie['description'] ?? 'N/A')); ?></td>
                                <td>
                                    <a href="course_category_form.php?id=<?php echo $categorie['id']; ?>" class="btn btn-warning btn-sm me-2" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="course_category_actions.php?action=delete&id=<?php echo $categorie['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       title="Supprimer"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Cette action est irréversible.');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/admin_footer.php'; // Pied de page de l'interface d'administration
?>
