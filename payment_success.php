<?php
session_start();
require_once 'config.php';
require_once 'includes/db.php';

if (!isset($_GET['order_id']) || !isset($_GET['reservation_id'])) {
    header('Location: index.php');
    exit();
}

$order_id = $_GET['order_id'];
$reservation_id = $_GET['reservation_id'];

try {
    // Mettre à jour le statut de la réservation
    $stmt = $conn->prepare("UPDATE reservations SET status = 'confirmed', payment_id = ? WHERE id_reservation = ?");
    $stmt->execute([$order_id, $reservation_id]);
    
    $_SESSION['success'] = "Paiement effectué avec succès ! Votre réservation est confirmée.";
    header('Location: mes_reservations.php');
    exit();
} catch (PDOException $e) {
    $_SESSION['error'] = "Une erreur est survenue lors de la confirmation du paiement.";
    header('Location: mes_reservations.php');
    exit();
}
?>
