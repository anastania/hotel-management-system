<?php
session_start();
require_once "includes/config.php";
require_once "includes/db.php";

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_reservation'])) {
    $id_reservation = $_POST['id_reservation'];
    $id_client = $_SESSION["id_client"];

    // Vérifier que la réservation appartient bien au client
    $check_sql = "SELECT * FROM reservations WHERE id_reservation = ? AND id_client = ?";
    if($stmt = mysqli_prepare($conn, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $id_reservation, $id_client);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1) {
            // Supprimer la réservation
            $delete_sql = "DELETE FROM reservations WHERE id_reservation = ? AND id_client = ?";
            if($stmt_delete = mysqli_prepare($conn, $delete_sql)) {
                mysqli_stmt_bind_param($stmt_delete, "ii", $id_reservation, $id_client);
                
                if(mysqli_stmt_execute($stmt_delete)) {
                    $_SESSION['success'] = "Votre réservation a été annulée avec succès.";
                } else {
                    $_SESSION['error'] = "Une erreur est survenue lors de l'annulation de la réservation.";
                }
                mysqli_stmt_close($stmt_delete);
            }
        } else {
            $_SESSION['error'] = "Réservation non trouvée ou non autorisée.";
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $_SESSION['error'] = "Requête invalide.";
}

header("location: mes_reservations.php");
exit;
?>
