<?php
session_start();

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/admin_auth_check.php';
    require_once __DIR__ . '/includes/admin_functions.php';
} catch (Exception $e) {
    die("Erreur lors du chargement des fichiers requis : " . $e->getMessage());
}

// Récupérer l'ID de la leçon
$lecon_id = filter_input(INPUT_GET, 'lecon_id', FILTER_VALIDATE_INT);

if (!$lecon_id) {
    $_SESSION['error_message'] = "ID de leçon invalide.";
    redirect('manage_cours.php');
}

// Variables pour stocker les messages de débogage
$debug_messages = [];

// Récupérer les informations de la leçon et sa hiérarchie (cours, module)
try {
    // Debug : afficher les paramètres
    echo "<!-- Debug: lecon_id = " . htmlspecialchars($lecon_id) . " -->\n";

    // Récupérer les informations de la leçon
    $sql = "SELECT l.*, m.titre as module_titre, m.id_cours, c.titre as cours_titre
            FROM lecons l
            JOIN modules m ON l.id_module = m.id
            JOIN cours c ON m.id_cours = c.id
            WHERE l.id = :lecon_id";
            
    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        throw new PDOException("Erreur de préparation de la requête SQL : " . print_r($pdo->errorInfo(), true));
    }

    $stmt->bindParam(':lecon_id', $lecon_id, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        throw new PDOException("Erreur d'exécution de la requête SQL : " . print_r($stmt->errorInfo(), true));
    }
    $lecon = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debug : résultats de la requête leçon
    echo "<!-- Debug: leçon = " . print_r($lecon, true) . " -->\n";

    if (!$lecon) {
        $_SESSION['error_message'] = "Leçon non trouvée.";
        redirect('manage_cours.php');
    }

    // Debug : requête SQL pour les quiz
    $sql_quiz = "SELECT id, titre, description, duree_limite, nombre_questions, score_minimum, statut
                 FROM quiz
                 WHERE id_lecon = :lecon_id
                 ORDER BY date_creation DESC";
    echo "<!-- Debug: SQL = " . htmlspecialchars($sql_quiz) . " -->\n";

    // Récupérer les quiz de la leçon
    $sql_quiz = "SELECT id, titre, description, duree_limite, nombre_questions, score_minimum, statut
                 FROM quiz
                 WHERE id_lecon = :lecon_id
                 ORDER BY date_creation DESC";
    $stmt_quiz = $pdo->prepare($sql_quiz);
    if (!$stmt_quiz) {
        throw new PDOException("Erreur de préparation de la requête SQL pour les quiz : " . print_r($pdo->errorInfo(), true));
    }

    $stmt_quiz->bindParam(':lecon_id', $lecon_id, PDO::PARAM_INT);
    if (!$stmt_quiz->execute()) {
        throw new PDOException("Erreur d'exécution de la requête SQL pour les quiz : " . print_r($stmt_quiz->errorInfo(), true));
    }
    $quizzes = $stmt_quiz->fetchAll(PDO::FETCH_ASSOC);

    // Debug : résultats de la requête quiz
    echo "<!-- Debug: quizzes = " . print_r($quizzes, true) . " -->\n";

} catch (PDOException $e) {
    // Sauvegarder l'erreur pour l'affichage après le header
    $error_details = [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString(),
        'sql' => $sql_quiz ?? 'Non disponible'
    ];
} catch (Exception $e) {
    // Sauvegarder l'erreur pour l'affichage après le header
    $error_details = [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString(),
        'sql' => 'N/A'
    ];
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
                <a href="manage_cours_contenu.php?cours_id=<?php echo $lecon['id_cours']; ?>">
                    <?php echo htmlspecialchars($lecon['cours_titre']); ?>
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="manage_module_contenu.php?module_id=<?php echo $lecon['id_module']; ?>">
                    <?php echo htmlspecialchars($lecon['module_titre']); ?>
                </a>
            </li>
            <li class="breadcrumb-item active">Quiz de <?php echo htmlspecialchars($lecon['titre']); ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Quiz de la Leçon : <?php echo htmlspecialchars($lecon['titre']); ?></h1>
        <a href="quiz_form.php?lecon_id=<?php echo $lecon_id; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter un Quiz
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

    <?php if (isset($error_details)): ?>
        <div class="alert alert-danger">
            <h4>Détails de l'erreur :</h4>
            <pre><?php echo htmlspecialchars($error_details['message']); ?></pre>
            <?php if ($error_details['sql'] !== 'N/A'): ?>
            <h4>Requête SQL :</h4>
            <pre><?php echo htmlspecialchars($error_details['sql']); ?></pre>
            <?php endif; ?>
        </div>
    <?php elseif (empty($quizzes)): ?>
        <div class="alert alert-info" role="alert">
            Aucun quiz n'a encore été créé pour cette leçon.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($quizzes as $quiz): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($quiz['titre']); ?></h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
                            <ul class="list-unstyled">
                                <li><strong>Questions :</strong> <?php echo $quiz['nombre_questions']; ?></li>
                                <li><strong>Durée :</strong> <?php echo $quiz['duree_limite'] ? $quiz['duree_limite'].' min' : 'Illimitée'; ?></li>
                                <li><strong>Score minimum :</strong> <?php echo $quiz['score_minimum']; ?>%</li>
                                <li><strong>Statut :</strong> 
                                    <span class="badge <?php echo $quiz['statut'] === 'publie' ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo $quiz['statut'] === 'publie' ? 'Publié' : 'Brouillon'; ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group" role="group">
                                <a href="quiz_form.php?id=<?php echo $quiz['id']; ?>&lecon_id=<?php echo $lecon_id; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="manage_questions.php?quiz_id=<?php echo $quiz['id']; ?>" 
                                   class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-list"></i> Questions
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDelete(<?php echo $quiz['id']; ?>)">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(quizId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce quiz ? Cette action est irréversible.')) {
        window.location.href = `quiz_actions.php?action=delete&id=${quizId}&lecon_id=<?php echo $lecon_id; ?>`;
    }
}
</script>

<?php include 'admin_footer.php'; ?>
