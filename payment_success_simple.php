<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    
    try {
        // Update reservation status
        $sql = "UPDATE reservations 
                SET status = 'confirmed', 
                    payment_status = 'completed', 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE payment_id = ? AND id_client = ?";
                
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $order_id, $_SESSION['id_client']);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Paiement effectué avec succès! Votre réservation est confirmée.";
        } else {
            throw new Exception("Erreur lors de la mise à jour de la réservation.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Informations de paiement manquantes.";
}

header('Location: mes_reservations.php');
exit;
