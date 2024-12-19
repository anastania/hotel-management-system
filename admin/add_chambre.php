<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et nettoyer les données du formulaire
    $id_hotel = $_POST['hotel'];
    $type_chambre = trim($_POST['type']);
    $nombre_lits = intval($_POST['lits']);
    $prix = floatval($_POST['prix']);
    $disponibilite = isset($_POST['disponibilite']) ? 1 : 0;

    // Validation des données
    if(empty($type_chambre) || $nombre_lits < 1 || $prix < 0) {
        $_SESSION['error'] = "Veuillez remplir tous les champs correctement.";
        header("location: chambres.php");
        exit;
    }

    // Insérer la nouvelle chambre
    $sql = "INSERT INTO chambres (id_hotel, type_chambre, nombre_lits, prix, disponibilite) VALUES (?, ?, ?, ?, ?)";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "isidi", $id_hotel, $type_chambre, $nombre_lits, $prix, $disponibilite);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "La chambre a été ajoutée avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de l'ajout de la chambre : " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Erreur de préparation de la requête : " . mysqli_error($conn);
    }

    mysqli_close($conn);
    header("location: chambres.php");
    exit;
}

// Si ce n'est pas une requête POST, rediriger vers la page des chambres
header("location: chambres.php");
exit;
?>
