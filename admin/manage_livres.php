<?php
$page_title = "Gestion des Livres";
require_once __DIR__ . '/admin_auth_check.php'; // Auth et config ($pdo)
require_once __DIR__ . '/admin_header.php';

// Messages flash (succès/erreur)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Pagination (simple exemple, à développer si besoin)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20; // Nombre de livres par page
$offset = ($page - 1) * $limit;

try {
    // Compter le nombre total de livres pour la pagination
    $total_livres_stmt = $pdo->query("SELECT COUNT(*) FROM livres");
    $total_livres = $total_livres_stmt->fetchColumn();
    $total_pages = ceil($total_livres / $limit);

    // Récupérer les livres avec les noms des catégories (si applicable)
    $stmt_livres = $pdo->prepare("
        SELECT 
            l.id, 
            l.titre, 
            l.auteur,
            l.statut, 
            l.date_creation,
            l.image_url,
            cl.nom AS nom_categorie_livre
        FROM livres l
        LEFT JOIN categories_livres cl ON l.id_categorie = cl.id
        ORDER BY l.date_creation DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt_livres->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt_livres->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt_livres->execute();
    $livres_liste = $stmt_livres->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des livres : " . $e->getMessage();
    $livres_liste = []; 
    $total_pages = 0;
}
?>

<div class="admin-content">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message && empty($livres_liste)): // Afficher l'erreur PDO uniquement si rien n'a pu être chargé ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <p>
        <a href="livre_form.php?action=add" class="btn btn-primary">Ajouter un nouveau livre</a>
    </p>

    <?php if (empty($livres_liste) && !$error_message): ?>
        <p>Aucun livre n'a été ajouté pour le moment.</p>
    <?php elseif (!empty($livres_liste)):
 ?>
        <table class="table table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Couverture</th>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Catégorie</th>
                    <th>Statut</th>
                    <th>Date Ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($livres_liste as $livre_item):
 ?>
                    <tr>
                        <td><?php echo htmlspecialchars($livre_item['id']); ?></td>
                        <td>
                            <?php if (!empty($livre_item['image_url'])): ?>
                                <img src="../<?php echo htmlspecialchars($livre_item['image_url']); ?>" alt="Couverture" style="width: 50px; height: auto; border-radius: 3px;">
                            <?php else: ?>
                                <span class="text-muted small">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($livre_item['titre']); ?></td>
                        <td><?php echo htmlspecialchars($livre_item['auteur'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($livre_item['nom_categorie_livre'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($livre_item['statut'])); ?></td>
                        <td>
                            <?php 
                            try {
                                echo htmlspecialchars((new DateTime($livre_item['date_creation']))->format('d/m/Y H:i')); 
                            } catch (Exception $e) {
                                echo 'Date invalide';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="livre_form.php?action=edit&id=<?php echo $livre_item['id']; ?>" class="btn btn-sm btn-outline-warning">Modifier</a>
                                <a href="livre_actions.php?action=delete&id=<?php echo $livre_item['id']; ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livre ? Cette action est irréversible.');">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php
require_once __DIR__ . '/admin_footer.php';
?>
