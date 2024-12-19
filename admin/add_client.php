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
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $telephone = trim($_POST["telephone"]);
    $adresse = trim($_POST["adresse"]);
    $mot_de_passe = password_hash(trim($_POST["mot_de_passe"]), PASSWORD_DEFAULT);
    
    // Validate input
    $error = false;
    if(empty($nom) || empty($email) || empty($_POST["mot_de_passe"])) {
        $_SESSION['error'] = "Le nom, l'email et le mot de passe sont obligatoires.";
        $error = true;
    }
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format d'email invalide.";
        $error = true;
    }
    
    // Check if email already exists
    $check_email = "SELECT id_client FROM clients WHERE email = ?";
    if($stmt = mysqli_prepare($conn, $check_email)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($result) > 0) {
            $_SESSION['error'] = "Cet email est déjà utilisé.";
            $error = true;
        }
    }
    
    if(!$error) {
        $sql = "INSERT INTO clients (nom, email, telephone, adresse, password) VALUES (?, ?, ?, ?, ?)";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssss", $nom, $email, $telephone, $adresse, $mot_de_passe);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Client ajouté avec succès.";
                header("location: clients.php");
                exit();
            } else {
                $_SESSION['error'] = "Une erreur s'est produite lors de l'ajout du client.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    header("location: clients.php");
    exit();
}

// If not POST request, redirect to clients page
header("location: clients.php");
exit();
?>
