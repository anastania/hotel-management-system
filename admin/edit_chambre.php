<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et valider les données
    $id_chambre = $_POST["id_chambre"];
    $id_hotel = $_POST["hotel"];
    $type_chambre = $_POST["type"];
    $nombre_lits = $_POST["lits"];
    $prix = $_POST["prix"];

    // Validation de base
    if (empty($id_chambre) || empty($id_hotel) || empty($type_chambre) || empty($nombre_lits) || empty($prix)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        header("location: chambres.php");
        exit();
    }

    // Valider le type de chambre
    $types_valides = ['simple', 'double', 'suite'];
    if (!in_array($type_chambre, $types_valides)) {
        $_SESSION['error'] = "Type de chambre invalide.";
        header("location: chambres.php");
        exit();
    }

    // Valider le nombre de lits
    if ($nombre_lits < 1 || $nombre_lits > 4) {
        $_SESSION['error'] = "Le nombre de lits doit être entre 1 et 4.";
        header("location: chambres.php");
        exit();
    }

    // Valider le prix
    if ($prix < 0) {
        $_SESSION['error'] = "Le prix ne peut pas être négatif.";
        header("location: chambres.php");
        exit();
    }

    // Vérifier si l'hôtel existe
    $check_hotel = "SELECT id_hotel FROM hotels WHERE id_hotel = ?";
    $stmt = mysqli_prepare($conn, $check_hotel);
    mysqli_stmt_bind_param($stmt, "i", $id_hotel);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['error'] = "L'hôtel sélectionné n'existe pas.";
        header("location: chambres.php");
        exit();
    }

    // Mettre à jour la chambre
    $sql = "UPDATE chambres SET 
            id_hotel = ?, 
            type_chambre = ?, 
            nombre_lits = ?, 
            prix = ?
            WHERE id_chambre = ?";

    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "isidi", $id_hotel, $type_chambre, $nombre_lits, $prix, $id_chambre);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "La chambre a été mise à jour avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur s'est produite lors de la mise à jour.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Une erreur s'est produite lors de la préparation de la requête.";
    }

    header("location: chambres.php");
    exit();
} else {
    // Si ce n'est pas une requête POST, rediriger vers la page des chambres
    header("location: chambres.php");
    exit();
}
?>
