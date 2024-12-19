<?php
// Configuration PayPal
define('PAYPAL_CLIENT_ID', 'YOUR_PAYPAL_CLIENT_ID');
define('PAYPAL_SECRET', 'YOUR_PAYPAL_SECRET');
define('PAYPAL_CURRENCY', 'EUR');
define('PAYPAL_MODE', 'sandbox'); // sandbox ou live

// URLs PayPal
define('PAYPAL_RETURN_URL', 'http://localhost/Gestion_reservation_hotel/process_payment.php');
define('PAYPAL_CANCEL_URL', 'http://localhost/Gestion_reservation_hotel/cancel_payment.php');
?>
