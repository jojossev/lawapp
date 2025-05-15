<?php // Test comment for line 1 diagnosis
// config.php est maintenant inclus via header.php
$page_title = "Inscription";
require_once 'includes/header.php';

$nom = '';
$prenom = '';
$email = '';
$errors = [];
$success_message = '';

// Redirection si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
    echo "<script>window.location.href = 'profil.php';</script>";
    echo "<div style='background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; padding: 10px; margin: 10px;'>Vous êtes déjà connecté. Redirection vers votre profil...</div>";
    die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);

    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom est requis.";
    }
    if (empty($prenom)) {
        $errors[] = "Le prénom est requis.";
    }
    if (empty($email)) {
        $errors[] = "L'adresse email est requise.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        try {
            $stmt_check_email = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email");
            $stmt_check_email->bindParam(':email', $email);
            $stmt_check_email->execute();
            if ($stmt_check_email->fetch()) {
                $errors[] = "Cette adresse email est déjà utilisée. Veuillez en choisir une autre ou vous <a href='login.php'>connecter</a>.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la vérification de l'email : " . $e->getMessage();
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt_insert_user = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, date_inscription, derniere_connexion) VALUES (:nom, :prenom, :email, :mot_de_passe, NOW(), NULL)");
            $stmt_insert_user->bindParam(':nom', $nom);
            $stmt_insert_user->bindParam(':prenom', $prenom);
            $stmt_insert_user->bindParam(':email', $email);
            $stmt_insert_user->bindParam(':mot_de_passe', $hashed_password);

            if ($stmt_insert_user->execute()) {
                $_SESSION['registration_success'] = "Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.";
                // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
                $success_message = "Votre compte a été créé avec succès ! Vous allez être redirigé vers la page de connexion...";
                echo "<script>setTimeout(function() { window.location.href = 'login.php?registration=success'; }, 2000);</script>";
                // Ne pas utiliser exit ici
            } else {
                $errors[] = "Une erreur s'est produite lors de la création de votre compte. Veuillez réessayer.";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '42S02') {
                $errors[] = "Erreur système : La table des utilisateurs semble manquante. Veuillez contacter l'administrateur.";
            } else {
                $errors[] = "Erreur d'inscription : " . $e->getMessage();
            }
        }
    }
}
?>

<div class="container page-content auth-page">
    <div class="card auth-card">
        <h2 class="page-title text-center">Créer un compte</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; // Permet le HTML pour le lien de connexion ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" name="prenom" id="prenom" class="form-control" value="<?php echo htmlspecialchars($prenom); ?>" required>
            </div>
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" name="nom" id="nom" class="form-control" value="<?php echo htmlspecialchars($nom); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Adresse Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe (8 caractères minimum)</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
            </div>
        </form>
        <p class="text-center auth-links">
            Vous avez déjà un compte ? <a href="login.php">Connectez-vous ici</a>.
        </p>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
