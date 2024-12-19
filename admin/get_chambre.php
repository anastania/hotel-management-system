<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID non fourni']);
    exit;
}

$id = intval($_GET['id']);

// Récupérer les informations de la chambre
$sql = "SELECT * FROM chambres WHERE id_chambre = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($chambre = mysqli_fetch_assoc($result)) {
        header('Content-Type: application/json');
        echo json_encode($chambre);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Chambre non trouvée']);
    }
    
    mysqli_stmt_close($stmt);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erreur de préparation de la requête']);
}

mysqli_close($conn);
?>
