<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Vérifier si le nom d'utilisateur existe déjà
    $check_sql = "SELECT id_admin FROM admin WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows > 0) {
        $_SESSION['error'] = "Ce nom d'utilisateur existe déjà.";
        header("location: administrateurs.php");
        exit;
    }
    $check_stmt->close();

    // Vérifier si les mots de passe correspondent
    if($password !== $confirm_password) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
        header("location: administrateurs.php");
        exit;
    }

    // Hasher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insérer le nouvel administrateur
    $sql = "INSERT INTO admin (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if($stmt->execute()) {
        $_SESSION['success'] = "L'administrateur a été créé avec succès.";
        $stmt->close();
        $conn->close();
        header("location: administrateurs.php");
        exit;
    } else {
        $_SESSION['error'] = "Erreur lors de la création de l'administrateur : " . $conn->error;
        $stmt->close();
        $conn->close();
        header("location: administrateurs.php");
        exit;
    }
}

header("location: administrateurs.php");
exit;
?>
