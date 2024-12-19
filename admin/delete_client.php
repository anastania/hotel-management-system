<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if id parameter exists
if(!isset($_GET["id"]) || empty($_GET["id"])) {
    $_SESSION['error'] = "ID client non spécifié.";
    header("location: clients.php");
    exit;
}

$id_client = $_GET["id"];

// Check if client has reservations
$check_reservations = "SELECT COUNT(*) as count FROM reservations WHERE id_client = ?";
if($stmt = mysqli_prepare($conn, $check_reservations)) {
    mysqli_stmt_bind_param($stmt, "i", $id_client);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if($row['count'] > 0) {
        $_SESSION['error'] = "Impossible de supprimer ce client car il a des réservations associées.";
        header("location: clients.php");
        exit;
    }
}

// Delete client
$sql = "DELETE FROM clients WHERE id_client = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_client);
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Client supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Une erreur s'est produite lors de la suppression.";
    }
    
    mysqli_stmt_close($stmt);
}

header("location: clients.php");
exit;
?>
