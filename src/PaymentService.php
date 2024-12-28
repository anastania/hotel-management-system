<?php
namespace App;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use Exception;

class PaymentService {
    private $apiContext;

    public function __construct($clientId, $clientSecret, $mode = 'sandbox') {
        // Load the PayPal SDK
        require_once __DIR__ . '/../vendor/autoload.php';

        // Create API context
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($clientId, $clientSecret)
        );

        // Set config
        $this->apiContext->setConfig([
            'mode' => $mode,
            'log.LogEnabled' => true,
            'log.FileName' => __DIR__ . '/../logs/PayPal.log',
            'log.LogLevel' => 'DEBUG',
            'cache.enabled' => false
        ]);
    }

    public function createPayment($amount, $description, $returnUrl, $cancelUrl) {
        try {
            // Create new payment
            $payment = new Payment();

            // Set payment method
            $payer = new Payer();
            $payer->setPaymentMethod("paypal");

            // Set redirect URLs
            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($returnUrl)
                        ->setCancelUrl($cancelUrl);

            // Set transaction amount
            $amountDetails = new Amount();
            $amountDetails->setCurrency("EUR")
                         ->setTotal($amount);

            // Create transaction
            $transaction = new Transaction();
            $transaction->setAmount($amountDetails)
                       ->setDescription($description);

            // Build payment
            $payment->setIntent("sale")
                   ->setPayer($payer)
                   ->setRedirectUrls($redirectUrls)
                   ->setTransactions(array($transaction));

            try {
                $payment->create($this->apiContext);
                return $payment;
            } catch (\Exception $ex) {
                throw new Exception("Erreur lors de la création du paiement: " . $ex->getMessage());
            }
        } catch (\Exception $ex) {
            throw new Exception("Erreur lors de la préparation du paiement: " . $ex->getMessage());
        }
    }

    public function executePayment($paymentId, $payerId) {
        try {
            $payment = Payment::get($paymentId, $this->apiContext);
            
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);

            $result = $payment->execute($execution, $this->apiContext);
            return $result;
        } catch (\Exception $ex) {
            throw new Exception("Erreur lors de l'exécution du paiement: " . $ex->getMessage());
        }
    }
}
