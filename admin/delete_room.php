<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if(isset($_GET["id"])) {
    $id = $_GET["id"];
    
    // First check if room has any reservations
    $check_sql = "SELECT COUNT(*) as res_count FROM reservations WHERE id_chambre = ?";
    if($check_stmt = mysqli_prepare($conn, $check_sql)) {
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $res_count = mysqli_fetch_assoc($check_result)['res_count'];
        
        if($res_count > 0) {
            $_SESSION['error'] = "Impossible de supprimer la chambre car elle a des réservations associées.";
            header("location: rooms.php");
            exit();
        }
    }
    
    // If no reservations, proceed with deletion
    $sql = "DELETE FROM chambres WHERE id_chambre = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "La chambre a été supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur s'est produite lors de la suppression de la chambre.";
        }
    } else {
        $_SESSION['error'] = "Une erreur s'est produite lors de la préparation de la requête.";
    }
}

header("location: rooms.php");
exit;
?>
