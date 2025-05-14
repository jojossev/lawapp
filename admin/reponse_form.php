<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php';

$reponse_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$question_id = filter_input(INPUT_GET, 'question_id', FILTER_VALIDATE_INT);
$action = $reponse_id ? 'edit' : 'add';
$pageTitle = $reponse_id ? 'Modifier la Réponse' : 'Ajouter une Réponse';

if (!$question_id) {
    $_SESSION['error_message'] = "ID de question manquant pour gérer les réponses.";
    // Tenter une redirection intelligente si possible, sinon une redirection générale
    if ($reponse_id) { // Si on éditait une réponse, on pourrait avoir besoin de l\'id_question de la réponse elle-même
         // Logique à ajouter si nécessaire pour récupérer $question_id depuis $reponse_id si $question_id n\'est pas dans l\'URL.
    }
    redirect('manage_cours.php'); // Redirection par défaut
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
        $_SESSION['error_message'] = "Question parente non trouvée.";
        redirect('manage_cours.php');
    }
    $quiz_id_for_redirect = $questionInfo['id_quiz']; // Pour le bouton retour
    $lecon_id_for_ariane = $questionInfo['id_lecon'];
    $module_id_for_ariane = $questionInfo['id_module'];
    $cours_id_for_ariane = $questionInfo['id_cours'];

} catch (PDOException $e) {
    error_log("Erreur récupération question (form reponse): " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors du chargement des informations de la question.";
    redirect('manage_quiz_questions.php?quiz_id=' . ($questionInfo['id_quiz'] ?? 0));
}


// Si action 'edit', charger les données de la réponse
$current_reponse = [
    'texte_reponse' => '',
    'est_correcte' => 0,
    'ordre' => 0,
    'feedback_specifique' => ''
];

if ($action === 'edit' && $reponse_id) {
    try {
        $sql_reponse = "SELECT texte_reponse, est_correcte, ordre, feedback_specifique
                        FROM quiz_reponses
                        WHERE id = :reponse_id AND id_question = :question_id"; // Sécurité
        $stmt_reponse = $pdo->prepare($sql_reponse);
        $stmt_reponse->bindParam(':reponse_id', $reponse_id, PDO::PARAM_INT);
        $stmt_reponse->bindParam(':question_id', $question_id, PDO::PARAM_INT);
        $stmt_reponse->execute();
        $data = $stmt_reponse->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $current_reponse = $data;
        } else {
            $_SESSION['error_message'] = "Réponse non trouvée ou n\'appartient pas à cette question.";
            redirect("manage_question_reponses.php?question_id=$question_id");
        }
    } catch (PDOException $e) {
        error_log("Erreur récupération réponse (edit): " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors du chargement de la réponse.";
        redirect("manage_question_reponses.php?question_id=$question_id");
    }
}

// Gérer les erreurs de formulaire et les anciennes entrées
$form_errors = $_SESSION['form_errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

// Pré-remplir le formulaire avec les anciennes entrées ou les données actuelles
$texte_reponse_val = $old_input['texte_reponse'] ?? $current_reponse['texte_reponse'];
$est_correcte_val = $old_input['est_correcte'] ?? $current_reponse['est_correcte'];
$ordre_val = $old_input['ordre'] ?? $current_reponse['ordre'];
$feedback_val = $old_input['feedback_specifique'] ?? $current_reponse['feedback_specifique'];

include 'admin_header.php';
?>

<div class="container mt-4">
    <!-- Fil d\'Ariane -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_dashboard.php">Admin</a></li>
            <li class="breadcrumb-item"><a href="manage_cours.php">Cours</a></li>
            <li class="breadcrumb-item"><a href="manage_cours_contenu.php?cours_id=<?php echo $cours_id_for_ariane; ?>"><?php echo htmlspecialchars($questionInfo['cours_titre']); ?></a></li>
            <li class="breadcrumb-item"><a href="manage_module_contenu.php?module_id=<?php echo $module_id_for_ariane; ?>"><?php echo htmlspecialchars($questionInfo['module_titre']); ?></a></li>
            <li class="breadcrumb-item"><a href="manage_lecon_quiz.php?lecon_id=<?php echo $lecon_id_for_ariane; ?>"><?php echo htmlspecialchars($questionInfo['quiz_titre']); ?> (Quiz)</a></li>
            <li class="breadcrumb-item"><a href="manage_quiz_questions.php?quiz_id=<?php echo $quiz_id_for_redirect; ?>">Questions</a></li>
            <li class="breadcrumb-item"><a href="manage_question_reponses.php?question_id=<?php echo $question_id; ?>">Réponses</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $pageTitle; ?></li>
        </ol>
    </nav>

    <h1 class="mb-3"><?php echo $pageTitle; ?></h1>
    <h5 class="text-muted mb-4">Pour la question: <?php echo nl2br(htmlspecialchars($questionInfo['texte_question'])); ?></h5>


    <?php if (!empty($form_errors)):
    ?>
        <div class="alert alert-danger">
            <strong>Erreur(s) :</strong>
            <ul>
                <?php foreach ($form_errors as $error):
                ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php $errorMessage = $_SESSION['error_message'] ?? null; unset($_SESSION['error_message']); ?>
     <?php if ($errorMessage): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div> <?php endif; ?>


    <div class="card">
        <div class="card-body">
            <form action="reponse_actions.php" method="POST">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $reponse_id; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="texte_reponse" class="form-label">Texte de la réponse <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="texte_reponse" name="texte_reponse" rows="3" required><?php echo htmlspecialchars($texte_reponse_val); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="ordre" class="form-label">Ordre d\'affichage <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="ordre" name="ordre" value="<?php echo htmlspecialchars($ordre_val); ?>" min="0" required>
                    <small class="form-text text-muted">Détermine la position de cette réponse parmi les autres.</small>
                </div>

                <div class="mb-3 form-check">
                    <input type="hidden" name="est_correcte" value="0"> <!-- Valeur par défaut si la case n\'est pas cochée -->
                    <input type="checkbox" class="form-check-input" id="est_correcte" name="est_correcte" value="1" <?php if ($est_correcte_val == 1) echo 'checked'; ?>>
                    <label class="form-check-label" for="est_correcte">Est-ce la bonne réponse ?</label>
                </div>
                 <p class="form-text text-muted">
                    Pour une question à choix unique (QCM classique, Vrai/Faux), une seule réponse doit être marquée comme correcte.
                    Si vous marquez plusieurs réponses comme correctes, cela pourrait être interprété comme un QCM à réponses multiples par le système d\'évaluation.
                </p>


                <div class="mb-3">
                    <label for="feedback_specifique" class="form-label">Feedback spécifique (optionnel)</label>
                    <textarea class="form-control" id="feedback_specifique" name="feedback_specifique" rows="2"><?php echo htmlspecialchars($feedback_val); ?></textarea>
                    <small class="form-text text-muted">Sera affiché à l\'étudiant s\'il choisit cette réponse.</small>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $action === 'edit' ? 'Mettre à jour' : 'Ajouter'; ?> la réponse
                    </button>
                    <a href="manage_question_reponses.php?question_id=<?php echo $question_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
