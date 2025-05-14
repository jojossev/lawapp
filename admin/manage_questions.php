<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/admin_includes/admin_functions.php';

// Récupérer l'ID du quiz
$quiz_id = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);

if (!$quiz_id) {
    $_SESSION['error_message'] = "ID de quiz invalide.";
    redirect('manage_cours.php');
}

// Récupérer les informations du quiz et sa hiérarchie
try {
    $sql = "SELECT q.*, l.titre as lecon_titre, l.id_module, m.titre as module_titre, m.id_cours, c.titre as cours_titre
            FROM quiz q
            JOIN lecons l ON q.id_lecon = l.id
            JOIN modules m ON l.id_module = m.id
            JOIN cours c ON m.id_cours = c.id
            WHERE q.id = :quiz_id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
    $stmt->execute();
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        $_SESSION['error_message'] = "Quiz non trouvé.";
        redirect('manage_cours.php');
    }

    // Récupérer les questions du quiz
    $sql_questions = "SELECT id, question, type, points, ordre
                     FROM quiz_questions
                     WHERE id_quiz = :quiz_id
                     ORDER BY ordre ASC";
    $stmt_questions = $pdo->prepare($sql_questions);
    $stmt_questions->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
    $stmt_questions->execute();
    $questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des questions : " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors du chargement des questions.";
    redirect('manage_quiz.php?lecon_id=' . ($quiz['id_lecon'] ?? 0));
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
            <li class="breadcrumb-item">
                <a href="manage_cours_contenu.php?cours_id=<?php echo $quiz['id_cours']; ?>">
                    <?php echo htmlspecialchars($quiz['cours_titre']); ?>
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="manage_module_contenu.php?module_id=<?php echo $quiz['id_module']; ?>">
                    <?php echo htmlspecialchars($quiz['module_titre']); ?>
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="manage_quiz.php?lecon_id=<?php echo $quiz['id_lecon']; ?>">
                    Quiz de <?php echo htmlspecialchars($quiz['lecon_titre']); ?>
                </a>
            </li>
            <li class="breadcrumb-item active">Questions de <?php echo htmlspecialchars($quiz['titre']); ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Questions du Quiz : <?php echo htmlspecialchars($quiz['titre']); ?></h1>
        <a href="question_form.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter une Question
        </a>
    </div>

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

    <?php if (empty($questions)): ?>
        <div class="alert alert-info" role="alert">
            Aucune question n'a encore été créée pour ce quiz.
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($questions as $question): ?>
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <h5 class="mb-1">
                            <?php echo htmlspecialchars($question['question']); ?>
                            <span class="badge bg-secondary ms-2"><?php echo $question['points']; ?> pts</span>
                            <span class="badge bg-info ms-2"><?php echo ucfirst($question['type']); ?></span>
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="question_form.php?id=<?php echo $question['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="manage_reponses.php?question_id=<?php echo $question['id']; ?>" 
                               class="btn btn-sm btn-outline-info">
                                <i class="fas fa-list"></i> Réponses
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="confirmDelete(<?php echo $question['id']; ?>)">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(questionId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette question ? Cette action est irréversible.')) {
        window.location.href = `question_actions.php?action=delete&id=${questionId}&quiz_id=<?php echo $quiz_id; ?>`;
    }
}
</script>

<?php include 'admin_footer.php'; ?>
