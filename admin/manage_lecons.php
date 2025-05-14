<?php
$page_title = "Gestion des Leçons";
require_once 'admin_auth_check.php';
require_once 'admin_header.php';

// Récupérer l'ID du module
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : null;

if (!$module_id) {
    $_SESSION['error_message'] = "ID du module non spécifié.";
    header('Location: manage_cours.php');
    exit;
}

try {
    // Récupérer les informations du module et du cours parent
    $stmt_module = $pdo->prepare("
        SELECT m.*, c.titre AS titre_cours, c.id AS cours_id
        FROM modules m
        JOIN cours c ON m.id_cours = c.id
        WHERE m.id = ?
    ");
    $stmt_module->execute([$module_id]);
    $module = $stmt_module->fetch(PDO::FETCH_ASSOC);

    if (!$module) {
        $_SESSION['error_message'] = "Module non trouvé.";
        header('Location: manage_cours.php');
        exit;
    }

    // Récupérer toutes les leçons du module
    $stmt_lecons = $pdo->prepare("
        SELECT 
            l.*,
            (SELECT COUNT(*) FROM quiz q WHERE q.id_lecon = l.id) as nombre_quiz
        FROM lecons l
        WHERE l.id_module = ?
        ORDER BY l.ordre_affichage ASC
    ");
    $stmt_lecons->execute([$module_id]);
    $lecons = $stmt_lecons->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des données : " . $e->getMessage();
    $lecons = [];
}

// Messages flash
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="manage_cours.php">Cours</a></li>
            <li class="breadcrumb-item"><a href="manage_modules.php?cours_id=<?php echo $module['cours_id']; ?>">Modules de <?php echo htmlspecialchars($module['titre_cours']); ?></a></li>
            <li class="breadcrumb-item active">Leçons de <?php echo htmlspecialchars($module['titre']); ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <a href="lecon_form.php?module_id=<?php echo $module_id; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Leçon
        </a>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Leçons du module : <?php echo htmlspecialchars($module['titre']); ?></h2>
        </div>
        <div class="card-body">
            <?php if (empty($lecons)): ?>
                <p class="text-muted">Aucune leçon n'a encore été créée pour ce module.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ordre</th>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Durée</th>
                                <th>Quiz</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="leconsTableBody">
                            <?php foreach ($lecons as $lecon): ?>
                                <tr data-lecon-id="<?php echo $lecon['id']; ?>">
                                    <td>
                                        <input type="number" 
                                               class="form-control form-control-sm ordre-lecon" 
                                               value="<?php echo $lecon['ordre_affichage']; ?>" 
                                               style="width: 70px;"
                                               min="0">
                                    </td>
                                    <td><?php echo htmlspecialchars($lecon['titre']); ?></td>
                                    <td><?php echo htmlspecialchars($lecon['type_contenu']); ?></td>
                                    <td><?php echo htmlspecialchars($lecon['duree_estimee']); ?></td>
                                    <td>
                                        <?php if ($lecon['nombre_quiz'] > 0): ?>
                                            <span class="badge bg-info"><?php echo $lecon['nombre_quiz']; ?> quiz</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Aucun quiz</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $lecon['statut'] === 'publie' ? 'success' : 'warning'; ?>">
                                            <?php echo htmlspecialchars($lecon['statut']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="manage_quiz.php?lecon_id=<?php echo $lecon['id']; ?>" 
                                               class="btn btn-sm btn-info" 
                                               title="Gérer les quiz">
                                                <i class="fas fa-question-circle"></i>
                                            </a>
                                            <a href="lecon_form.php?id=<?php echo $lecon['id']; ?>&module_id=<?php echo $module_id; ?>" 
                                               class="btn btn-sm btn-primary" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-lecon" 
                                                    data-lecon-id="<?php echo $lecon['id']; ?>"
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestionnaire pour la suppression des leçons
    document.querySelectorAll('.delete-lecon').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette leçon et tous ses quiz ?')) {
                const leconId = this.dataset.leconId;
                // Envoyer la requête de suppression
                fetch('lecon_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&id=${leconId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Supprimer la ligne du tableau
                        this.closest('tr').remove();
                        // Afficher un message de succès
                        alert('Leçon supprimée avec succès');
                    } else {
                        alert(data.message || 'Erreur lors de la suppression');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la suppression');
                });
            }
        });
    });

    // Gestionnaire pour la mise à jour de l'ordre d'affichage
    let timeoutId;
    document.querySelectorAll('.ordre-lecon').forEach(input => {
        input.addEventListener('change', function() {
            const leconId = this.closest('tr').dataset.leconId;
            const newOrder = this.value;

            // Annuler le timeout précédent si existe
            if (timeoutId) clearTimeout(timeoutId);

            // Définir un nouveau timeout
            timeoutId = setTimeout(() => {
                fetch('lecon_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_order&id=${leconId}&ordre=${newOrder}`
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.message || 'Erreur lors de la mise à jour de l\'ordre');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la mise à jour de l\'ordre');
                });
            }, 500);
        });
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
