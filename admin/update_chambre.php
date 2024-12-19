<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_chambre = $_POST['id_chambre'];
    $id_hotel = $_POST['hotel'];
    $type_chambre = trim($_POST['type']);
    $nombre_lits = intval($_POST['lits']);
    $prix = floatval($_POST['prix']);
    $disponibilite = isset($_POST['disponibilite']) ? 1 : 0;
    
    // Mettre à jour la chambre
    $sql = "UPDATE chambres SET id_hotel = ?, type_chambre = ?, nombre_lits = ?, prix = ?, disponibilite = ? WHERE id_chambre = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "isidii", $id_hotel, $type_chambre, $nombre_lits, $prix, $disponibilite, $id_chambre);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "La chambre a été mise à jour avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour de la chambre.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Erreur de préparation de la requête.";
    }
}

header("location: chambres.php");
exit;
?>
