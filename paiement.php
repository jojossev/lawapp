<?php
$page_title = "Paiement";
$extra_css = "<link rel='stylesheet' href='css/auth.css'>";
require_once 'includes/header.php';
require_once 'includes/OrangeMoney.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// Récupérer les détails du produit
$product_type = isset($_GET['type']) ? $_GET['type'] : '';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$error = null;

try {
    switch ($product_type) {
        case 'cours':
            $stmt = $pdo->prepare("SELECT titre, prix FROM cours WHERE id = ?");
            break;
        case 'livre':
            $stmt = $pdo->prepare("SELECT titre, prix FROM livres WHERE id = ?");
            break;
        case 'podcast':
            $stmt = $pdo->prepare("SELECT titre, prix FROM podcasts WHERE id = ?");
            break;
        default:
            throw new Exception("Type de produit invalide");
    }
    
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception("Produit non trouvé");
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Traiter le paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product) {
    $phoneNumber = $_POST['phone_number'] ?? '';
    $orangeMoney = new OrangeMoney();
    
    $result = $orangeMoney->processPayment(
        $product['prix'],
        $phoneNumber,
        "Paiement {$product_type} : {$product['titre']}"
    );
    
    if ($result['success']) {
        // Enregistrer la transaction
        try {
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, product_type, product_id, montant, transaction_id, statut) VALUES (?, ?, ?, ?, ?, 'completed')");
            $stmt->execute([
                $_SESSION['user_id'],
                $product_type,
                $product_id,
                $product['prix'],
                $result['transactionId']
            ]);
            
            // Rediriger vers la page de succès
            $_SESSION['payment_success'] = true;
            $_SESSION['transaction_id'] = $result['transactionId'];
            header('Location: paiement_succes.php');
            exit;
        } catch (PDOException $e) {
            $error = "Erreur lors de l'enregistrement de la transaction";
        }
    } else {
        $error = $result['error'];
    }
}
?>

<div class="auth-container">
    <div class="auth-box">
        <?php if ($error): ?>
            <div class="alert alert-error animate-fadeIn">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($product): ?>
            <div class="auth-header">
                <h1 class="gradient-text animate-slideIn">Paiement Orange Money</h1>
                <p class="auth-subtitle animate-fadeIn delay-200">
                    <?php echo htmlspecialchars($product_type); ?> : <?php echo htmlspecialchars($product['titre']); ?>
                </p>
            </div>

            <div class="payment-details animate-fadeIn delay-300">
                <div class="amount-display">
                    <span class="amount"><?php echo number_format($product['prix'], 2); ?></span>
                    <span class="currency">USD</span>
                </div>
            </div>

            <form action="" method="POST" class="auth-form animate-fadeIn delay-400">
                <div class="form-group">
                    <label for="phone_number" class="form-label">Numéro Orange Money</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <img src="assets/icons/phone.svg" alt="Téléphone">
                        </span>
                        <input type="tel" name="phone_number" id="phone_number" 
                               class="form-input" required 
                               placeholder="+225 07123456"
                               pattern="^\+\d{3}\s\d{8}$">
                    </div>
                    <small class="form-help">Format: +225 07123456</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block hover-scale animate-fadeIn delay-500">
                    Payer maintenant
                </button>
            </form>

            <div class="payment-info animate-fadeIn delay-600">
                <div class="secure-badge">
                    <img src="assets/icons/shield.svg" alt="Sécurisé">
                    <span>Paiement 100% sécurisé</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
