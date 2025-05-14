<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Sauvegarder l'URL actuelle pour rediriger après la connexion
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Message d'erreur
    $_SESSION['error_message'] = "Veuillez vous connecter pour accéder à cette page.";
    
    // Rediriger vers la page de connexion
    header('Location: /LawApp/login.php');
    exit;
}

// Vérifier si l'utilisateur a le droit d'accéder à cette page
function checkUserAccess($required_type = null) {
    if ($required_type) {
        // Si un type spécifique est requis, vérifier le type de l'utilisateur
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== $required_type) {
            $_SESSION['error_message'] = "Vous n'avez pas les droits nécessaires pour accéder à cette page.";
            header('Location: /LawApp/accueil.php');
            exit;
        }
    }
}

// Vérifier si l'utilisateur a accès au cours/leçon spécifique
function checkCourseAccess($course_id) {
    global $pdo;
    
    try {
        // Vérifier si le cours est gratuit ou si l'utilisateur y est inscrit
        $stmt = $pdo->prepare("
            SELECT c.prix, COALESCE(i.id, 0) as is_inscrit 
            FROM cours c 
            LEFT JOIN inscriptions i ON i.id_cours = c.id AND i.id_utilisateur = :user_id
            WHERE c.id = :course_id
        ");
        
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':course_id' => $course_id
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            $_SESSION['error_message'] = "Ce cours n'existe pas.";
            header('Location: /LawApp/catalogue.php');
            exit;
        }
        
        // Si le cours est payant et l'utilisateur n'est pas inscrit
        if ($result['prix'] > 0 && !$result['is_inscrit']) {
            $_SESSION['error_message'] = "Vous devez vous inscrire à ce cours pour y accéder.";
            header('Location: /LawApp/cours.php?id=' . $course_id);
            exit;
        }
        
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification de l'accès au cours: " . $e->getMessage());
        $_SESSION['error_message'] = "Une erreur est survenue lors de la vérification de vos droits d'accès.";
        header('Location: /LawApp/accueil.php');
        exit;
    }
}

// Vérifier si l'utilisateur a accès à la leçon spécifique
function checkLessonAccess($lesson_id) {
    global $pdo;
    
    try {
        // Vérifier si la leçon fait partie d'un cours auquel l'utilisateur est inscrit
        $stmt = $pdo->prepare("
            SELECT c.id as course_id, c.prix, COALESCE(i.id, 0) as is_inscrit 
            FROM lecons l
            JOIN modules m ON l.id_module = m.id
            JOIN cours c ON m.id_cours = c.id
            LEFT JOIN inscriptions i ON i.id_cours = c.id AND i.id_utilisateur = :user_id
            WHERE l.id = :lesson_id
        ");
        
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':lesson_id' => $lesson_id
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            $_SESSION['error_message'] = "Cette leçon n'existe pas.";
            header('Location: /LawApp/catalogue.php');
            exit;
        }
        
        // Si le cours est payant et l'utilisateur n'est pas inscrit
        if ($result['prix'] > 0 && !$result['is_inscrit']) {
            $_SESSION['error_message'] = "Vous devez vous inscrire au cours pour accéder à cette leçon.";
            header('Location: /LawApp/cours.php?id=' . $result['course_id']);
            exit;
        }
        
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification de l'accès à la leçon: " . $e->getMessage());
        $_SESSION['error_message'] = "Une erreur est survenue lors de la vérification de vos droits d'accès.";
        header('Location: /LawApp/accueil.php');
        exit;
    }
}

// Mettre à jour la dernière activité de l'utilisateur
try {
    $stmt = $pdo->prepare("
        UPDATE utilisateurs 
        SET derniere_connexion = CURRENT_TIMESTAMP 
        WHERE id = :user_id
    ");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
} catch (PDOException $e) {
    // On log l'erreur mais on ne bloque pas l'accès
    error_log("Erreur lors de la mise à jour de derniere_connexion: " . $e->getMessage());
}
?>
