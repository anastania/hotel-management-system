<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'src/PaymentService.php';

use App\PaymentService;

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_reservation = isset($_POST['id_reservation']) ? $_POST['id_reservation'] : null;
    $total_amount = isset($_POST['total_amount']) ? $_POST['total_amount'] : null;

    if (!$id_reservation || !$total_amount) {
        $_SESSION['error'] = "Informations de paiement manquantes.";
        header('Location: mes_reservations.php');
        exit();
    }

    try {
        // Get reservation details
        $sql = "SELECT * FROM reservations WHERE id_reservation = ? AND id_client = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $id_reservation, $_SESSION['id_client']);
        mysqli_stmt_execute($stmt);
        $reservation = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$reservation) {
            throw new Exception("Réservation non trouvée.");
        }

        // Initialize PayPal
        $paymentService = new PaymentService(
            PAYPAL_CLIENT_ID,
            PAYPAL_SECRET,
            PAYPAL_MODE
        );

        // Create payment
        $payment = $paymentService->createPayment(
            $total_amount,
            "Réservation #" . $id_reservation,
            BASE_URL . "/payment_success.php?reservation_id=" . $id_reservation,
            BASE_URL . "/payment_cancel.php?reservation_id=" . $id_reservation
        );

        // Get approval URL
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() === 'approval_url') {
                header('Location: ' . $link->getHref());
                exit();
            }
        }

        throw new Exception("URL de paiement PayPal non trouvée.");

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: mes_reservations.php');
        exit();
    }
} else {
    $_SESSION['error'] = "Méthode de requête invalide.";
    header('Location: mes_reservations.php');
    exit();
}
