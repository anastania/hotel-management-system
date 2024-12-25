<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];
    
    // Update reservation status to cancelled
    $sql = "UPDATE reservations SET status = 'cancelled' WHERE id_reservation = ? AND id_client = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $reservation_id, $_SESSION['id']);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['warning'] = "Le paiement a été annulé. Vous pouvez réessayer plus tard.";
    } else {
        $_SESSION['error'] = "Erreur lors de l'annulation de la réservation.";
    }
} else {
    $_SESSION['error'] = "Informations de réservation manquantes.";
}

header('Location: mes_reservations.php');
exit;
?>
