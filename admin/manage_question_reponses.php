<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php';

$question_id = filter_input(INPUT_GET, 'question_id', FILTER_VALIDATE_INT);

if (!$question_id) {
    $_SESSION['error_message'] = "ID de question invalide.";
    redirect('manage_cours.php'); // Redirection générique
}

// Récupérer les infos de la question parente (et quiz/leçon/module/cours pour le fil d\'Ariane)
try {
    $sql_question_info = "SELECT
                            qq.id AS question_id, qq.texte_question, qq.id_quiz,
                            q.titre AS quiz_titre, q.id_lecon,
                            l.titre AS lecon_titre, l.id_module,
                            m.titre AS module_titre, m.id_cours,
                            c.titre AS cours_titre
                          FROM questions_quiz qq
                          JOIN quiz q ON qq.id_quiz = q.id
                          JOIN lecons l ON q.id_lecon = l.id
                          JOIN modules m ON l.id_module = m.id
                          JOIN cours c ON m.id_cours = c.id
                          WHERE qq.id = :question_id";
    $stmt_question_info = $pdo->prepare($sql_question_info);
    $stmt_question_info->bindParam(':question_id', $question_id, PDO::PARAM_INT);
    $stmt_question_info->execute();
    $questionInfo = $stmt_question_info->fetch(PDO::FETCH_ASSOC);

    if (!$questionInfo) {
        $_SESSION['error_message'] = "Question non trouvée.";
        redirect('manage_cours.php');
    }
    $pageTitle = 'Réponses: ' . substr(htmlspecialchars($questionInfo['texte_question']), 0, 50) . '...';
    $quiz_id = $questionInfo['id_quiz'];
    $lecon_id = $questionInfo['id_lecon'];
    $module_id = $questionInfo['id_module'];
    $cours_id = $questionInfo['id_cours'];

} catch (PDOException $e) {
    error_log("Erreur récupération question parente: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors du chargement des informations de la question.";
    redirect('manage_quiz_questions.php?quiz_id=' . ($questionInfo['id_quiz'] ?? 0));
}

// Récupérer les réponses pour cette question depuis la table 'quiz_reponses'
$reponsesList = [];
try {
    // Utilisation des noms de colonnes fournis
    $sql_reponses = "SELECT id, texte_reponse, est_correcte, ordre, feedback_specifique
                     FROM quiz_reponses
                     WHERE id_question = :question_id
                     ORDER BY ordre ASC, id ASC";
    $stmt_reponses = $pdo->prepare($sql_reponses);
    $stmt_reponses->bindParam(':question_id', $question_id, PDO::PARAM_INT);
    $stmt_reponses->execute();
    $reponsesList = $stmt_reponses->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur récupération réponses: " . $e->getMessage());
    if ($e->getCode() === '42S02' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1146)) {
         $_SESSION['info_message'] = "La fonctionnalité Réponses est en cours de développement (table 'quiz_reponses' manquante ou problème).";
     } else {
        $_SESSION['error_message'] = "Erreur lors du chargement des réponses pour cette question.";
     }
    $reponsesList = [];
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
            <li class="breadcrumb-item"><a href="manage_cours_contenu.php?cours_id=<?php echo $cours_id; ?>"><?php echo htmlspecialchars($questionInfo['cours_titre']); ?></a></li>
            <li class="breadcrumb-item"><a href="manage_module_contenu.php?module_id=<?php echo $module_id; ?>"><?php echo htmlspecialchars($questionInfo['module_titre']); ?></a></li>
            <li class="breadcrumb-item"><a href="manage_lecon_quiz.php?lecon_id=<?php echo $lecon_id; ?>"><?php echo htmlspecialchars($questionInfo['quiz_titre']); ?> (Quiz)</a></li>
            <li class="breadcrumb-item"><a href="manage_quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>">Questions</a></li>
            <li class="breadcrumb-item active" aria-current="page">Réponses pour "<?php echo substr(htmlspecialchars($questionInfo['texte_question']), 0, 30); ?>..."</li>
        </ol>
    </nav>

    <h1 class="mb-3">Gestion des Réponses</h1>
    <h5 class="text-muted mb-4">Question: <?php echo nl2br(htmlspecialchars($questionInfo['texte_question'])); ?></h5>


    <?php if ($successMessage): ?> <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div> <?php endif; ?>
    <?php if ($errorMessage): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div> <?php endif; ?>
    <?php if ($infoMessage): ?> <div class="alert alert-info"><?php echo htmlspecialchars($infoMessage); ?></div> <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Options de Réponse</h2>
            <a href="reponse_form.php?question_id=<?php echo $question_id; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Ajouter une Réponse
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($reponsesList)): ?>
                <p class="text-center">Aucune réponse n\'a encore été ajoutée à cette question.</p>
            <?php else: ?>
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Ordre</th>
                            <th>Texte de la Réponse</th>
                            <th class="text-center">Correcte ?</th>
                            <th>Feedback Spécifique</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reponsesList as $reponse): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reponse['id']); ?></td>
                                <td><?php echo htmlspecialchars($reponse['ordre']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($reponse['texte_reponse'])); ?></td>
                                <td class="text-center">
                                    <?php if ($reponse['est_correcte']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Oui</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-times"></i> Non</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($reponse['feedback_specifique'] ?? '')); ?></td>
                                <td class="text-center">
                                    <a href="reponse_form.php?id=<?php echo $reponse['id']; ?>&question_id=<?php echo $question_id; ?>" class="btn btn-sm btn-warning me-1" title="Modifier la Réponse">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="reponse_actions.php" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette réponse ?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $reponse['id']; ?>">
                                        <input type="hidden" name="question_id" value="<?php echo $question_id; ?>"> <!-- Pour redirection -->
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer la Réponse">
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

     <div class="mt-4">
        <a href="manage_quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour aux questions du quiz
        </a>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
