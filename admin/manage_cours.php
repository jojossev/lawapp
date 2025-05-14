<?php
session_start();
require_once __DIR__ . '/../includes/config.php'; // Inclut db_connect et session_start
require_once __DIR__ . '/admin_auth_check.php'; // Vérif auth admin
require_once __DIR__ . '/includes/admin_functions.php';

// admin_auth_check.php gère l'authentification.

$pageTitle = 'Gestion des Cours';

// Récupérer les cours depuis la base de données avec le nom de la catégorie
try {
    $sql = "SELECT c.id, c.titre, c.statut, c.date_creation, cat.nom 
            FROM cours c 
            LEFT JOIN categories_cours cat ON c.id_categorie = cat.id 
            ORDER BY c.date_creation DESC";
    $stmt = $pdo->query($sql);
    $coursList = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des cours: " . $e->getMessage());
    $_SESSION['error_message_cours'] = "Impossible de charger la liste des cours.";
    $coursList = []; // Tableau vide pour éviter les erreurs
}

$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

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

    <a href="cours_form.php" class="btn btn-primary mb-3">Ajouter un Cours</a>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Statut</th>
                    <th>Date Création</th>
                    <th style="width: 200px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($coursList)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Aucun cours trouvé.</td>
                    </tr>
                <?php else: 
                    foreach ($coursList as $cours): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cours['id']); ?></td>
                            <td><?php echo htmlspecialchars($cours['titre']); ?></td>
                            <td><?php echo htmlspecialchars($cours['nom'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($cours['statut'])); ?></td>
                            <td>
                                <?php echo date('d/m/Y H:i', strtotime($cours['date_creation'])); ?>
                            </td>
                            <td>
                                <a href="manage_cours_contenu.php?cours_id=<?php echo $cours['id']; ?>" class="btn btn-sm btn-secondary me-1 mb-1" title="Gérer le Contenu (Modules/Leçons)"><i class="fas fa-list-alt"></i> Contenu</a>
                                <a href="cours_form.php?id=<?php echo $cours['id']; ?>" class="btn btn-sm btn-primary me-1 mb-1" title="Modifier les informations du cours"><i class="fas fa-edit"></i> Meta</a>
                                <form action="cours_actions.php" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce cours ? Ceci supprimera aussi tous ses modules et leçons !');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $cours['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm mb-1" title="Supprimer le cours"><i class="fas fa-trash-alt"></i> Suppr.</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
