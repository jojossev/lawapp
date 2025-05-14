<?php
$page_title = "Gestion des Catégories de Vidéos";
require_once __DIR__ . '/admin_auth_check.php'; // Auth et connexion BDD ($pdo)
require_once __DIR__ . '/admin_header.php';

// Messages flash (succès/erreur)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

try {
    $stmt_categories = $pdo->query("
        SELECT 
            cv.id, 
            cv.nom,
            cv.description,
            cv.date_creation,
            (SELECT COUNT(*) FROM videos WHERE id_categorie = cv.id) AS nombre_videos
        FROM categories_videos cv
        ORDER BY cv.nom ASC
    ");
    $categories_liste = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des catégories de vidéos : " . $e->getMessage();
    $categories_liste = [];
}
?>

<div class="admin-content">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message && empty($categories_liste)):
 ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <p>
        <a href="video_category_form.php?action=add" class="btn btn-primary">Ajouter une nouvelle catégorie de vidéo</a>
    </p>

    <?php if (empty($categories_liste) && !$error_message):
 ?>
        <p>Aucune catégorie de vidéo n'a été ajoutée pour le moment.</p>
    <?php elseif (!empty($categories_liste)):
 ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom de la Catégorie</th>
                    <th>Description</th>
                    <th>Nombre de Vidéos</th>
                    <th>Date Création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories_liste as $categorie_item):
 ?>
                    <tr>
                        <td><?php echo htmlspecialchars($categorie_item['id']); ?></td>
                        <td><?php echo htmlspecialchars($categorie_item['nom']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($categorie_item['description'] ?? 'N/A')); ?></td>
                        <td><?php echo htmlspecialchars($categorie_item['nombre_videos']); ?></td>
                        <td><?php echo htmlspecialchars( (new DateTime($categorie_item['date_creation']))->format('d/m/Y H:i') ); ?></td>
                        <td>
                            <a href="video_category_form.php?action=edit&id=<?php echo $categorie_item['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                            <a href="video_category_actions.php?action=delete&id=<?php echo $categorie_item['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Attention, cela pourrait affecter les vidéos associées.');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
     <p>
        <a href="manage_videos.php" class="btn btn-outline-secondary mt-3">&larr; Retour à la gestion des vidéos</a>
    </p>
</div>

<?php
require_once __DIR__ . '/admin_footer.php';
?>
