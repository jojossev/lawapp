<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php'; // Pour la fonction redirect

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // N'accepter que les requêtes POST
    redirect('manage_cours.php');
}

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT); // Pour edit et delete
$id_cours = filter_input(INPUT_POST, 'id_cours', FILTER_VALIDATE_INT); // Crucial pour la redirection

// Vérification de base : id_cours est nécessaire pour savoir où rediriger
if (!$id_cours && $action !== 'delete') { // Delete peut potentiellement fonctionner sans, mais la redirection échouera
    $_SESSION['error_message'] = "ID de cours manquant.";
    // Tenter une redirection générique si id_cours manque
    redirect('manage_cours.php');
}
// Destination de redirection par défaut
$redirect_url = 'manage_cours_contenu.php?cours_id=' . $id_cours;


// --- Action: Ajouter un module ---
if ($action === 'add') {
    $titre = trim(filter_input(INPUT_POST, 'titre', FILTER_SANITIZE_SPECIAL_CHARS));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS));
    $ordre = filter_input(INPUT_POST, 'ordre', FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
    $statut = filter_input(INPUT_POST, 'statut', FILTER_SANITIZE_SPECIAL_CHARS);
    $allowed_statuts = ['brouillon', 'publie']; // Ajoutez d'autres si nécessaire

    // Validation simple
    if (empty($titre) || !$id_cours || !in_array($statut, $allowed_statuts)) {
        $_SESSION['error_message'] = "Données du formulaire invalides ou manquantes.";
        $_SESSION['form_values'] = $_POST; // Sauvegarder les valeurs pour pré-remplir
        redirect('module_form.php?cours_id=' . $id_cours);
    }

    try {
        $sql = "INSERT INTO modules (id_cours, titre, description, ordre, statut, date_creation, date_mise_a_jour) 
                VALUES (:id_cours, :titre, :description, :ordre, :statut, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_cours', $id_cours, PDO::PARAM_INT);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':ordre', $ordre, PDO::PARAM_INT);
        $stmt->bindParam(':statut', $statut);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Module ajouté avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'ajout du module.";
        }
    } catch (PDOException $e) {
        error_log("Erreur SQL (Add Module): " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur base de données lors de l'ajout du module. Code: " . $e->getCode();
         if ($e->getCode() == '23000') { // Contrainte d'unicité ou clé étrangère
             $_SESSION['error_message'] = "Erreur de contrainte de base de données. Vérifiez que le cours existe et qu'il n'y a pas de conflit.";
         }
        $_SESSION['form_values'] = $_POST;
        redirect('module_form.php?cours_id=' . $id_cours); // Redirige vers le form en cas d'erreur SQL
    }
    redirect($redirect_url); // Rediriger vers la liste des modules du cours

}
// --- Action: Modifier un module ---
elseif ($action === 'edit') {
    $titre = trim(filter_input(INPUT_POST, 'titre', FILTER_SANITIZE_SPECIAL_CHARS));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS));
    $ordre = filter_input(INPUT_POST, 'ordre', FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
    $statut = filter_input(INPUT_POST, 'statut', FILTER_SANITIZE_SPECIAL_CHARS);
    $allowed_statuts = ['brouillon', 'publie'];

    // Validation
    if (empty($titre) || !$id || !$id_cours || !in_array($statut, $allowed_statuts)) {
        $_SESSION['error_message'] = "Données du formulaire invalides ou manquantes pour la modification.";
        $_SESSION['form_values'] = $_POST;
        redirect('module_form.php?id=' . $id . '&cours_id=' . $id_cours);
    }

    try {
        $sql = "UPDATE modules SET 
                    titre = :titre, 
                    description = :description, 
                    ordre = :ordre, 
                    statut = :statut, 
                    date_mise_a_jour = NOW() 
                WHERE id = :id AND id_cours = :id_cours"; // Sécurité: s'assurer qu'on modifie bien un module du bon cours
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':ordre', $ordre, PDO::PARAM_INT);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':id_cours', $id_cours, PDO::PARAM_INT);


        if ($stmt->execute()) {
             if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Module mis à jour avec succès.";
             } else {
                // Soit aucune modification n'a été faite, soit l'ID/ID_Cours ne correspondait pas
                 $_SESSION['error_message'] = "Aucune modification effectuée (données identiques ou module non trouvé pour ce cours).";
             }
        } else {
            $_SESSION['error_message'] = "Erreur lors de la mise à jour du module.";
        }
    } catch (PDOException $e) {
        error_log("Erreur SQL (Edit Module): " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur base de données lors de la mise à jour du module.";
        $_SESSION['form_values'] = $_POST;
        redirect('module_form.php?id=' . $id . '&cours_id=' . $id_cours); // Redirige vers le form en cas d'erreur SQL
    }
     redirect($redirect_url);

}
// --- Action: Supprimer un module ---
elseif ($action === 'delete') {
     $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
     $id_cours_redirect = filter_input(INPUT_POST, 'cours_id', FILTER_VALIDATE_INT); // Récupérer pour redirection

     if (!$id) {
         $_SESSION['error_message'] = "ID de module manquant pour la suppression.";
         redirect($id_cours_redirect ? 'manage_cours_contenu.php?cours_id=' . $id_cours_redirect : 'manage_cours.php');
     }

    // Assurez-vous que les clés étrangères sont configurées avec ON DELETE CASCADE pour les leçons, quiz liés aux leçons, etc.
    // Sinon, il faudra supprimer manuellement les éléments dépendants avant de supprimer le module.
    try {
        $sql = "DELETE FROM modules WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
             if ($stmt->rowCount() > 0) {
                 $_SESSION['success_message'] = "Module (et son contenu associé) supprimé avec succès.";
             } else {
                  $_SESSION['error_message'] = "Module non trouvé ou déjà supprimé.";
             }
        } else {
            $_SESSION['error_message'] = "Erreur lors de la suppression du module.";
        }
    } catch (PDOException $e) {
        error_log("Erreur SQL (Delete Module): " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur base de données lors de la suppression. Vérifiez les dépendances. Code: " . $e->getCode();
         if ($e->getCode() == '23000') { // Foreign key constraint
             $_SESSION['error_message'] = "Impossible de supprimer ce module car il est référencé ailleurs (peut-être des quiz liés directement au module ?).";
         }
    }
    // Rediriger vers la page du cours si on a l'ID, sinon vers la liste des cours
    redirect($id_cours_redirect ? 'manage_cours_contenu.php?cours_id=' . $id_cours_redirect : 'manage_cours.php');
}
// --- Action Inconnue ---
else {
    $_SESSION['error_message'] = "Action non valide.";
    redirect($redirect_url); // Rediriger vers la page contenu du cours si possible
}

exit; // Terminer le script
?>
