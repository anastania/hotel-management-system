<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Vérifier si les données sont reçues
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_hotel = trim($_POST["id_hotel"]);
    $nom = trim($_POST["nom"]);
    $adresse = trim($_POST["adresse"]);
    $telephone = trim($_POST["telephone"]);
    
    // Mettre à jour l'hôtel
    $sql = "UPDATE hotels SET nom_hotel = ?, adresse = ?, telephone = ? WHERE id_hotel = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssi", $nom, $adresse, $telephone, $id_hotel);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "L'hôtel a été modifié avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur s'est produite lors de la modification de l'hôtel.";
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Rediriger vers la liste des hôtels
header("location: hotels.php");
exit;
?>
