<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    
    // Update reservation status
    $sql = "UPDATE reservations 
            SET status = 'pending', 
                payment_status = 'cancelled', 
                updated_at = CURRENT_TIMESTAMP 
            WHERE payment_id = ? AND id_client = ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $order_id, $_SESSION['id_client']);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['warning'] = "Le paiement a été annulé. Vous pouvez réessayer plus tard.";
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour de la réservation.";
    }
} else {
    $_SESSION['error'] = "Informations de paiement manquantes.";
}

header('Location: mes_reservations.php');
exit;
