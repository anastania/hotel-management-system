<?php
session_start();
require_once "includes/config.php";

// Verify if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Verify if room ID is provided
if (!isset($_POST["id_chambre"])) {
    $_SESSION["error"] = "ID de chambre manquant.";
    header("location: chambres.php");
    exit;
}

$id_chambre = $_POST["id_chambre"];

// Get room details
$sql = "SELECT c.*, h.nom_hotel 
        FROM chambres c 
        JOIN hotels h ON c.id_hotel = h.id_hotel 
        WHERE c.id_chambre = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_chambre);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $chambre = mysqli_fetch_assoc($result);
            
            // Check if room is available
            if ($chambre["disponibilite"] == 1) {
                // Redirect to the reservation form with the room ID
                header("location: chambre_details.php?id=" . $id_chambre);
                exit;
            } else {
                $_SESSION["error"] = "Cette chambre n'est plus disponible.";
                header("location: chambres.php");
                exit;
            }
        } else {
            $_SESSION["error"] = "Chambre non trouvée.";
            header("location: chambres.php");
            exit;
        }
    } else {
        $_SESSION["error"] = "Une erreur est survenue. Veuillez réessayer.";
        header("location: chambres.php");
        exit;
    }
    
    mysqli_stmt_close($stmt);
} else {
    $_SESSION["error"] = "Une erreur est survenue. Veuillez réessayer.";
    header("location: chambres.php");
    exit;
}

mysqli_close($conn);
?>
