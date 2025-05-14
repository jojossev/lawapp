<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/admin_includes/admin_functions.php';

// 1. Récupérer et valider l'ID du module depuis l'URL
$module_id = filter_input(INPUT_GET, 'module_id', FILTER_VALIDATE_INT);

if (!$module_id) {
    $_SESSION['error_message'] = "ID de module invalide.";
    redirect('manage_cours.php'); // Redirection générique si ID module manque
}

// 2. Récupérer les informations du module et de son cours parent
try {
    $sql_module = "SELECT m.id AS module_id, m.titre AS module_titre, m.id_cours, c.titre AS cours_titre
                   FROM modules m
                   JOIN cours c ON m.id_cours = c.id
                   WHERE m.id = :module_id";
    $stmt_module = $pdo->prepare($sql_module);
    $stmt_module->bindParam(':module_id', $module_id, PDO::PARAM_INT);
    $stmt_module->execute();
    $moduleInfo = $stmt_module->fetch(PDO::FETCH_ASSOC);

    if (!$moduleInfo) {
        $_SESSION['error_message'] = "Module non trouvé.";
        redirect('manage_cours.php'); // Redirection générique
    }
    $pageTitle = 'Gestion Leçons: ' . htmlspecialchars($moduleInfo['module_titre']);
    $cours_id = $moduleInfo['id_cours']; // Récupérer l'ID du cours pour les liens retour

} catch (PDOException $e) {
    error_log("Erreur récupération module/cours parent: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors du chargement des informations du module.";
    redirect('manage_cours.php');
}

// 3. Récupérer les leçons de ce module
try {
    // Vérifier si la table lecons existe
    $sql_check = "SHOW TABLES LIKE 'lecons'";
    $result = $pdo->query($sql_check);
    if ($result->rowCount() == 0) {
        throw new Exception("La table 'lecons' n'existe pas.");
    }

    $sql_lecons = "SELECT id, titre, type_contenu, ordre, statut 
                   FROM lecons 
                   WHERE id_module = :module_id 
                   ORDER BY ordre ASC, date_creation ASC";
    $stmt_lecons = $pdo->prepare($sql_lecons);
    $stmt_lecons->bindParam(':module_id', $module_id, PDO::PARAM_INT);
    $stmt_lecons->execute();
    $lecons = $stmt_lecons->fetchAll(PDO::FETCH_ASSOC);

    // Debug
    error_log("Module ID: " . $module_id);
    error_log("Nombre de leçons trouvées: " . count($lecons));

} catch (PDOException $e) {
    error_log("Erreur SQL lors du chargement des leçons: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur SQL lors du chargement des leçons: " . $e->getMessage();
    $lecons = [];
} catch (Exception $e) {
    error_log("Erreur lors du chargement des leçons: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
    $lecons = [];
}

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
            <li class="breadcrumb-item"><a href="manage_cours_contenu.php?cours_id=<?php echo $cours_id; ?>"><?php echo htmlspecialchars($moduleInfo['cours_titre']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($moduleInfo['module_titre']); ?> (Leçons)</li>
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


    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Leçons du Module</h2>
             <a href="lecon_form.php?module_id=<?php echo $module_id; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Ajouter une Leçon
            </a>
        </div>
        <div class="card-body">
            <!-- Affichage de la liste des leçons ici -->
            <?php if (empty($lecons)): ?>
                <p class="text-center">Aucune leçon n'a encore été ajoutée à ce module.</p>
            <?php else: ?>
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Ordre</th>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th class="text-center" style="width: 250px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lecons as $lecon): ?>
                            <tr>
                                <td><?php echo (int)$lecon['ordre']; ?></td>
                                <td><?php echo htmlspecialchars($lecon['titre']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($lecon['type_contenu'])); ?></td>
                                <td>
                                     <span class="badge bg-<?php echo $lecon['statut'] === 'publie' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($lecon['statut'])); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="lecon_form.php?id=<?php echo $lecon['id']; ?>&module_id=<?php echo $module_id; ?>" class="btn btn-sm btn-warning me-1" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="manage_lecon_quiz.php?lecon_id=<?php echo $lecon['id']; ?>" class="btn btn-sm btn-info me-1" title="Gérer les Quiz de cette leçon">
                                        <i class="fas fa-question-circle"></i> Quiz
                                    </a>
                                    <!-- Formulaire pour la suppression -->
                                    <form action="lecon_actions.php" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette leçon ? Cette action est irréversible.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $lecon['id']; ?>">
                                        <input type="hidden" name="module_id" value="<?php echo $module_id; ?>"> <!-- Envoyer module_id pour redirection -->
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

     <!-- Section pour les Quiz liés directement au module (si nécessaire) -->
     <!--
     <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Quiz du Module</h2>
            <a href="quiz_form.php?element_id=<?php echo $module_id; ?>&element_type=module" class="btn btn-secondary btn-sm">
                <i class="fas fa-plus"></i> Ajouter un Quiz au Module
            </a>
        </div>
        <div class="card-body">
            <p>Liste des quiz spécifiques à ce module...</p>
        </div>
     </div>
     -->

</div>

<?php include 'admin_footer.php'; ?>
