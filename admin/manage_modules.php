<?php
$page_title = "Gestion des Modules";
require_once 'admin_auth_check.php';
require_once 'admin_header.php';

// Récupérer l'ID du cours si fourni
$cours_id = isset($_GET['cours_id']) ? (int)$_GET['cours_id'] : null;

if (!$cours_id) {
    $_SESSION['error_message'] = "ID du cours non spécifié.";
    header('Location: manage_cours.php');
    exit;
}

try {
    // Récupérer les informations du cours
    $stmt_cours = $pdo->prepare("SELECT titre FROM cours WHERE id = ?");
    $stmt_cours->execute([$cours_id]);
    $cours = $stmt_cours->fetch(PDO::FETCH_ASSOC);

    if (!$cours) {
        $_SESSION['error_message'] = "Cours non trouvé.";
        header('Location: manage_cours.php');
        exit;
    }

    // Récupérer tous les modules du cours
    $stmt_modules = $pdo->prepare("
        SELECT 
            m.*,
            (SELECT COUNT(*) FROM lecons l WHERE l.id_module = m.id) as nombre_lecons
        FROM modules m
        WHERE m.id_cours = ?
        ORDER BY m.ordre_affichage ASC
    ");
    $stmt_modules->execute([$cours_id]);
    $modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des données : " . $e->getMessage();
    $modules = [];
}

// Messages flash
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <a href="module_form.php?cours_id=<?php echo $cours_id; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouveau Module
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
            <h2 class="h5 mb-0">Modules du cours : <?php echo htmlspecialchars($cours['titre']); ?></h2>
        </div>
        <div class="card-body">
            <?php if (empty($modules)): ?>
                <p class="text-muted">Aucun module n'a encore été créé pour ce cours.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ordre</th>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Nombre de leçons</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="modulesTableBody">
                            <?php foreach ($modules as $module): ?>
                                <tr data-module-id="<?php echo $module['id']; ?>">
                                    <td>
                                        <input type="number" 
                                               class="form-control form-control-sm ordre-module" 
                                               value="<?php echo $module['ordre_affichage']; ?>" 
                                               style="width: 70px;"
                                               min="0">
                                    </td>
                                    <td><?php echo htmlspecialchars($module['titre']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($module['description'], 0, 100)) . '...'; ?></td>
                                    <td><?php echo $module['nombre_lecons']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $module['statut'] === 'publie' ? 'success' : 'warning'; ?>">
                                            <?php echo htmlspecialchars($module['statut']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="manage_lecons.php?module_id=<?php echo $module['id']; ?>" 
                                               class="btn btn-sm btn-info" 
                                               title="Gérer les leçons">
                                                <i class="fas fa-book-reader"></i>
                                            </a>
                                            <a href="module_form.php?id=<?php echo $module['id']; ?>&cours_id=<?php echo $cours_id; ?>" 
                                               class="btn btn-sm btn-primary" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-module" 
                                                    data-module-id="<?php echo $module['id']; ?>"
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
    // Gestionnaire pour la suppression des modules
    document.querySelectorAll('.delete-module').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce module et toutes ses leçons ?')) {
                const moduleId = this.dataset.moduleId;
                // Envoyer la requête de suppression
                fetch('module_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&id=${moduleId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Supprimer la ligne du tableau
                        this.closest('tr').remove();
                        // Afficher un message de succès
                        alert('Module supprimé avec succès');
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
    document.querySelectorAll('.ordre-module').forEach(input => {
        input.addEventListener('change', function() {
            const moduleId = this.closest('tr').dataset.moduleId;
            const newOrder = this.value;

            // Annuler le timeout précédent si existe
            if (timeoutId) clearTimeout(timeoutId);

            // Définir un nouveau timeout
            timeoutId = setTimeout(() => {
                fetch('module_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_order&id=${moduleId}&ordre=${newOrder}`
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
