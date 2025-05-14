<?php
class OrangeMoney {
    private $apiKey = 'TEST_API_KEY_123';
    private $merchantId = 'LAWAPP_MERCHANT_001';
    private $currency = 'USD';
    
    // Simuler une transaction
    public function processPayment($amount, $phoneNumber, $description) {
        // Simuler un délai d'API
        usleep(500000); // 0.5 seconde
        
        // Générer un ID de transaction unique
        $transactionId = 'OM_' . time() . '_' . rand(1000, 9999);
        
        // Simuler une validation de numéro de téléphone
        if (!$this->validatePhoneNumber($phoneNumber)) {
            return [
                'success' => false,
                'error' => 'Numéro de téléphone invalide',
                'code' => 'INVALID_PHONE'
            ];
        }
        
        // Simuler une vérification de solde (échoue si montant > 1000)
        if ($amount > 1000) {
            return [
                'success' => false,
                'error' => 'Solde insuffisant',
                'code' => 'INSUFFICIENT_FUNDS'
            ];
        }
        
        // Simuler un taux de réussite de 90%
        if (rand(1, 100) > 90) {
            return [
                'success' => false,
                'error' => 'Erreur réseau. Veuillez réessayer.',
                'code' => 'NETWORK_ERROR'
            ];
        }
        
        // Transaction réussie
        return [
            'success' => true,
            'transactionId' => $transactionId,
            'amount' => $amount,
            'currency' => $this->currency,
            'timestamp' => date('Y-m-d H:i:s'),
            'description' => $description,
            'phoneNumber' => $phoneNumber
        ];
    }
    
    // Vérifier le statut d'une transaction
    public function checkTransactionStatus($transactionId) {
        // Simuler un délai d'API
        usleep(300000); // 0.3 seconde
        
        // Simuler différents états possibles
        $statuses = ['completed', 'pending', 'failed'];
        $randomStatus = $statuses[array_rand($statuses)];
        
        return [
            'transactionId' => $transactionId,
            'status' => $randomStatus,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    // Valider le format du numéro de téléphone
    private function validatePhoneNumber($phoneNumber) {
        // Format attendu : +XXX XXXXXXXX (ex: +225 07123456)
        return preg_match('/^\+\d{3}\s\d{8}$/', $phoneNumber);
    }
    
    // Obtenir le solde du compte (simulation)
    public function getBalance($phoneNumber) {
        if (!$this->validatePhoneNumber($phoneNumber)) {
            return [
                'success' => false,
                'error' => 'Numéro de téléphone invalide'
            ];
        }
        
        // Simuler un solde aléatoire entre 100 et 2000 USD
        $balance = rand(100, 2000);
        
        return [
            'success' => true,
            'balance' => $balance,
            'currency' => $this->currency,
            'phoneNumber' => $phoneNumber,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>
