<?php
session_start();

$_SESSION['error'] = "Le paiement a été annulé. Vous pouvez réessayer plus tard.";
header('Location: mes_reservations.php');
exit();
?>
