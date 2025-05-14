<?php
require_once 'includes/auth_check.php';
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalogue.php');
    exit;
}

$livre_id = isset($_POST['livre_id']) ? (int)$_POST['livre_id'] : 0;
$note = isset($_POST['note']) ? (int)$_POST['note'] : 0;
$commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';
$user_id = $_SESSION['user_id'];

// Validation
if (!$livre_id || $note < 1 || $note > 5 || empty($commentaire)) {
    $_SESSION['error_message'] = "Données invalides. Veuillez réessayer.";
    header('Location: livre.php?id=' . $livre_id);
    exit;
}

try {
    // Vérifier si l'utilisateur a acheté le livre
    $stmt = $pdo->prepare("
        SELECT id FROM achats_livres 
        WHERE id_utilisateur = :user_id 
        AND id_livre = :livre_id 
        AND statut = 'complete'
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':livre_id' => $livre_id
    ]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Vous devez acheter le livre pour pouvoir donner votre avis.");
    }
    
    // Vérifier si l'utilisateur a déjà donné son avis
    $stmt = $pdo->prepare("
        SELECT id FROM avis_livres 
        WHERE id_utilisateur = :user_id 
        AND id_livre = :livre_id
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':livre_id' => $livre_id
    ]);
    
    if ($stmt->fetch()) {
        // Mettre à jour l'avis existant
        $stmt = $pdo->prepare("
            UPDATE avis_livres 
            SET note = :note, 
                commentaire = :commentaire,
                date_modification = CURRENT_TIMESTAMP
            WHERE id_utilisateur = :user_id 
            AND id_livre = :livre_id
        ");
    } else {
        // Insérer un nouvel avis
        $stmt = $pdo->prepare("
            INSERT INTO avis_livres (id_utilisateur, id_livre, note, commentaire)
            VALUES (:user_id, :livre_id, :note, :commentaire)
        ");
    }
    
    $stmt->execute([
        ':user_id' => $user_id,
        ':livre_id' => $livre_id,
        ':note' => $note,
        ':commentaire' => $commentaire
    ]);
    
    $_SESSION['success_message'] = "Votre avis a été publié avec succès.";
    
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
}

header('Location: livre.php?id=' . $livre_id);
exit;
?>
