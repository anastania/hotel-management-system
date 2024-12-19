<?php
session_start();
require_once "includes/config.php";
require_once "includes/paypal_config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (!isset($_GET['order_id']) || !isset($_GET['reservation_id'])) {
    header("location: error.php");
    exit;
}

$order_id = $_GET['order_id'];
$reservation_id = $_GET['reservation_id'];

// Mettre à jour le statut de la réservation
$sql = "UPDATE reservations SET 
        status = 'confirmé',
        paypal_order_id = ?
        WHERE id_reservation = ? AND id_client = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "sii", $order_id, $reservation_id, $_SESSION["id"]);
    
    if (mysqli_stmt_execute($stmt)) {
        // Rediriger vers la page de confirmation
        header("location: confirmation.php?id=" . $reservation_id);
    } else {
        header("location: error.php");
    }
    mysqli_stmt_close($stmt);
} else {
    header("location: error.php");
}

mysqli_close($conn);
?>
