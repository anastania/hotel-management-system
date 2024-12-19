<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $id_hotel = trim($_POST["id_hotel"]);
    $type_chambre = trim($_POST["type_chambre"]);
    $prix = trim($_POST["prix"]);
    $disponibilite = isset($_POST["disponibilite"]) ? 1 : 0;
    
    // Validate input
    $error = false;
    if(empty($id_hotel) || empty($type_chambre) || empty($prix)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        $error = true;
    }
    
    if(!is_numeric($prix) || $prix <= 0) {
        $_SESSION['error'] = "Le prix doit être un nombre positif.";
        $error = true;
    }
    
    // Check if hotel exists
    $check_hotel = "SELECT id_hotel FROM hotels WHERE id_hotel = ?";
    if($stmt = mysqli_prepare($conn, $check_hotel)) {
        mysqli_stmt_bind_param($stmt, "i", $id_hotel);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($result) == 0) {
            $_SESSION['error'] = "L'hôtel sélectionné n'existe pas.";
            $error = true;
        }
    }
    
    // If no errors, insert room
    if(!$error) {
        $sql = "INSERT INTO chambres (id_hotel, type_chambre, prix, disponibilite) VALUES (?, ?, ?, ?)";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "isdi", $id_hotel, $type_chambre, $prix, $disponibilite);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "La chambre a été ajoutée avec succès.";
                header("location: rooms.php");
                exit();
            } else {
                $_SESSION['error'] = "Une erreur s'est produite lors de l'ajout de la chambre.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    // If there was an error, redirect back to rooms page
    header("location: rooms.php");
    exit();
}

// If not POST request, redirect to rooms page
header("location: rooms.php");
exit();
?>
