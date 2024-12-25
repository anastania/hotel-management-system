<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'src/PaymentService.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (!isset($_GET['reservation_id']) || !isset($_GET['paymentId']) || !isset($_GET['PayerID'])) {
    $_SESSION['error'] = "Informations de paiement manquantes.";
    header('Location: mes_reservations.php');
    exit;
}

$reservation_id = $_GET['reservation_id'];
$payment_id = $_GET['paymentId'];
$payer_id = $_GET['PayerID'];

try {
    // Initialize PayPal service
    $paymentService = new PaymentService(
        PAYPAL_CLIENT_ID,
        PAYPAL_SECRET,
        PAYPAL_MODE,
        PAYPAL_CURRENCY
    );

    // Execute the payment
    $payment = $paymentService->executePayment($payment_id, $payer_id);

    if ($payment->getState() === 'approved') {
        // Update reservation status
        $sql = "UPDATE reservations SET 
                status = 'confirmed',
                payment_status = 'completed',
                payment_id = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id_reservation = ? AND id_client = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sii", $payment_id, $reservation_id, $_SESSION['id']);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Paiement effectué avec succès! Votre réservation est confirmée.";
        } else {
            throw new Exception("Erreur lors de la mise à jour de la réservation");
        }
    } else {
        throw new Exception("Le paiement n'a pas été approuvé");
    }

    header('Location: mes_reservations.php');
    exit;

} catch (Exception $e) {
    error_log("Payment execution error: " . $e->getMessage());
    $_SESSION['error'] = "Erreur lors de la confirmation du paiement: " . $e->getMessage();
    header('Location: mes_reservations.php');
    exit;
}
?>
