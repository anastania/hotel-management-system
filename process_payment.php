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

// Check if it's a POST request with payment details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get payment details from POST data
    $id_chambre = isset($_POST['id_chambre']) ? $_POST['id_chambre'] : null;
    $date_arrivee = isset($_POST['date_arrivee']) ? $_POST['date_arrivee'] : null;
    $date_depart = isset($_POST['date_depart']) ? $_POST['date_depart'] : null;
    $total_amount = isset($_POST['total_amount']) ? $_POST['total_amount'] : null;

    // Validate required fields
    if (!$id_chambre || !$date_arrivee || !$date_depart || !$total_amount) {
        $_SESSION['error'] = "Tous les champs sont requis pour le paiement.";
        header('Location: mes_reservations.php');
        exit();
    }

    try {
        // First, create a new reservation record with pending status
        $sql = "INSERT INTO reservations (id_client, id_chambre, date_arrivee, date_depart, prix_total, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iissd", $_SESSION['id'], $id_chambre, $date_arrivee, $date_depart, $total_amount);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erreur lors de la création de la réservation");
        }

        $reservation_id = mysqli_insert_id($conn);

        // Initialize PayPal payment
        $paymentService = new PaymentService(
            PAYPAL_CLIENT_ID,
            PAYPAL_SECRET,
            PAYPAL_MODE
        );

        // Create payment with PayPal
        $payment = $paymentService->createPayment(
            $total_amount,
            "Réservation #" . $reservation_id,
            "http://localhost/Gestion_reservation_hotel/payment_success.php?reservation_id=" . $reservation_id,
            "http://localhost/Gestion_reservation_hotel/payment_cancel.php?reservation_id=" . $reservation_id
        );

        // Store payment ID in database
        $sql = "UPDATE reservations SET payment_id = ? WHERE id_reservation = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $payment->getId(), $reservation_id);
        mysqli_stmt_execute($stmt);

        // Redirect to PayPal
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() === 'approval_url') {
                header('Location: ' . $link->getHref());
                exit();
            }
        }

        throw new Exception("Erreur lors de la création du lien de paiement PayPal");

    } catch (Exception $e) {
        error_log("Payment error: " . $e->getMessage());
        $_SESSION['error'] = "Erreur lors de la création du paiement: " . $e->getMessage();
        header('Location: mes_reservations.php');
        exit();
    }
} else {
    $_SESSION['error'] = "Méthode de requête non valide.";
    header('Location: mes_reservations.php');
    exit();
}
?>
