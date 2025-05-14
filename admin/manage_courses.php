<?php
$page_title = "Gestion des Cours";
require_once __DIR__ . '/admin_auth_check.php'; // Auth et config
require_once __DIR__ . '/admin_header.php';

// Messages flash (succès/erreur)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

try {
    // Récupérer les cours avec les noms des catégories et des créateurs
    $stmt_cours = $pdo->query("
        SELECT 
            c.id, 
            c.titre, 
            c.niveau, 
            c.statut, 
            c.date_creation,
            c.image_url, /* <-- Ajouter cette ligne */
            cat.nom_categorie,
            CONCAT(u.prenom, ' ', u.nom) AS nom_createur
        FROM cours c
        LEFT JOIN categories_cours cat ON c.id_categorie = cat.id
        LEFT JOIN utilisateurs u ON c.id_createur = u.id
        ORDER BY c.date_creation DESC
    ");
    $cours_liste = $stmt_cours->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des cours : " . $e->getMessage();
    $cours_liste = []; // Initialiser comme tableau vide en cas d'erreur
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

    <p>
        <a href="course_form.php?action=add" class="btn btn-primary">Ajouter un nouveau cours</a>
    </p>

    <?php if (empty($cours_liste) && !$error_message): ?>
        <p>Aucun cours n'a été créé pour le moment.</p>
    <?php elseif (!empty($cours_liste)): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th> <!-- Ajouter cet en-tête -->
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Niveau</th>
                    <th>Statut</th>
                    <th>Créateur</th>
                    <th>Date Création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cours_liste as $cours_item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cours_item['id']); ?></td>
                        <td>
                            <?php if (!empty($cours_item['image_url'])): ?>
                                <a href="../<?php echo htmlspecialchars($cours_item['image_url']); ?>" target="_blank">
                                    <img src="../<?php echo htmlspecialchars($cours_item['image_url']); ?>" alt="Couverture" style="width: 50px; height: auto; border: 1px solid #ddd;" />
                                </a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($cours_item['titre']); ?></td>
                        <td><?php echo htmlspecialchars($cours_item['nom_categorie'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($cours_item['niveau'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($cours_item['statut'])); ?></td>
                        <td><?php echo htmlspecialchars($cours_item['nom_createur'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($cours_item['date_creation']))); ?></td>
                        <td>
                            <a href="course_form.php?action=edit&id=<?php echo $cours_item['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                            <a href="course_actions.php?action=delete&id=<?php echo $cours_item['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce cours ?');">Supprimer</a>
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

