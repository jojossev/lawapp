<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$lecon_id = filter_input(INPUT_POST, 'lecon_id', FILTER_VALIDATE_INT);

if (!$lecon_id) {
    echo json_encode(['success' => false, 'message' => 'ID de leçon invalide']);
    exit;
}

try {
    // 1. Récupérer les informations de la leçon et du module
    $stmt = $pdo->prepare("
        SELECT l.id_module, m.id_cours
        FROM lecons l
        JOIN modules m ON l.id_module = m.id
        WHERE l.id = ?
    ");
    $stmt->execute([$lecon_id]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        echo json_encode(['success' => false, 'message' => 'Leçon non trouvée']);
        exit;
    }

    // 2. Mettre à jour la progression de l'utilisateur
    $stmt = $pdo->prepare("
        UPDATE progression_utilisateurs
        SET statut = 'termine',
            date_completion = NOW(),
            progression = 100
        WHERE id_utilisateur = ?
        AND id_lecon = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $lecon_id]);

    // 3. Calculer la progression globale du module
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_lecons,
            SUM(CASE WHEN pu.statut = 'termine' THEN 1 ELSE 0 END) as lecons_terminees
        FROM lecons l
        LEFT JOIN progression_utilisateurs pu 
            ON pu.id_lecon = l.id 
            AND pu.id_utilisateur = ?
        WHERE l.id_module = ?
        AND l.statut = 'publie'
    ");
    $stmt->execute([$_SESSION['user_id'], $info['id_module']]);
    $progression = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($progression['total_lecons'] > 0) {
        $pourcentage = ($progression['lecons_terminees'] / $progression['total_lecons']) * 100;

        // Mettre à jour la progression du module
        $stmt = $pdo->prepare("
            UPDATE progression_utilisateurs
            SET progression = ?,
                date_derniere_activite = NOW(),
                statut = CASE 
                    WHEN ? = ? THEN 'termine'
                    ELSE 'en_cours'
                END
            WHERE id_utilisateur = ?
            AND id_module = ?
        ");
        $stmt->execute([
            $pourcentage,
            $progression['lecons_terminees'],
            $progression['total_lecons'],
            $_SESSION['user_id'],
            $info['id_module']
        ]);

        // 4. Calculer la progression globale du cours
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_lecons,
                SUM(CASE WHEN pu.statut = 'termine' THEN 1 ELSE 0 END) as lecons_terminees
            FROM lecons l
            JOIN modules m ON l.id_module = m.id
            LEFT JOIN progression_utilisateurs pu 
                ON pu.id_lecon = l.id 
                AND pu.id_utilisateur = ?
            WHERE m.id_cours = ?
            AND l.statut = 'publie'
        ");
        $stmt->execute([$_SESSION['user_id'], $info['id_cours']]);
        $progression_cours = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($progression_cours['total_lecons'] > 0) {
            $pourcentage_cours = ($progression_cours['lecons_terminees'] / $progression_cours['total_lecons']) * 100;

            // Mettre à jour la progression du cours
            $stmt = $pdo->prepare("
                UPDATE progression_utilisateurs
                SET progression = ?,
                    date_derniere_activite = NOW(),
                    statut = CASE 
                        WHEN ? = ? THEN 'termine'
                        ELSE 'en_cours'
                    END
                WHERE id_utilisateur = ?
                AND id_cours = ?
            ");
            $stmt->execute([
                $pourcentage_cours,
                $progression_cours['lecons_terminees'],
                $progression_cours['total_lecons'],
                $_SESSION['user_id'],
                $info['id_cours']
            ]);
        }
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    error_log("Erreur lors de la mise à jour de la progression : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de la progression']);
}
?>
