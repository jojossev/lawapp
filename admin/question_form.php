<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php';

$quiz_id = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);
$question_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Pour l\'édition

if (!$quiz_id) {
    $_SESSION['error_message'] = "ID de quiz manquant pour ajouter/modifier une question.";
    redirect('manage_cours.php'); // Redirection générique
}

// Récupérer les infos du quiz parent pour le titre et le fil d\'Ariane
try {
    $sql_quiz_parent_info = "SELECT q.titre AS quiz_titre, q.id_lecon, l.titre AS lecon_titre, l.id_module, m.titre AS module_titre, m.id_cours, c.titre AS cours_titre
                             FROM quiz q
                             JOIN lecons l ON q.id_lecon = l.id
                             JOIN modules m ON l.id_module = m.id
                             JOIN cours c ON m.id_cours = c.id
                             WHERE q.id = :quiz_id";
    $stmt_quiz_parent_info = $pdo->prepare($sql_quiz_parent_info);
    $stmt_quiz_parent_info->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
    $stmt_quiz_parent_info->execute();
    $quizParentInfo = $stmt_quiz_parent_info->fetch(PDO::FETCH_ASSOC);

    if (!$quizParentInfo) {
        $_SESSION['error_message'] = "Quiz parent non trouvé.";
        redirect('manage_lecon_quiz.php?lecon_id=' . ($quizParentInfo['id_lecon'] ?? 0) );
    }
} catch (PDOException $e) {
    error_log("Erreur récupération quiz parent pour form: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la récupération des infos du quiz parent.";
    redirect('manage_lecon_quiz.php?lecon_id=' . ($quizParentInfo['id_lecon'] ?? 0));
}


$questionData = [
    'id' => null,
    'texte_question' => '',
    'type_question' => 'qcm', // Valeur par défaut
    'points' => 1,        // Valeur par défaut
    'ordre' => 0,         // Valeur par défaut
    'id_quiz' => $quiz_id // Pré-rempli
];
$pageTitle = 'Ajouter une Question au Quiz: ' . htmlspecialchars($quizParentInfo['quiz_titre']);
$action = 'add';

if ($question_id) {
    $action = 'edit';
    $pageTitle = 'Modifier la Question pour le Quiz: ' . htmlspecialchars($quizParentInfo['quiz_titre']);
    try {
        // Utilise les noms de colonnes de la table questions_quiz
        $sql_question = "SELECT id, id_quiz, texte_question, type_question, points, ordre
                         FROM questions_quiz
                         WHERE id = :question_id AND id_quiz = :quiz_id";
        $stmt_question = $pdo->prepare($sql_question);
        $stmt_question->bindParam(':question_id', $question_id, PDO::PARAM_INT);
        $stmt_question->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT); // Assurer la cohérence
        $stmt_question->execute();
        $fetched_question = $stmt_question->fetch(PDO::FETCH_ASSOC);

        if ($fetched_question) {
            $questionData = $fetched_question;
        } else {
            $_SESSION['error_message'] = "Question non trouvée ou n\'appartient pas à ce quiz.";
            redirect("manage_quiz_questions.php?quiz_id=$quiz_id");
        }
    } catch (PDOException $e) {
        error_log("Erreur récupération question: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors du chargement de la question.";
        redirect("manage_quiz_questions.php?quiz_id=$quiz_id");
    }
}

$valid_question_types = ['qcm', 'vrai_faux', 'reponse_courte']; // Types de questions autorisés

// Récupérer les erreurs de validation depuis la session
$errors = $_SESSION['form_errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

// Si old_input existe (après échec de validation), l\'utiliser pour pré-remplir
if (!empty($old_input)) {
    $questionData['texte_question'] = $old_input['texte_question'] ?? $questionData['texte_question'];
    $questionData['type_question'] = $old_input['type_question'] ?? $questionData['type_question'];
    $questionData['points'] = $old_input['points'] ?? $questionData['points'];
    $questionData['ordre'] = $old_input['ordre'] ?? $questionData['ordre'];
}

include 'admin_header.php';
?>

<div class="container mt-4">
    <!-- Fil d\'Ariane -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_dashboard.php">Admin</a></li>
            <li class="breadcrumb-item"><a href="manage_cours.php">Cours</a></li>
            <li class="breadcrumb-item"><a href="manage_cours_contenu.php?cours_id=<?php echo $quizParentInfo['id_cours']; ?>"><?php echo htmlspecialchars($quizParentInfo['cours_titre']); ?></a></li>
            <li class="breadcrumb-item"><a href="manage_module_contenu.php?module_id=<?php echo $quizParentInfo['id_module']; ?>"><?php echo htmlspecialchars($quizParentInfo['module_titre']); ?></a></li>
            <li class="breadcrumb-item"><a href="manage_lecon_quiz.php?lecon_id=<?php echo $quizParentInfo['id_lecon']; ?>"><?php echo htmlspecialchars($quizParentInfo['quiz_titre']); ?> (Quiz)</a></li>
            <li class="breadcrumb-item"><a href="manage_quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>">Questions</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $action === 'add' ? 'Ajouter' : 'Modifier'; ?> Question</li>
        </ol>
    </nav>

    <h1 class="mb-4"><?php echo $pageTitle; ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Erreur(s) :</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="question_actions.php" method="POST">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <input type="hidden" name="id_quiz" value="<?php echo htmlspecialchars($quiz_id); ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($questionData['id']); ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="texte_question" class="form-label">Texte de la Question <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="texte_question" name="texte_question" rows="4" required><?php echo htmlspecialchars($questionData['texte_question']); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="type_question" class="form-label">Type de Question <span class="text-danger">*</span></label>
                        <select class="form-select" id="type_question" name="type_question" required>
                            <?php foreach ($valid_question_types as $type): ?>
                                <option value="<?php echo $type; ?>" <?php echo ($questionData['type_question'] === $type) ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(str_replace('_', ' ', $type)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="points" class="form-label">Points <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="points" name="points" value="<?php echo htmlspecialchars((string)$questionData['points']); ?>" min="0" step="1" required>
                        <small class="form-text text-muted">Nombre de points pour cette question.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ordre" class="form-label">Ordre d\'affichage <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="ordre" name="ordre" value="<?php echo htmlspecialchars((string)$questionData['ordre']); ?>" min="0" step="1" required>
                        <small class="form-text text-muted">Les questions seront triées par ce numéro.</small>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="manage_quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour aux Questions
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $action === 'add' ? 'Ajouter la Question' : 'Mettre à Jour'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
