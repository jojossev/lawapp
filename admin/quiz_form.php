<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php';

// 1. Récupérer et valider lecon_id (obligatoire) et id (optionnel pour édition)
$lecon_id = filter_input(INPUT_GET, 'lecon_id', FILTER_VALIDATE_INT);
$quiz_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$lecon_id) {
    $_SESSION['error_message'] = "ID de leçon manquant pour créer/modifier un quiz.";
    redirect('manage_cours.php'); // Fallback
}

// 2. Récupérer les infos de la leçon parente pour l'affichage et le contexte
try {
    $sql_lecon = "SELECT l.titre AS lecon_titre, l.id_module, m.id_cours
                   FROM lecons l
                   JOIN modules m ON l.id_module = m.id
                   WHERE l.id = :lecon_id";
    $stmt_lecon = $pdo->prepare($sql_lecon);
    $stmt_lecon->bindParam(':lecon_id', $lecon_id, PDO::PARAM_INT);
    $stmt_lecon->execute();
    $leconInfo = $stmt_lecon->fetch(PDO::FETCH_ASSOC);
    if (!$leconInfo) {
        throw new Exception("Leçon parente non trouvée.");
    }
} catch (Exception $e) {
    error_log("Erreur récupération leçon parente pour quiz_form: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors du chargement des informations de la leçon parente.";
    // Tenter de rediriger vers la page précédente (gestion quiz de la leçon)
    redirect("manage_lecon_quiz.php?lecon_id=" . $lecon_id);
}

// 3. Initialisation des variables du formulaire
$form_action = 'add';
$quiz = [
    'id' => '',
    'titre' => '',
    'description' => '',
    'type' => 'qcm', // Défaut
    'statut' => 'brouillon', // Défaut
    'ordre' => 0, // Défaut pour le nouvel ordre
    'id_lecon' => $lecon_id // Pré-rempli
];
$pageTitle = 'Ajouter un Quiz à la leçon : ' . htmlspecialchars($leconInfo['lecon_titre']);

// 4. Si quiz_id est présent (mode édition), récupérer les données du quiz
if ($quiz_id) {
    $form_action = 'edit';
    $pageTitle = 'Modifier le Quiz';
    try {
        // Supposons que la table 'quiz' existe
        $sql_quiz = "SELECT id, titre, description, type, statut, id_lecon, ordre FROM quiz WHERE id = :quiz_id";
        $stmt_quiz = $pdo->prepare($sql_quiz);
        $stmt_quiz->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
        $stmt_quiz->execute();
        $quizData = $stmt_quiz->fetch(PDO::FETCH_ASSOC);

        if ($quizData) {
            // Vérifier si le quiz appartient bien à la leçon attendue (sécurité)
            if ($quizData['id_lecon'] != $lecon_id) {
                 $_SESSION['error_message'] = "Erreur: Le quiz ne correspond pas à la leçon spécifiée.";
                 redirect("manage_lecon_quiz.php?lecon_id=" . $lecon_id);
            }
            $quiz = $quizData; // Remplacer les valeurs par défaut
            $pageTitle .= ' : ' . htmlspecialchars($quiz['titre']); // Ajouter titre quiz au titre page
        } else {
            $_SESSION['error_message'] = "Quiz non trouvé.";
            redirect("manage_lecon_quiz.php?lecon_id=" . $lecon_id);
        }
    } catch (PDOException $e) {
         error_log("Erreur récupération quiz pour édition: " . $e->getMessage());
        // Gérer le cas où la table n'existe pas encore
        if ($e->getCode() === '42S02') {
             $_SESSION['error_message'] = "La fonctionnalité Quiz n'est pas encore prête (table 'quiz' manquante).";
         } else {
            $_SESSION['error_message'] = "Erreur lors du chargement des données du quiz.";
         }
        redirect("manage_lecon_quiz.php?lecon_id=" . $lecon_id);
    }
}

// Définir les types de quiz possibles
$quiz_types = [
    'qcm' => 'Questionnaire à Choix Multiples (QCM)',
    'vrai_faux' => 'Vrai ou Faux',
    'reponse_courte' => 'Réponse Courte'
    // Ajouter d'autres types ici si nécessaire
];

// Définir les statuts possibles
$statuses = ['brouillon' => 'Brouillon', 'publie' => 'Publié'];

$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

include 'admin_header.php';
?>

<div class="container mt-4">
     <!-- Fil d'Ariane -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="admin_dashboard.php">Admin</a></li>
             <li class="breadcrumb-item"><a href="manage_cours.php">Cours</a></li>
             <li class="breadcrumb-item"><a href="manage_cours_contenu.php?cours_id=<?php echo $leconInfo['id_cours']; ?>">Contenu Cours</a></li>
             <li class="breadcrumb-item"><a href="manage_module_contenu.php?module_id=<?php echo $leconInfo['id_module']; ?>">Leçons Module</a></li>
             <li class="breadcrumb-item"><a href="manage_lecon_quiz.php?lecon_id=<?php echo $lecon_id; ?>">Quiz Leçon</a></li>
             <li class="breadcrumb-item active" aria-current="page"><?php echo $form_action === 'add' ? 'Ajouter' : 'Modifier'; ?> Quiz</li>
        </ol>
    </nav>

    <h1 class="mb-4"><?php echo $pageTitle; ?></h1>

    <?php if ($successMessage): ?> <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div> <?php endif; ?>
    <?php if ($errorMessage): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div> <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="quiz_actions.php" method="POST">
                <input type="hidden" name="action" value="<?php echo $form_action; ?>">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($quiz['id']); ?>">
                <input type="hidden" name="id_lecon" value="<?php echo htmlspecialchars($lecon_id); ?>">

                <div class="mb-3">
                    <label for="titre" class="form-label">Titre du Quiz <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titre" name="titre" value="<?php echo htmlspecialchars($quiz['titre']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($quiz['description']); ?></textarea>
                     <small class="form-text text-muted">Brève description du contenu ou objectif du quiz.</small>
                </div>

                <div class="row mb-3">
                     <div class="col-md-6">
                         <label for="type" class="form-label">Type de Quiz <span class="text-danger">*</span></label>
                         <select class="form-select" id="type" name="type" required>
                             <?php foreach ($quiz_types as $key => $value): ?>
                                 <option value="<?php echo $key; ?>" <?php echo ($quiz['type'] === $key) ? 'selected' : ''; ?>>
                                     <?php echo htmlspecialchars($value); ?>
                                 </option>
                             <?php endforeach; ?>
                         </select>
                          <small class="form-text text-muted">Détermine la structure des questions/réponses.</small>
                     </div>
                     <div class="col-md-6">
                         <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                         <select class="form-select" id="statut" name="statut" required>
                             <?php foreach ($statuses as $key => $value): ?>
                                 <option value="<?php echo $key; ?>" <?php echo ($quiz['statut'] === $key) ? 'selected' : ''; ?>>
                                     <?php echo htmlspecialchars($value); ?>
                                 </option>
                             <?php endforeach; ?>
                         </select>
                         <small class="form-text text-muted">Un quiz 'Brouillon' n'est pas visible par les étudiants.</small>
                     </div>
                </div>

                <div class="mb-3">
                    <label for="ordre" class="form-label">Ordre d'affichage</label>
                    <input type="number" class="form-control" id="ordre" name="ordre" value="<?php echo htmlspecialchars((string)$quiz['ordre']); ?>" min="0" step="1">
                    <small class="form-text text-muted">Les quiz seront triés par ce numéro (0 en premier).</small>
                </div>

                <hr>

                <div class="d-flex justify-content-end">
                    <a href="manage_lecon_quiz.php?lecon_id=<?php echo $lecon_id; ?>" class="btn btn-secondary me-2">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        <?php echo $form_action === 'add' ? 'Ajouter le Quiz' : 'Mettre à jour le Quiz'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
