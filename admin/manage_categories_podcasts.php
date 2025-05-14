<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once 'admin_auth_check.php'; 
require_once 'admin_includes/admin_functions.php'; 

$page_title = "Gérer les Catégories de Podcasts";
require_once 'admin_header.php';

$categories = [];
$error_message = '';

// Messages de succès/erreur depuis la session
$success_message = $_SESSION['success_message_cat_podcast'] ?? null;
$error_feedback = $_SESSION['error_message_cat_podcast'] ?? null;
if ($success_message) {
    unset($_SESSION['success_message_cat_podcast']);
}
if ($error_feedback) {
    unset($_SESSION['error_message_cat_podcast']);
}

try {
    $stmt = $pdo->query("SELECT id, nom, description, date_creation FROM categories_podcasts ORDER BY nom ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des catégories de podcasts: " . $e->getMessage();
    error_log($error_message);
}

?>

<div class="container admin-container">
    <h2 class="admin-title"><?php echo $page_title; ?></h2>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_feedback): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_feedback); ?></div>
    <?php endif; ?>
    <?php if ($error_message && !$error_feedback): // Affiche l'erreur PDO seulement si pas d'erreur de session ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="admin-actions">
        <a href="categorie_podcast_form.php?action=add" class="btn btn-success">+ Ajouter une catégorie</a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Nom de la catégorie</th>
                <th>Description</th>
                <th>Date de création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $categorie): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($categorie['nom']); ?></td>
                        <td><?php echo htmlspecialchars(substr($categorie['description'], 0, 100)) . (strlen($categorie['description']) > 100 ? '...' : ''); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($categorie['date_creation']))); ?></td>
                        <td class="action-links">
                            <a href="categorie_podcast_form.php?action=edit&id=<?php echo $categorie['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                            <a href="categorie_podcast_actions.php?action=delete&id=<?php echo $categorie['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Attention, cela pourrait affecter les podcasts associés.');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">Aucune catégorie de podcast trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once 'admin_footer.php';
?>
