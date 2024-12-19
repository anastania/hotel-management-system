<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Vérifier si l'ID est fourni
if(!isset($_GET["id"])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID non fourni']);
    exit;
}

$id = $_GET["id"];

// Récupérer les données de l'hôtel
$sql = "SELECT * FROM hotels WHERE id_hotel = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1) {
            $hotel = mysqli_fetch_assoc($result);
            header('Content-Type: application/json');
            echo json_encode($hotel);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Hôtel non trouvé']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de l\'exécution de la requête']);
    }
    
    mysqli_stmt_close($stmt);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erreur lors de la préparation de la requête']);
}
?>
