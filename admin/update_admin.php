<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_POST['admin_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Vérifier si le nom d'utilisateur existe déjà
    $sql = "SELECT id_admin FROM admin WHERE username = ? AND id_admin != ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $username, $admin_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) > 0) {
            $_SESSION['error'] = "Ce nom d'utilisateur existe déjà.";
            header("location: administrateurs.php");
            exit;
        }
        mysqli_stmt_close($stmt);
    }
    
    // Mise à jour avec ou sans mot de passe
    if(!empty($password)) {
        // Hasher le nouveau mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE admin SET username = ?, email = ?, password = ? WHERE id_admin = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $hashed_password, $admin_id);
        }
    } else {
        $sql = "UPDATE admin SET username = ?, email = ? WHERE id_admin = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $admin_id);
        }
    }
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "L'administrateur a été mis à jour avec succès.";
    } else {
        $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour.";
    }
    
    mysqli_stmt_close($stmt);
}

header("location: administrateurs.php");
exit;
?>
