<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php';

// 1. Récupérer et valider l\'ID du quiz depuis l\'URL
$quiz_id = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);

if (!$quiz_id) {
    $_SESSION['error_message'] = "ID de quiz invalide.";
    redirect('manage_cours.php'); // Redirection générique
}

// 2. Récupérer les informations du quiz parent (et leçon/module/cours pour le fil d\'Ariane)
try {
    $sql_quiz_info = "SELECT
                        q.id AS quiz_id, q.titre AS quiz_titre, q.id_lecon,
                        l.titre AS lecon_titre, l.id_module,
                        m.titre AS module_titre, m.id_cours,
                        c.titre AS cours_titre
                      FROM quiz q
                      JOIN lecons l ON q.id_lecon = l.id
                      JOIN modules m ON l.id_module = m.id
                      JOIN cours c ON m.id_cours = c.id
                      WHERE q.id = :quiz_id";
    $stmt_quiz_info = $pdo->prepare($sql_quiz_info);
    $stmt_quiz_info->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
    $stmt_quiz_info->execute();
    $quizInfo = $stmt_quiz_info->fetch(PDO::FETCH_ASSOC);

    if (!$quizInfo) {
        $_SESSION['error_message'] = "Quiz non trouvé.";
        redirect('manage_cours.php'); // Fallback
    }
    $pageTitle = 'Gestion Questions: ' . htmlspecialchars($quizInfo['quiz_titre']);
    $lecon_id = $quizInfo['id_lecon'];
    $module_id = $quizInfo['id_module'];
    $cours_id = $quizInfo['id_cours'];

} catch (PDOException $e) {
    error_log("Erreur récupération quiz parent: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors du chargement des informations du quiz.";
    redirect('manage_cours.php');
}


// 3. Récupérer les questions de ce quiz depuis la table 'questions_quiz'
$questionsList = [];
try {
    // Utilisation des noms de colonnes fournis: id, id_quiz, texte_question, type_question, points, ordre
    $sql_questions = "SELECT id, texte_question, type_question, points, ordre
                      FROM questions_quiz
                      WHERE id_quiz = :quiz_id
                      ORDER BY ordre ASC, id ASC"; // Tri par ordre, puis par ID
    $stmt_questions = $pdo->prepare($sql_questions);
    $stmt_questions->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
    $stmt_questions->execute();
    $questionsList = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
     error_log("Erreur récupération questions quiz: " . $e->getMessage());
     if ($e->getCode() === '42S02' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1146)) {
         $_SESSION['info_message'] = "La fonctionnalité Questions de Quiz est en cours de développement (table 'questions_quiz' manquante ou problème).";
     } else {
        $_SESSION['error_message'] = "Erreur lors du chargement des questions pour ce quiz.";
     }
    $questionsList = [];
}


$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
$infoMessage = $_SESSION['info_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['info_message']);

include 'admin_header.php';
?>

<div class="container mt-4">
    <!-- Fil d\'Ariane -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_dashboard.php">Admin</a></li>
            <li class="breadcrumb-item"><a href="manage_cours.php">Cours</a></li>
            <li class="breadcrumb-item"><a href="manage_cours_contenu.php?cours_id=<?php echo $cours_id; ?>"><?php echo htmlspecialchars($quizInfo['cours_titre']); ?></a></li>
            <li class="breadcrumb-item"><a href="manage_module_contenu.php?module_id=<?php echo $module_id; ?>"><?php echo htmlspecialchars($quizInfo['module_titre']); ?></a></li>
             <li class="breadcrumb-item"><a href="manage_lecon_quiz.php?lecon_id=<?php echo $lecon_id; ?>"><?php echo htmlspecialchars($quizInfo['lecon_titre']); ?> (Quiz)</a></li>
             <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($quizInfo['quiz_titre']); ?> (Questions)</li>
        </ol>
    </nav>

    <h1 class="mb-4"><?php echo $pageTitle; ?></h1>

    <?php if ($successMessage): ?> <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div> <?php endif; ?>
    <?php if ($errorMessage): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div> <?php endif; ?>
     <?php if ($infoMessage): ?> <div class="alert alert-info"><?php echo htmlspecialchars($infoMessage); ?></div> <?php endif; ?>


    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Questions du Quiz</h2>
             <!-- Lien vers le formulaire d\'ajout de question -->
            <a href="question_form.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Ajouter une Question
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($questionsList)): ?>
                <p class="text-center">Aucune question n\'a encore été ajoutée à ce quiz.</p>
            <?php else: ?>
                <table class="table table-striped table-hover table-bordered">
                     <thead class=\"table-dark\">
                        <tr>
                            <th>ID</th>
                            <th>Ordre</th>
                            <th>Texte de la Question</th>
                            <th>Type</th>
                            <th>Points</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questionsList as $question): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($question['id']); ?></td>
                                <td><?php echo htmlspecialchars($question['ordre']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($question['texte_question'])); ?></td>
                                <td><?php echo htmlspecialchars(strtoupper($question['type_question'])); ?></td>
                                <td><?php echo htmlspecialchars($question['points']); ?></td>
                                <td class="text-center">
                                    <!-- Lien vers la gestion des réponses (à créer plus tard) -->
                                     <a href="manage_question_reponses.php?question_id=<?php echo $question['id']; ?>" class=\"btn btn-sm btn-info me-1\" title=\"Gérer les Réponses\">
                                         <i class=\"fas fa-list-ul\"></i> Réponses
                                     </a>
                                     <!-- Lien vers le formulaire de modification de question -->
                                     <a href="question_form.php?id=<?php echo $question['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" class=\"btn btn-sm btn-warning me-1\" title=\"Modifier la Question\">
                                        <i class=\"fas fa-edit\"></i>
                                    </a>
                                     <!-- Formulaire pour la suppression de question -->
                                    <form action=\"question_actions.php\" method=\"POST\" class=\"d-inline\" onsubmit=\"return confirm('Êtes-vous sûr de vouloir supprimer cette question ?');\">
                                        <input type=\"hidden\" name=\"action\" value=\"delete\">
                                        <input type=\"hidden\" name=\"id\" value=\"<?php echo $question['id']; ?>\">
                                         <input type=\"hidden\" name=\"quiz_id\" value=\"<?php echo $quiz_id; ?>\"> <!-- Pour redirection -->
                                        <button type=\"submit\" class=\"btn btn-sm btn-danger\" title=\"Supprimer la Question\">
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
