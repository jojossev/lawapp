<?php
$page_title = "Gestion des Catégories de Livres";
require_once __DIR__ . '/admin_auth_check.php'; // Auth et config ($pdo)
require_once __DIR__ . '/admin_header.php';

// Messages flash
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Récupération des catégories
$categories = [];
$fetch_error = null;
try {
    $stmt = $pdo->query("SELECT id, nom FROM categories_livres ORDER BY nom");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $fetch_error = "Erreur lors de la récupération des catégories : " . $e->getMessage();
}

?>

<div class="admin-content">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    <?php if ($fetch_error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($fetch_error); ?></div>
    <?php endif; ?>

    <p>
        <a href="categorie_livre_form.php?action=add" class="btn btn-primary">Ajouter une catégorie</a>
    </p>

    <?php if (empty($categories) && !$fetch_error): ?>
        <p>Aucune catégorie de livre n'a été ajoutée.</p>
    <?php elseif (!empty($categories)): ?>
        <table class="table table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Nom de la Catégorie</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cat['id']); ?></td>
                        <td><?php echo htmlspecialchars($cat['nom']); ?></td>
                        <td>
                            <a href="categorie_livre_form.php?action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                            <form action="categorie_livre_actions.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Cela pourrait affecter les livres associés.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/admin_footer.php';
?>
