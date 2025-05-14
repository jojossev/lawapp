<?php
session_start();
require_once __DIR__ . '/../includes/config.php'; // Inclut db_connect et session_start
require_once __DIR__ . '/admin_auth_check.php'; // Vérif auth admin
require_once __DIR__ . '/includes/admin_functions.php'; // Fonctions utilitaires admin

// 1. Récupérer et valider l'ID du cours depuis l'URL
$cours_id = filter_input(INPUT_GET, 'cours_id', FILTER_VALIDATE_INT);

if (!$cours_id) {
    $_SESSION['error_message'] = "ID de cours invalide.";
    redirect('manage_cours.php');
}

// 2. Récupérer les informations de base du cours
try {
    $sql_cours = "SELECT titre FROM cours WHERE id = :cours_id";
    $stmt_cours = $pdo->prepare($sql_cours);
    $stmt_cours->bindParam(':cours_id', $cours_id, PDO::PARAM_INT);
    $stmt_cours->execute();
    $cours = $stmt_cours->fetch(PDO::FETCH_ASSOC);

    if (!$cours) {
        $_SESSION['error_message'] = "Cours non trouvé.";
        redirect('manage_cours.php');
    }
    $pageTitle = 'Gestion du Contenu : ' . htmlspecialchars($cours['titre']);

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du cours : " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors du chargement des informations du cours.";
    redirect('manage_cours.php');
}

// 3. Récupérer les modules de ce cours
try {
    $sql_modules = "SELECT id, titre, ordre_affichage, description, statut FROM modules WHERE id_cours = :cours_id ORDER BY ordre_affichage ASC, date_creation ASC";
    $stmt_modules = $pdo->prepare($sql_modules);
    $stmt_modules->bindParam(':cours_id', $cours_id, PDO::PARAM_INT);
    $stmt_modules->execute();
    $modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des modules : " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors du chargement des modules.";
    $modules = []; // Assure que $modules est un tableau même en cas d'erreur
    // Pas de redirection ici, on affiche la page avec le message d'erreur
}

// 4. Récupérer les leçons de chaque module (sera fait plus tard)
$lecons_par_module = []; // Placeholder

$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);


include 'admin_header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard Admin</a></li>
            <li class="breadcrumb-item"><a href="manage_cours.php">Gestion des Cours</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($cours['titre']); ?></li>
        </ol>
    </nav>

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

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Modules</h2>
            <a href="module_form.php?cours_id=<?php echo $cours_id; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Ajouter un Module
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($modules)):
                // Correction : Affichage du message d'erreur s'il existe
                if ($errorMessage && strpos($errorMessage, 'chargement des modules') !== false) {
                    echo '<div class="alert alert-warning">' . htmlspecialchars($errorMessage) . '</div>';
                    // Réinitialiser le message pour ne pas l'afficher deux fois
                    unset($_SESSION['error_message']); 
                    $errorMessage = null;
                } else {
                    echo '<p class="text-center">Aucun module n\'a encore été ajouté à ce cours.</p>';
                }
             ?>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Ordre</th>
                                <th>Titre du Module</th>
                                <th style="width: 250px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $module): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($module['ordre_affichage'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($module['titre']); ?></td>
                                    <td>
                                        <a href="manage_module_contenu.php?module_id=<?php echo $module['id']; ?>" class="btn btn-info btn-sm me-1 mb-1" title="Gérer les Leçons">
                                            <i class="fas fa-list-ul"></i> Leçons
                                        </a>
                                        <a href="module_form.php?id=<?php echo $module['id']; ?>&cours_id=<?php echo $cours_id; ?>" class="btn btn-primary btn-sm me-1 mb-1" title="Modifier le module">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <form action="module_actions.php" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce module et toutes ses leçons ?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $module['id']; ?>">
                                            <input type="hidden" name="cours_id" value="<?php echo $cours_id; ?>"> <!-- Pour redirection -->
                                            <button type="submit" class="btn btn-danger btn-sm mb-1" title="Supprimer le module">
                                                <i class="fas fa-trash-alt"></i> Suppr.
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- D'autres sections pour Quiz Globaux, etc. pourraient être ajoutées ici -->

</div>

<?php include 'admin_footer.php'; ?>
