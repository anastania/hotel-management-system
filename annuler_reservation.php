<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $reservation_id = $_GET['id'];

    // Prepare the SQL statement to cancel the reservation
    $sql = "DELETE FROM reservations WHERE id_reservation = ? AND id_client = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $reservation_id, $_SESSION["id"]);
        
        if (mysqli_stmt_execute($stmt)) {
            // Optionally restore room availability here if needed
            $_SESSION['success'] = "Réservation annulée avec succès.";
            header("location: mes_reservations.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'annulation de la réservation.";
            header("location: mes_reservations.php");
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Erreur lors de la préparation de la requête.";
        header("location: mes_reservations.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Identifiant de réservation manquant.";
    header("location: mes_reservations.php");
    exit();
}

mysqli_close($conn);
?>
