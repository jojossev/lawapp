<?php
$page_title = "Mes achats";
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Récupérer les transactions de l'utilisateur
try {
    $stmt = $pdo->prepare("
        SELECT t.*, 
            CASE 
                WHEN t.product_type = 'cours' THEN c.titre
                WHEN t.product_type = 'livre' THEN l.titre
                WHEN t.product_type = 'podcast' THEN p.titre
            END as product_title
        FROM transactions t
        LEFT JOIN cours c ON t.product_type = 'cours' AND t.product_id = c.id
        LEFT JOIN livres l ON t.product_type = 'livre' AND t.product_id = l.id
        LEFT JOIN podcasts p ON t.product_type = 'podcast' AND t.product_id = p.id
        WHERE t.user_id = ?
        ORDER BY t.date_creation DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des transactions";
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8 gradient-text">Mes achats</h1>

    <?php if (!empty($transactions)): ?>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($transactions as $transaction): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                <?php echo date('d/m/Y H:i', strtotime($transaction['date_creation'])); ?>
                            </span>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                <?php echo $transaction['statut'] === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                         ($transaction['statut'] === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                         'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'); ?>">
                                <?php echo ucfirst($transaction['statut']); ?>
                            </span>
                        </div>
                        
                        <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($transaction['product_title']); ?>
                        </h3>
                        
                        <div class="flex items-center text-gray-600 dark:text-gray-300 mb-4">
                            <span class="capitalize"><?php echo $transaction['product_type']; ?></span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">
                                $<?php echo number_format($transaction['montant'], 2); ?>
                            </span>
                            
                            <?php if ($transaction['statut'] === 'completed'): ?>
                                <?php 
                                $accessUrl = '';
                                switch ($transaction['product_type']) {
                                    case 'cours':
                                        $accessUrl = "cours.php?id=" . $transaction['product_id'];
                                        break;
                                    case 'livre':
                                        $accessUrl = "livre.php?id=" . $transaction['product_id'];
                                        break;
                                    case 'podcast':
                                        $accessUrl = "podcast.php?id=" . $transaction['product_id'];
                                        break;
                                }
                                ?>
                                <a href="<?php echo $accessUrl; ?>" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                                    Accéder
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Transaction ID: <?php echo $transaction['transaction_id']; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <img src="assets/icons/shopping-bag.svg" alt="Aucun achat" class="mx-auto mb-4 w-16 h-16 opacity-50">
            <h2 class="text-xl font-semibold mb-2">Aucun achat pour le moment</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Découvrez notre catalogue de cours, livres et podcasts</p>
            <a href="catalogue.php" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors duration-300">
                Voir le catalogue
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
