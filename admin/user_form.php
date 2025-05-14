<?php
require_once __DIR__ . '/admin_auth_check.php'; // Authentification et configuration

// Déterminer l'action (ajouter ou modifier) et récupérer les données si modification
$action = $_GET['action'] ?? 'add'; // Par défaut 'add'
$user_id = $_GET['id'] ?? null;
$user_data = ['prenom' => '', 'nom' => '', 'email' => '', 'role' => 'user']; // Données par défaut
$form_title = "Ajouter un nouvel utilisateur";
$submit_button_text = "Ajouter l\'utilisateur";
$password_required = true;
$page_errors = [];

if ($action === 'edit' && $user_id) {
    $form_title = "Modifier l\'utilisateur";
    $submit_button_text = "Mettre à jour l\'utilisateur";
    $password_required = false; // Le mot de passe n'est pas requis pour la modification, sauf si on veut le changer
    try {
        if (!isset($pdo)) {
            throw new Exception("La connexion PDO n\'est pas disponible.");
        }
        $stmt = $pdo->prepare("SELECT prenom, nom, email, role FROM utilisateurs WHERE id = :id");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $fetched_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fetched_user) {
            $user_data = $fetched_user;
        } else {
            $_SESSION['admin_flash_message'] = "Erreur : Utilisateur non trouvé (ID: " . htmlspecialchars($user_id) . ").";
            $_SESSION['admin_flash_type'] = "error";
            header("Location: manage_users.php");
            exit;
        }
    } catch (Exception $e) {
        $page_errors[] = "Erreur lors de la récupération des données de l\'utilisateur : " . $e->getMessage();
        error_log("Admin user_form.php - Exception: " . $e->getMessage());
        // On pourrait choisir de rediriger ou d'afficher le formulaire avec une erreur
    }
} elseif ($action !== 'add') {
    $_SESSION['admin_flash_message'] = "Action non valide spécifiée.";
    $_SESSION['admin_flash_type'] = "error";
    header("Location: manage_users.php");
    exit;
}

// Récupérer les erreurs de validation et les données soumises de la session (après redirection)
$form_errors = $_SESSION['admin_form_errors'] ?? [];
$form_data = $_SESSION['admin_form_data'] ?? [];
unset($_SESSION['admin_form_errors']);
unset($_SESSION['admin_form_data']);

// Si des données de formulaire ont été passées par la session (après une erreur de validation),
// elles ont la priorité sur les données chargées depuis la BDD (pour l'édition) ou les valeurs par défaut (pour l'ajout)
if (!empty($form_data)) {
    $user_data['prenom'] = htmlspecialchars($form_data['prenom'] ?? $user_data['prenom']);
    $user_data['nom'] = htmlspecialchars($form_data['nom'] ?? $user_data['nom']);
    $user_data['email'] = htmlspecialchars($form_data['email'] ?? $user_data['email']);
    $user_data['role'] = htmlspecialchars($form_data['role'] ?? $user_data['role']);
    // Le mot de passe n'est pas re-rempli pour des raisons de sécurité
}

$page_title = $form_title;
include_once __DIR__ . '/admin_header.php';
?>

<div class="admin-container">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($form_title); ?></h1>
        <a href="manage_users.php" class="btn-action">Retour à la liste</a>
    </div>

    <?php if (!empty($page_errors)):
    foreach ($page_errors as $error):
    ?>
        <div class="alert alert-danger" role="alert">
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php 
    endforeach;
    endif; ?>
    
    <?php
    // Afficher les messages flash de la session (par exemple, après une redirection)
    if (isset($_SESSION['admin_form_flash_message'])) {
        $flash_type = $_SESSION['admin_form_flash_type'] ?? 'info'; // 'info', 'success', 'error'
        echo '<div class="alert alert-' . htmlspecialchars($flash_type) . '">' . htmlspecialchars($_SESSION['admin_form_flash_message']) . '</div>';
        unset($_SESSION['admin_form_flash_message']);
        unset($_SESSION['admin_form_flash_type']);
    }
    ?>

    <?php if (!empty($form_errors)):
        ?>
        <div class="alert alert-danger" role="alert">
            <p><strong>Veuillez corriger les erreurs suivantes :</strong></p>
            <ul>
                <?php foreach ($form_errors as $error):
                    ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php 
                endforeach; ?>
            </ul>
        </div>
    <?php 
    endif; ?>

    <form action="user_actions.php" method="POST" class="admin-form">
        <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
        <?php if ($action === 'edit' && $user_id): ?>
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="prenom">Prénom :</label>
            <input type="text" name="prenom" id="prenom" value="<?php echo htmlspecialchars($user_data['prenom']); ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="nom">Nom :</label>
            <input type="text" name="nom" id="nom" value="<?php echo htmlspecialchars($user_data['nom']); ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" <?php echo $password_required ? 'required' : ''; ?> class="form-control">
            <?php if ($action === 'edit'): ?>
                <small class="form-text text-muted">Laissez vide si vous ne souhaitez pas changer le mot de passe.</small>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="mot_de_passe_confirm">Confirmer le mot de passe :</label>
            <input type="password" name="mot_de_passe_confirm" id="mot_de_passe_confirm" <?php echo $password_required ? 'required' : ''; ?> class="form-control">
             <?php if ($action === 'edit'): ?>
                <small class="form-text text-muted">Laissez vide si vous ne souhaitez pas changer le mot de passe.</small>
            <?php endif; ?>
        </div>


        <div class="form-group">
            <label for="role">Rôle :</label>
            <select name="role" id="role" required class="form-control">
                <option value="user" <?php echo ($user_data['role'] === 'user') ? 'selected' : ''; ?>>Utilisateur</option>
                <option value="admin" <?php echo ($user_data['role'] === 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                <!-- Ajoutez d'autres rôles si nécessaire -->
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn-add"><?php echo htmlspecialchars($submit_button_text); ?></button>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/admin_footer.php'; ?>
