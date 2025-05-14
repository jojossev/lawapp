<?php
$page_title = "Connexion";
$extra_css = "<link rel='stylesheet' href='css/auth.css'>";
require_once 'includes/header.php';

$email = '';
$errors = [];

// Redirection si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header("Location: profil.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validation basique
    if (empty($email)) {
        $errors[] = "L'adresse email est requise.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    }

    if (empty($errors)) {
        // Vérification des identifiants
        $user_data_from_db = [];

        try {
            $stmt = $pdo->prepare("SELECT id, nom, prenom, email, mot_de_passe FROM utilisateurs WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user_data_from_db = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la connexion à la base de données : " . $e->getMessage();
        }

        if ($user_data_from_db && password_verify($password, $user_data_from_db['mot_de_passe'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user_data_from_db['id'];
            $_SESSION['user_nom'] = $user_data_from_db['nom'];
            $_SESSION['user_prenom'] = $user_data_from_db['prenom'];
            $_SESSION['user_email'] = $user_data_from_db['email'];

            // Mise à jour de la dernière date de connexion
            try {
                $stmt_update_login = $pdo->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = :user_id");
                $stmt_update_login->bindParam(':user_id', $_SESSION['user_id']);
                $stmt_update_login->execute();
            } catch (PDOException $e) {
                // Logguer l'erreur, mais ne pas bloquer la connexion pour cela
                error_log("Erreur lors de la mise à jour de la dernière connexion pour l'utilisateur ID {$_SESSION['user_id']}: " . $e->getMessage());
            }

            header("Location: profil.php");
            exit;
        } else {
            if (!$user_data_from_db && empty($errors)) {
                $errors[] = "Aucun compte n'est associé à cet email.";
            } elseif (empty($errors)) {
                $errors[] = "Email ou mot de passe incorrect.";
            }
        }
    }
}
?>

<div class="auth-container animate-fadeIn">
    <div class="auth-box glass">
        <div class="auth-header">
            <h1 class="gradient-text animate-slideIn">Bienvenue</h1>
            <p class="auth-subtitle animate-fadeIn delay-200">Connectez-vous pour accéder à vos cours</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error animate-fadeIn">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="auth-form animate-fadeIn delay-300">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-icon">
                        <img src="assets/icons/email.svg" alt="Email">
                    </span>
                    <input type="email" name="email" id="email" class="form-input" 
                           value="<?php echo htmlspecialchars($email); ?>" required 
                           placeholder="votre@email.com">
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <div class="input-group">
                    <span class="input-icon">
                        <img src="assets/icons/lock.svg" alt="Mot de passe">
                    </span>
                    <input type="password" name="password" id="password" class="form-input" 
                           required placeholder="Votre mot de passe">
                </div>
            </div>

            <div class="form-group remember-forgot">
                <label class="checkbox-container">
                    <input type="checkbox" name="remember" id="remember">
                    <span class="checkmark"></span>
                    Se souvenir de moi
                </label>
                <a href="mot_de_passe_oublie.php" class="forgot-link">Mot de passe oublié ?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block hover-scale animate-fadeIn delay-400">
                Se connecter
            </button>
        </form>

        <div class="auth-separator animate-fadeIn delay-500">
            <span>ou</span>
        </div>

        <div class="social-login animate-fadeIn delay-600">
            <button class="btn btn-outline social-btn hover-lift">
                <img src="assets/icons/google.svg" alt="Google">
                Continuer avec Google
            </button>
        </div>

        <p class="auth-links animate-fadeIn delay-700">
            Nouveau sur LawApp ? 
            <a href="register.php" class="gradient-link">Créer un compte</a>
        </p>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
