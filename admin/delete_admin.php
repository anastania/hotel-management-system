<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

if(isset($_GET["id"]) && !empty($_GET["id"])) {
    $id = intval($_GET["id"]);
    
    // Empêcher la suppression de son propre compte
    if($id == $_SESSION["admin_id"]) {
        $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte.";
        header("location: administrateurs.php");
        exit;
    }
    
    // Supprimer l'administrateur
    $sql = "DELETE FROM admin WHERE id_admin = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "L'administrateur a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la suppression.";
        }
        
        mysqli_stmt_close($stmt);
    }
}

header("location: administrateurs.php");
exit;
?>
