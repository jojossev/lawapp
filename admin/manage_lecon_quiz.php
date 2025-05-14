<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php';

// 1. Récupérer et valider l'ID de la leçon depuis l'URL
$lecon_id = filter_input(INPUT_GET, 'lecon_id', FILTER_VALIDATE_INT);

if (!$lecon_id) {
    $_SESSION['error_message'] = "ID de leçon invalide.";
    redirect('manage_cours.php'); // Redirection générique si ID leçon manque
}

// 2. Récupérer les informations de la leçon, du module parent et du cours parent
try {
    $sql_lecon = "SELECT 
                        l.id AS lecon_id, l.titre AS lecon_titre, l.id_module,
                        m.titre AS module_titre, m.id_cours,
                        c.titre AS cours_titre
                   FROM lecons l
                   JOIN modules m ON l.id_module = m.id
                   JOIN cours c ON m.id_cours = c.id
                   WHERE l.id = :lecon_id";
    $stmt_lecon = $pdo->prepare($sql_lecon);
    $stmt_lecon->bindParam(':lecon_id', $lecon_id, PDO::PARAM_INT);
    $stmt_lecon->execute();
    $leconInfo = $stmt_lecon->fetch(PDO::FETCH_ASSOC);

    if (!$leconInfo) {
        $_SESSION['error_message'] = "Leçon non trouvée.";
        redirect('manage_cours.php'); // Redirection générique
    }
    $pageTitle = 'Gestion Quiz: ' . htmlspecialchars($leconInfo['lecon_titre']);
    $module_id = $leconInfo['id_module'];
    $cours_id = $leconInfo['id_cours'];

} catch (PDOException $e) {
    error_log("Erreur récupération leçon/module/cours parent: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors du chargement des informations de la leçon.";
    redirect('manage_cours.php');
}

// 3. Récupérer les quiz de cette leçon (Table: quiz, FK: id_lecon)
$quizList = []; // Placeholder pour l'instant
try {
    // Supposons une table 'quiz' avec une colonne 'id_lecon'
    $sql_quiz = "SELECT id, titre, description, statut, type, ordre 
                 FROM quiz 
                 WHERE id_lecon = :lecon_id 
                 ORDER BY ordre ASC, date_creation ASC"; // Tri par ordre, puis par date
    $stmt_quiz = $pdo->prepare($sql_quiz);
    $stmt_quiz->bindParam(':lecon_id', $lecon_id, PDO::PARAM_INT);
    $stmt_quiz->execute();
    $quizList = $stmt_quiz->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
     // Si la table quiz n'existe pas encore, cela va échouer. Log l'erreur mais continuer.
    error_log("Erreur récupération quiz: " . $e->getMessage());
     if ($e->getCode() === '42S02') { // Base table or view not found
         $_SESSION['info_message'] = "La fonctionnalité Quiz est en cours de développement (table 'quiz' manquante).";
     } else {
        $_SESSION['error_message'] = "Erreur lors du chargement des quiz pour cette leçon.";
     }
    $quizList = [];
}


$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
$infoMessage = $_SESSION['info_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['info_message']);

include 'admin_header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard Admin</a></li>
            <li class="breadcrumb-item"><a href="manage_cours.php">Gestion des Cours</a></li>
            <li class="breadcrumb-item"><a href="manage_cours_contenu.php?cours_id=<?php echo $cours_id; ?>"><?php echo htmlspecialchars($leconInfo['cours_titre']); ?></a></li>
            <li class="breadcrumb-item"><a href="manage_module_contenu.php?module_id=<?php echo $module_id; ?>"><?php echo htmlspecialchars($leconInfo['module_titre']); ?></a></li>
             <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($leconInfo['lecon_titre']); ?> (Quiz)</li>
        </ol>
    </nav>

    <h1 class="mb-4"><?php echo $pageTitle; ?></h1>

     <?php displayMessages($successMessage, $errorMessage, $infoMessage); // Utiliser une fonction helper si possible ?>
    <?php if ($successMessage): ?> <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div> <?php endif; ?>
    <?php if ($errorMessage): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div> <?php endif; ?>
     <?php if ($infoMessage): ?> <div class="alert alert-info"><?php echo htmlspecialchars($infoMessage); ?></div> <?php endif; ?>


    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Quiz associés à la leçon</h2>
            <a href="quiz_form.php?lecon_id=<?php echo $lecon_id; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Ajouter un Quiz
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($quizList)): ?>
                <p class="text-center">Aucun quiz n'a encore été ajouté à cette leçon.</p>
            <?php else: ?>
                <table class="table table-striped table-hover table-bordered">
                     <thead class=\"table-dark\">
                        <tr>
                            <th>ID</th>
                            <th>Ordre</th>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quizList as $quiz): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($quiz['id']); ?></td>
                                <td><?php echo htmlspecialchars($quiz['ordre']); ?></td>
                                <td><?php echo htmlspecialchars($quiz['titre']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($quiz['type'] ?? 'N/A')); ?></td>
                                 <td>
                                     <span class="badge bg-<?php echo $quiz['statut'] === 'publie' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($quiz['statut'])); ?>
                                    </span>
                                     <!-- Lien vers la gestion des questions du quiz -->
                                     <a href="manage_quiz_questions.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-info me-1" title="Gérer les Questions">
                                         <i class="fas fa-question-circle"></i> Questions
                                     </a>
                                     <a href="quiz_form.php?id=<?php echo $quiz['id']; ?>&lecon_id=<?php echo $lecon_id; ?>" class=\"btn btn-sm btn-warning me-1\" title=\"Modifier le Quiz\">
                                        <i class=\"fas fa-edit\"></i>
                                    </a>
                                    <form action=\"quiz_actions.php\" method=\"POST\" class=\"d-inline\" onsubmit=\"return confirm('Êtes-vous sûr de vouloir supprimer ce quiz ?');\">
                                        <input type=\"hidden\" name=\"action\" value=\"delete\">
                                        <input type=\"hidden\" name=\"id\" value=\"<?php echo $quiz['id']; ?>\">
                                         <input type=\"hidden\" name=\"lecon_id\" value=\"<?php echo $lecon_id; ?>\"> <!-- Pour redirection -->
                                        <button type=\"submit\" class=\"btn btn-sm btn-danger\" title=\"Supprimer le Quiz\">
                                            <i class=\"fas fa-trash-alt\"></i>
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
</div>

<?php include 'admin_footer.php'; ?>
