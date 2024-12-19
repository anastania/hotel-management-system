<?php
namespace App;

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Exception;

class PaymentService {
    private $apiContext;
    private $currency;

    public function __construct($clientId, $clientSecret, $mode = 'sandbox', $currency = 'EUR') {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($clientId, $clientSecret)
        );

        $this->apiContext->setConfig([
            'mode' => $mode,
            'log.LogEnabled' => true,
            'log.FileName' => '../PayPal.log',
            'log.LogLevel' => 'DEBUG'
        ]);

        $this->currency = $currency;
    }

    public function createPayment($amount, $description, $returnUrl, $cancelUrl) {
        try {
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $amountDetails = new Amount();
            $amountDetails->setTotal(number_format($amount, 2, '.', ''))
                         ->setCurrency($this->currency);

            $transaction = new Transaction();
            $transaction->setAmount($amountDetails)
                       ->setDescription($description);

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($returnUrl)
                        ->setCancelUrl($cancelUrl);

            $payment = new Payment();
            $payment->setIntent('sale')
                   ->setPayer($payer)
                   ->setTransactions([$transaction])
                   ->setRedirectUrls($redirectUrls);

            $payment->create($this->apiContext);
            return $payment;

        } catch (Exception $e) {
            error_log("Erreur PayPal lors de la création du paiement: " . $e->getMessage());
            throw new Exception("Erreur lors de la création du paiement PayPal: " . $e->getMessage());
        }
    }

    public function executePayment($paymentId, $payerId) {
        try {
            $payment = Payment::get($paymentId, $this->apiContext);
            
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);

            $result = $payment->execute($execution, $this->apiContext);
            return $result;

        } catch (Exception $e) {
            error_log("Erreur PayPal lors de l'exécution du paiement: " . $e->getMessage());
            throw new Exception("Erreur lors de l'exécution du paiement PayPal: " . $e->getMessage());
        }
    }
}
?>
