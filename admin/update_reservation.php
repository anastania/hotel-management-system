<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if id and status are provided
if(!isset($_GET["id"]) || !isset($_GET["status"])) {
    $_SESSION['error'] = "Paramètres manquants.";
    header("location: reservations.php");
    exit;
}

$id = $_GET["id"];
$status = $_GET["status"];

// Validate status
$valid_statuses = ['pending', 'confirmed', 'cancelled'];
if(!in_array($status, $valid_statuses)) {
    $_SESSION['error'] = "Statut invalide.";
    header("location: reservations.php");
    exit;
}

// Update reservation status
$sql = "UPDATE reservations SET status = ? WHERE id_reservation = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    
    if(mysqli_stmt_execute($stmt)) {
        // If status is confirmed, update room availability
        if($status == 'confirmed') {
            $update_room = "UPDATE chambres c 
                          JOIN reservations r ON c.id_chambre = r.id_chambre 
                          SET c.disponibilite = 0 
                          WHERE r.id_reservation = ?";
            if($room_stmt = mysqli_prepare($conn, $update_room)) {
                mysqli_stmt_bind_param($room_stmt, "i", $id);
                mysqli_stmt_execute($room_stmt);
            }
        } elseif($status == 'cancelled') {
            // If status is cancelled, make room available again
            $update_room = "UPDATE chambres c 
                          JOIN reservations r ON c.id_chambre = r.id_chambre 
                          SET c.disponibilite = 1 
                          WHERE r.id_reservation = ?";
            if($room_stmt = mysqli_prepare($conn, $update_room)) {
                mysqli_stmt_bind_param($room_stmt, "i", $id);
                mysqli_stmt_execute($room_stmt);
            }
        }
        
        $_SESSION['success'] = "Le statut de la réservation a été mis à jour avec succès.";
    } else {
        $_SESSION['error'] = "Une erreur s'est produite lors de la mise à jour du statut.";
    }
    
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error'] = "Une erreur s'est produite lors de la préparation de la requête.";
}

header("location: reservations.php");
exit;
?>
